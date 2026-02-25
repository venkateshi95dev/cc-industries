<?php
/**
 * @namespace   Crimson
 * @module      Attributes
 * @author      Jennfier Nodwell
 * @email       jnodwell@crimsonagility.com
 * @date        03/07/2019
 */
namespace Crimson\Attributes\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Model\Customer; //only for aliasing


class FixIsSystemToCustomerAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    protected $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory     $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
    EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(
            'customer'
        );

        //$customerEntityId = $customerSetup->getEntityTypeId(\Magento\Customer\Model\Customer::ENTITY);

        $IsSytemAttributes = [
            155 => 'reward_update_notification',
            166 => 'reward_warning_notification',
            280 => 'mach_price_level',
            286 => 'mach_customer_number'
        ];
        foreach ($IsSytemAttributes as $attribute_id => $attribute_code) {
            $attribute = $customerSetup->getEavConfig()->getAttribute(
                Customer::ENTITY, $attribute_code
            )->setIsSystem(0);
            $attribute->save();
        }
    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [
        ];
    }
}