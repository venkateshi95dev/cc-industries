<?php

declare(strict_types=1);

namespace Crimson\AmastyStorePickupWithLocator\Console\Command;

use Amasty\Storelocator\Model\Attribute;
use Amasty\Storelocator\Model\Location;
use Amasty\Storelocator\Model\LocationFactory;
use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BulkUpdate extends Command
{
    const FILE = BP . '/var/import/ImportPreferredInstallerLocations/preferred_installer_import.csv';
    const LOCATION_IDENTIFIER_COLUMN = 'name';
    const OPTION_KEEP = 'keep';

    public function __construct(
        private readonly State $appState,
        private readonly Attribute $attributeModel,
        private readonly Location $locationModel,
        private readonly LocationFactory $locationFactory,
        private readonly ResourceConnection $resource
    ) {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('crimson:storelocator:bulk-update')
            ->setDescription(
                'Bulk update AmastyStoreLocator locations using the file ' . self::FILE . PHP_EOL .
                '. After the importation, the file will be deleted. To prevent it use the parameter -k'
            )->addOption(
                self::OPTION_KEEP,
                'k',
                InputOption::VALUE_NONE,
                'Keep the CSV file'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->appState->setAreaCode('adminhtml');
        } catch (LocalizedException $e) {
            // ignore if area code already set
        }


        if (!file_exists(self::FILE)) {
            $output->writeln(sprintf('File not found: %s', self::FILE));
            return Command::FAILURE;
        }

        $handle = fopen(self::FILE, 'r');
        if ($handle === false) {
            $output->writeln('Unable to open file.');
            return Command::FAILURE;
        }

        $headers = fgetcsv($handle);
        if ($headers === false || !in_array(self::LOCATION_IDENTIFIER_COLUMN, $headers, true)) {
            $output->writeln(sprintf('CSV must contain "%s" column.', self::LOCATION_IDENTIFIER_COLUMN));
            fclose($handle);
            return Command::FAILURE;
        }

        $locationColumns = $this->getLocationColumns();

        $updated = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);
            if (!$data) {
                continue;
            }
            if (!trim($data[self::LOCATION_IDENTIFIER_COLUMN])) {
                continue;
            }

            $location = $this->locationFactory->create();

            try {
                if ($data[self::LOCATION_IDENTIFIER_COLUMN]) {
                    $location = $this->loadLocationByName($data[self::LOCATION_IDENTIFIER_COLUMN]) ?? $location;
                    if (!$location) {
                        $output->writeln(sprintf(
                            'Location "%s" not found! Creating new one...',
                            $data[self::LOCATION_IDENTIFIER_COLUMN])
                        );
                    }
                }
            } catch (Exception $e) {
                if ($data[self::LOCATION_IDENTIFIER_COLUMN]) {
                    $output->writeln(sprintf(
                        'Location "%s" not found! Error: %s',
                        $data[self::LOCATION_IDENTIFIER_COLUMN],
                        $e->getMessage()
                    ));
                    continue;
                }
            }

            try {
                $attributes = $location->getAttributes() ?? [];
                foreach ($data as $field => $value) {
                    if ($value === '' || $value === null) {
                        continue;
                    }

                    // Update column or attribute
                    if (in_array($field, $locationColumns)) {
                        $location->setData($field, $value);
                    } else {
                        $attributes[$field] = $value;
                    }
                }

                $location->save();

                foreach ($attributes as $attributeCode => $attributeValue) {
                    $attribute = $this->getAttributeByCode($attributeCode);
                    if (!$attribute) {
                        $output->writeln(sprintf('Attribute "%s" does not exist', $attributeCode));
                        continue;
                    }
                    $this->updateAttribute((int) $attribute->getAttributeId(), $attributeValue, (int) $location->getId());
                }

                $updated++;
                $output->writeln(sprintf('Updated/Created location "%s"', $location->getName()));
            } catch (Exception $e) {
                $output->writeln(sprintf(
                    'Error updating location ID %s: %s',
                    $data[self::LOCATION_IDENTIFIER_COLUMN],
                    $e->getMessage()
                ));
            }
        }

        fclose($handle);
        $output->writeln(sprintf('Bulk update complete. %d locations updated.', $updated));

        if (!$input->getOption('keep')) {
            //delete CSV file
            unlink(self::FILE);
        }

        return Command::SUCCESS;
    }

    /**
     * @return array
     */
    protected function getLocationColumns() : array
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('amasty_amlocator_location');
        $columns = $connection->describeTable($tableName);

        return array_keys($columns) ?? [];
    }

    /**
     * @param string $name
     * @return Location|null
     */
    protected function loadLocationByName(string $name) : ?Location
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('amasty_amlocator_location');

        $select = $connection->select()
            ->from($tableName)
            ->where('LOWER(name) = ?', strtolower($name))
            ->limit(1);

        $result = $connection->fetchRow($select);

        return $result ? $this->locationModel->load((int)$result['id']) : null;
    }

    /**
     * @param string $code
     * @return Attribute|null
     */
    protected function getAttributeByCode(string $code) : ?Attribute
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('amasty_amlocator_attribute');

        $select = $connection->select()
            ->from($tableName)
            ->where('attribute_code = ?', $code)
            ->limit(1);

        $result = $connection->fetchRow($select);

        return $result ? $this->attributeModel->load((int)$result['attribute_id']) : null;
    }

    /**
     * @param int $attributeId
     * @param string $value
     * @param int $storeId
     * @return void
     */
    protected function updateAttribute(int $attributeId, string $value, int $storeId) : void
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('amasty_amlocator_store_attribute');

        $data = [
            'attribute_id' => $attributeId,
            'store_id'     => $storeId,
            'value'        => $value
        ];

        $connection->insertOnDuplicate(
            $tableName,
            $data,
            ['value']
        );
    }
}
