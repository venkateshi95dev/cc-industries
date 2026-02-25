<?php
/**
 * @author    Subhan
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_ShippingMapping
 */

namespace I95DevConnect\ShippingMapping\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Config\FileResolverInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Shippinglist implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var FileResolverInterface
     */
    public $fileResolver;
    /**
     * @var \I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Reverse\ShippingAddress
     */
    public $shippingAddress;
    /**
     * @var \I95DevConnect\MessageQueue\Helper\Data
     */
    public $dataHelper;

    /**
     * Shippinglist constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param FileResolverInterface $fileResolver
     * @param \I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Reverse\ShippingAddress $shippingAddress
     * @param \I95DevConnect\MessageQueue\Helper\Data $dataHelper
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Reverse\ShippingAddress $shippingAddress,
        \I95DevConnect\MessageQueue\Helper\Data $dataHelper
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->fileResolver = $fileResolver;
        $this->shippingAddress = $shippingAddress;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $csvData = $this->fileResolver->get("shipping.csv", 'global');

        if (count($csvData) > 0) {
            $activeMethods = $this->shippingAddress->getActiveShippingMethods();
            if (!empty($activeMethods)) {
                foreach ($csvData as $content) {
                    $csvRow = str_getcsv($content, "\n");
                }

                $this->insertShippingMapping($setup, $csvRow, $activeMethods);
            }
        }
    }

    /**
     * Inserting shipping mapping
     *
     * @param object $setup
     * @param array $csvRow
     * @param array $activeMethods
     */
    public function insertShippingMapping($setup, $csvRow, $activeMethods)
    {
        foreach ($csvRow as $mappingData) {
            $currentEntity = explode(",", $mappingData);

            if (in_array($currentEntity[2], $activeMethods)) {
                $setup->getConnection()->insert(
                    $setup->getTable('i95dev_shipping_mapping_list'),
                    [
                        'erp_code' => $currentEntity[3],
                        'magento_code' => $currentEntity[2],
                        'is_ecommerce_default' => $currentEntity[5],
                        'is_erp_default' => $currentEntity[6]
                    ]
                );
            }
        }
    }

    /**
     * Get Aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get Dependencies
     *
     * @return array
     */
    public static function getDependencies() // NOSONAR
    {
        return [

        ];
    }

    /**
     * Get patch version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.7';
    }
}
