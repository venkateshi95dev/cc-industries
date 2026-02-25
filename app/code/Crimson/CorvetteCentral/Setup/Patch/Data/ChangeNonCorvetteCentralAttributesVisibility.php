<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\WebsiteFactory;

/**
 * @def This Data Patch will change the "is_visible" property to false, for the ATTRIBUTE_CODES in CorvetteCentral
 */
class ChangeNonCorvetteCentralAttributesVisibility implements DataPatchInterface
{
    const ATTRIBUTE_CODES = [
        'mach_pin',
        'is_club_member',
        'club_expiration_date',
        'club_discount',
        'mach_customer_number',
        'customer_vehicle_segment',
        'my_car_group_001',
        'car_1_year',
        'car_1_make',
        'car_1_model',
        'car_1_submodel',
        'my_car_group_002',
        'car_02_year',
        'car_02_make',
        'car_02_model',
        'car_02_submodel',
        'my_car_group_003',
        'motorcycle_year_001',
        'motorcycle_make_001',
        'motorcycle_model_001',
        'motorcycle_year_002',
        'motorcycle_make_002',
        'motorcycle_model_002'
    ];

    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private EavConfig $eavConfig,
        private WebsiteFactory $websiteFactory
    ) {
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        try {
            $connection = $this->moduleDataSetup->getConnection();
            $websiteCode = CorvetteCentralStoreInterface::CORVETTE_CENTRAL_WEBSITE_CODE;

            $website = $this->websiteFactory->create()->load($websiteCode, 'code');
            $websiteId = (int) $website->getId();

            $table = $this->moduleDataSetup->getTable('customer_eav_attribute_website');

            foreach (self::ATTRIBUTE_CODES as $code) {
                $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, $code);
                if (!$attribute || !$attribute->getId()) {
                    continue;
                }

                $connection->insertOnDuplicate(
                    $table,
                    [
                        'attribute_id' => (int) $attribute->getId(),
                        'website_id'   => $websiteId,
                        'is_visible'   => 0,
                    ],
                    ['is_visible']
                );
            }
        } catch (\Exception $e) {}

    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
