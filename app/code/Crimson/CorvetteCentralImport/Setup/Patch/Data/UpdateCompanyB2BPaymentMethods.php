<?php
namespace Crimson\CorvetteCentralImport\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;

class UpdateCompanyB2BPaymentMethods implements DataPatchInterface
{
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private StoreManagerInterface $storeManager
    ) {
    }
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $conn    = $this->moduleDataSetup->getConnection();
        $cp     = $this->moduleDataSetup->getTable('company_payment');

        $sql = "UPDATE {$cp}
            SET applicable_payment_method = 0, available_payment_methods=null ,use_config_settings=1
            WHERE use_config_settings=0";

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
