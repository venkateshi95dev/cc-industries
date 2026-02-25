<?php
namespace Crimson\CorvetteCentralImport\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;

class UpdateMainImageFilenames implements DataPatchInterface
{
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Apply patch: replace `-1.main.jpg` with `.main.jpg`
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $conn    = $this->moduleDataSetup->getConnection();
        $cpv     = $this->moduleDataSetup->getTable('catalog_product_entity_varchar');
        $ea      = $this->moduleDataSetup->getTable('eav_attribute');
        $et      = $this->moduleDataSetup->getTable('eav_entity_type');
        $storeId = $this->storeManager->getStore(\Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();

        $sql = "
            UPDATE {$cpv} AS cpv
            JOIN {$ea} AS ea ON cpv.attribute_id = ea.attribute_id
            JOIN {$et} AS et ON ea.entity_type_id = et.entity_type_id
                AND et.entity_type_code = 'catalog_product'
            SET cpv.value = REPLACE(cpv.value, '-1.main.jpg', '.main.jpg')
            WHERE ea.attribute_code IN ('image','small_image','thumbnail')
              AND cpv.value LIKE '%-1.main.jpg' AND cpv.store_id={$storeId}
        ";

        $conn->query($sql);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * No dependencies
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * No aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
