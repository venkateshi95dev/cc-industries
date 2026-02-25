<?php
declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class UpdateConfigurableChildrenOrderForGiftCard implements DataPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;
    private ResourceConnection $resource;
    private LoggerInterface $logger;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ResourceConnection       $resource,
        LoggerInterface         $logger
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->resource = $resource;
        $this->logger = $logger;
    }

    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $this->moduleDataSetup->getConnection()->startSetup();

        try {
            $tableAttr = $this->resource->getTableName('eav_attribute');
            $tableOption = $this->resource->getTableName('eav_attribute_option');
            $tableOptionValue = $this->resource->getTableName('eav_attribute_option_value');

            // find attribute_id for catalog_product.attribute_code = 'cc_amounts'
            $selectAttr = $connection->select()
                ->from($tableAttr, ['attribute_id'])
                ->where('attribute_code = ?', 'cc_amounts')
                ->where('entity_type_id = 4');

            $attributeId = (int)$connection->fetchOne($selectAttr);
            if ($attributeId <= 0) {
                $this->log('Attribute "amounts" not found; aborting.');
                return;
            }

            // load options (store_id = 0) with their labels and current sort_order
            $select = $connection->select()
                ->from(['ao' => $tableOption], ['option_id', 'sort_order'])
                ->joinLeft(
                    ['aov' => $tableOptionValue],
                    'ao.option_id = aov.option_id AND aov.store_id = 0',
                    ['value']
                )
                ->where('ao.attribute_id = ?', $attributeId);

            $rows = $connection->fetchAll($select);
            if (empty($rows)) {
                $this->log('No options found for attribute "amount"; nothing to do.');
                return;
            }

            $map = [];
            foreach ($rows as $row) {
                $optionId = (int)$row['option_id'];
                $map[$optionId] = [
                    'option_id' => $optionId,
                    'numeric' => (float)$row['value']
                ];
            }

            // Sort by numeric ascending; non-numeric options go last
            uasort($map, function ($a, $b) {
                $an = $a['numeric'];
                $bn = $b['numeric'];
                if ($an !== null && $bn !== null) {
                    return $an <=> $bn;
                }
                if ($an !== null) {
                    return -1;
                }
                if ($bn !== null) {
                    return 1;
                }
                return $a['original_index'] <=> $b['original_index'];
            });

            $position = 0;
            foreach ($map as $entry) {
                $optionId = $entry['option_id'];
                $connection->update(
                    $tableOption,
                    ['sort_order' => $position],
                    ['option_id = ?' => $optionId]
                );
                $position++;
            }

            $this->log('Reordering of "amount" attribute options completed.');
        } catch (\Throwable $e) {
            $this->log('Error reordering attribute options: ' . $e->getMessage());
        } finally {
            $this->moduleDataSetup->getConnection()->endSetup();
        }
    }


    private function log(string $msg): void
    {
        if ($this->logger) {
            $this->logger->info('[Crimson][ReorderAmountAttributeOptions] ' . $msg);
        }
    }

    public static function getDependencies(): array
    {
        return [CreateProductAttributesForCC5::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
