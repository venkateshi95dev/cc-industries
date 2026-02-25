<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Console\Command;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area;
use Magento\Framework\Console\Cli;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Exception;

class ImportAttributeValues extends Command
{
    const CHUNK_SIZE = 500;
    const ARG_FILE_PATH = 'file';
    const ARG_ATTR_TYPE = 'attribute_type';
    const OPTION_IS_ATTRIBUTE_WITH_OPTIONS = 'has-options';

    public function __construct(
        protected AppState $appState,
        protected ModuleDataSetupInterface $moduleDataSetup,
        protected EavSetupFactory $eavSetupFactory,
        protected AttributeRepositoryInterface $attributeRepository,
        protected array $options = []
    ) {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure() : void
    {
        $this->setName('crimson:cc-attribute-values:import')
            ->setDescription('Command to import attribute values from a CSV file. Parameters <file> <attribute_type>')
            ->addArgument(
                self::ARG_FILE_PATH,
                InputArgument::REQUIRED,
                'Path to the CSV file, eg. /var/import/file.csv'
            )
            ->addArgument(
                self::ARG_ATTR_TYPE,
                InputArgument::REQUIRED,
                'Type of the attribute to import (eg. varchar, int...)'
            )->addOption(
            self::OPTION_IS_ATTRIBUTE_WITH_OPTIONS,
            'o',
            InputOption::VALUE_NONE,
            'Tells the algorithm that the attribute has options, and the value imported is the option label'
        );

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        // setting Area Code
        try {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
        }

        // initial message
        $output->writeln("Import attribute values...");

        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);


        // Path to the CSV file
        $csvFilePath = BP . '/' . $input->getArgument(self::ARG_FILE_PATH);
        $attributeType = $input->getArgument(self::ARG_ATTR_TYPE);

        if (!file_exists($csvFilePath)) {
            $output->writeln("CSV file not found: " . $csvFilePath);
            return Cli::RETURN_FAILURE;
        }

        $connection = $this->moduleDataSetup->getConnection();
        $productEntityTable = $connection->getTableName('catalog_product_entity');
        $attrTable = $connection->getTableName('catalog_product_entity_' . $attributeType);

        if (!$connection->isTableExists($attrTable)) {
            $output->writeln("Attribute type $attributeType doesn't exist");
            return Cli::RETURN_FAILURE;
        }

        $file = new \SplFileObject($csvFilePath);
        $file->setFlags(\SplFileObject::READ_CSV);
        $header = $file->fgetcsv();

        if (!$header) {
            $output->writeln("CSV header missing: " . $csvFilePath);
            return Cli::RETURN_FAILURE;
        }

        $attributeCodes = array_slice($header, 1);

        // resolve attribute IDs once
        $attributeIds = [];
        foreach ($attributeCodes as $code) {
            try {
                $attribute = $eavSetup->getAttribute(Product::ENTITY, $code);
                if ($attribute && $attribute['attribute_id']) {
                    $attributeIds[$code] = (int) $attribute['attribute_id'];
                }
            } catch (Exception $e) {
                $output->writeln("Wasn't able to get attribute $code to be populated");
                $output->writeln($e->getMessage());
            }
        }

        $insertData = [];
        $rowCount = 0;

        foreach ($file as $row) {
            if (empty($row) || !$row[0]) {
                continue;
            }

            $sku = $row[0];

            // get product Row ID
            $rowId = $connection->fetchOne(
                $connection->select()
                    ->from($productEntityTable, ['row_id'])
                    ->where('sku = ?', $sku)
            );

            if (!$rowId) {
                continue;
            }

            foreach ($attributeCodes as $index => $code) {
                if (!isset($row[$index + 1]) || !isset($attributeIds[$code])) {
                    continue;
                }

                try {
                    $value = $this->getCorrectValue(
                        $code,
                        $row[$index + 1],
                        (bool) $input->getOption(self::OPTION_IS_ATTRIBUTE_WITH_OPTIONS)
                    );
                } catch (Exception $e) {
                    $output->writeln($e->getMessage());
                    $output->writeln("skipping...");
                    continue;
                }

                $insertData[] = [
                    'attribute_id' => $attributeIds[$code],
                    'store_id'     => 0,
                    'row_id'       => $rowId,
                    'value'        => $value
                ];
            }

            $rowCount++;

            // flush batch
            if ($rowCount % self::CHUNK_SIZE === 0 && !empty($insertData)) {
                try {
                    $connection->insertOnDuplicate($attrTable, $insertData, ['value']);
                } catch (Exception $e) {
                    $output->writeln($e->getMessage());
                }
                $insertData = [];
            }
        }

        // flush remaining
        if (!empty($insertData)) {
            try {
                $connection->insertOnDuplicate($attrTable, $insertData, ['value']);
            } catch (Exception $e) {
                $output->writeln($e->getMessage());
            }
        }

        $output->writeln("Process finished!");
        $this->moduleDataSetup->getConnection()->endSetup();

        return Cli::RETURN_SUCCESS;
    }

    /**
     * @param string $attributeCode
     * @param mixed $value
     * @param bool $hasOptions
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCorrectValue(string $attributeCode, mixed $value, bool $hasOptions) : mixed
    {
        if (strtoupper($value) === 'TRUE') {
            $value = 1;
        } elseif (strtoupper($value) === 'FALSE') {
            $value = 0;
        }

        //If the attribute has options, get the correct value
        if ($hasOptions) {
            $value = $this->getOptionValueByLabel($attributeCode, $value);
        }

        return $value;
    }

    /**
     * @param string $attributeCode
     * @param mixed $label
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getOptionValueByLabel(string $attributeCode, mixed $label) : mixed
    {
        if (!isset($this->options[$label])) {
            $attribute = $this->attributeRepository->get('catalog_product', $attributeCode);
            $options = $attribute->getOptions();

            foreach ($options as $option) {
                $optionLabel = $option->getLabel();
                if (!isset($this->options[$optionLabel])) {
                    $this->options[$optionLabel] = $option->getValue();
                }
            }
        }
        return $this->options[$label];
    }
}
