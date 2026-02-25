<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\SalesRule\Api\Data\ConditionInterface;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\Data\RuleInterfaceFactory;
use Magento\SalesRule\Api\Data\CouponInterfaceFactory;
use Magento\SalesRule\Api\Data\ConditionInterfaceFactory;
use Magento\SalesRule\Model\Rule\Condition\Combine;
use Amasty\Conditions\Model\Rule\Condition\CustomerAttributes;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Area as AppArea;
use Magento\Framework\App\State as AppState;
use Psr\Log\LoggerInterface;

class CreateCartPriceRulesPart2 implements DataPatchInterface
{

    public function __construct(
        private readonly StoreManagerInterface                         $storeManager,
        private readonly RuleInterfaceFactory                          $ruleInterfaceFactory,
        private readonly ConditionInterfaceFactory                     $conditionInterfaceFactory,
        private readonly RuleRepositoryInterface                       $ruleRepository,
        private readonly CouponRepositoryInterface                     $couponRepository,
        private readonly CouponInterfaceFactory                        $couponInterfaceFactory,
        private readonly \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        private readonly AppState                                      $appState,
        private readonly LoggerInterface                               $logger
    )
    {
    }

    const ARR_RULES = [
        [
            'Name' => 'Brand \'Coker Classic\' FREE Shipping Monthly Promo',
            'Description' => 'Brand \'Coker Classic\' FREE Shipping Monthly Promo',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 1000,
            'UsesPerCustomer' => 1000,
            'DiscountStep' => 0,
            'DiscountAmount' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'CouponCode' => 'COKERFREE',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => false,
            'SortOrder' => 2,
            'SimpleFreeShipping' => true,
            'Condition' => [
                'Children' =>
                    [
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                            'AttributeName' => 'country_id',
                            'Operator' => '==',
                            'Value' => 'US'
                        ]
                    ]
            ],
            'ActionCondition' => [
                'Children' =>
                    [
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                            'AttributeName' => 'brand_item',
                            'Operator' => '==',
                            'OptionValue' => 'Coker Classic',
                            'Value' => ''
                        ]
                    ]
            ]
        ],
        [
            'Name' => 'Brand \'Firestone\' FREE Shipping Monthly Promo',
            'Description' => 'Brand \'Firestone\' FREE Shipping Monthly Promo',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 1000,
            'UsesPerCustomer' => 1000,
            'DiscountStep' => 0,
            'DiscountAmount' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'CouponCode' => 'FIRESTONEFREE',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => false,
            'SortOrder' => 10,
            'SimpleFreeShipping' => true,
            'Condition' => [
                'Children' =>
                    [
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                            'AttributeName' => 'country_id',
                            'Operator' => '==',
                            'Value' => 'US'
                        ]
                    ]
            ],
            'ActionCondition' => [
                'Children' =>
                    [
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                            'AttributeName' => 'brand_item',
                            'Operator' => '==',
                            'OptionValue' => 'Firestone',
                            'Value' => ''
                        ]
                    ]
            ]
        ],
        [
            'Name' => 'Brand \'American Classic\' FREE Shipping Monthly Promo',
            'Description' => 'Brand \'American Classic\' FREE Shipping Monthly Promo',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 1000,
            'UsesPerCustomer' => 1000,
            'DiscountStep' => 0,
            'DiscountAmount' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'CouponCode' => 'ACFREE',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => false,
            'SortOrder' => 5,
            'SimpleFreeShipping' => true,
            'Condition' => [
                'Children' =>
                    [
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                            'AttributeName' => 'country_id',
                            'Operator' => '==',
                            'Value' => 'US'
                        ],
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                            'AttributeName' => 'shipping_method',
                            'Operator' => '==',
                            'Value' => 'fedex_FEDEX_GROUND'
                        ]
                    ]
            ],
            'ActionCondition' => [
                'Children' =>
                    [
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                            'AttributeName' => 'brand_item',
                            'Operator' => '==',
                            'OptionValue' => 'American Classic',
                            'Value' => ''
                        ]
                    ]
            ]
        ],
        [
            'Name' => 'BFG Silvertown Bias Ply FREE Shipping',
            'Description' => 'BFG Silvertown Bias Ply FREE Shipping',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 99999,
            'UsesPerCustomer' => 99999,
            'DiscountStep' => 0,
            'DiscountAmount' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'CouponCode' => 'BIASFREE',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => false,
            'SortOrder' => 3,
            'SimpleFreeShipping' => true,
            'Condition' => [
                'Children' =>
                    [
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                            'AttributeName' => 'country_id',
                            'Operator' => '==',
                            'Value' => 'US'
                        ],
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                            'AttributeName' => 'shipping_method',
                            'Operator' => '==',
                            'Value' => 'fedex_FEDEX_GROUND'
                        ]
                    ]
            ],
            'ActionCondition' => [
                'Children' =>
                    [
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                            'AttributeName' => 'brand_item',
                            'Operator' => '==',
                            'OptionValue' => 'BFGoodrich',
                            'Value' => ''
                        ],
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                            'AttributeName' => 'coker_product_type',
                            'Operator' => '==',
                            'OptionValue' => 'Tire',
                            'Value' => ''
                        ],
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                            'AttributeName' => 'tire_build',
                            'Operator' => '==',
                            'OptionValue' => 'Bias Ply',
                            'Value' => ''
                        ],
                    ]
            ]
        ],
        [
            'Name' => 'Muscle Car Tires Free Shipping',
            'Description' => 'Muscle Car Tires Free Shipping',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 1000,
            'UsesPerCustomer' => 1000,
            'DiscountStep' => 0,
            'DiscountAmount' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'CouponCode' => 'MUSCLEFREE',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => false,
            'SortOrder' => 5,
            'SimpleFreeShipping' => true,
            'Condition' => [
                'Children' =>
                    [
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                            'AttributeName' => 'country_id',
                            'Operator' => '==',
                            'Value' => 'US'
                        ],
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                            'AttributeName' => 'shipping_method',
                            'Operator' => '==',
                            'Value' => 'fedex_FEDEX_GROUND'
                        ]
                    ]
            ],
            'ActionCondition' => [
                'Children' =>
                    [
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                            'AttributeName' => 'coker_product_type',
                            'Operator' => '==',
                            'OptionValue' => 'Tire',
                            'Value' => ''
                        ],
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                            'AttributeName' => 'tire_type_multi',
                            'Operator' => '{}',
                            'OptionValue' => 'Classic Muscle Car',
                            'Value' => ''
                        ],
                    ]
            ]
        ],
        [
            'Name' => 'Industry Discount',
            'Description' => 'Industry Discount',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [1],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 1,
            'UsesPerCustomer' => 5,
            'DiscountStep' => 0,
            'DiscountAmount' => 20,
            'DiscountQty' => 100,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'UseAutoGeneration' => true,
            'IsActive' => true,
            'SortOrder' => 10,
            'SimpleFreeShipping' => false,
            'Condition' => [],
            'ActionCondition' => []
        ],
        [
            'Name' => 'Goodguys 10',
            'Description' => 'Goodguys 10',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0,1,2,3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 2,
            'UsesPerCustomer' => 0,
            'DiscountStep' => 0,
            'DiscountAmount' => 10,
            'DiscountQty' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'UseAutoGeneration' => true,
            'IsActive' => true,
            'SortOrder' => 1,
            'SimpleFreeShipping' => false,
            'Condition' => [
                'Children' =>
                    [
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                            'AttributeName' => 'country_id',
                            'Operator' => '==',
                            'Value' => 'US'
                        ]
                    ]
            ],
            'ActionCondition' => []
        ],
        [
            'Name' => 'Lowrider Survey',
            'Description' => 'Lowrider Survey',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0,1,2,3],
            'WebsiteIds' => [],
            'IsRss' => false,
            'UsesPerCoupon' => 2,
            'UsesPerCustomer' => 0,
            'DiscountStep' => 0,
            'DiscountAmount' => 10,
            'DiscountQty' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'UseAutoGeneration' => true,
            'IsActive' => true,
            'SortOrder' => 1,
            'SimpleFreeShipping' => false,
            'Condition' => [
                'Children' =>
                    [
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                            'AttributeName' => 'country_id',
                            'Operator' => '==',
                            'Value' => 'US'
                        ]
                    ]
            ],
            'ActionCondition' => []
        ],
        [
            'Name' => 'DotDigital Contest Thank You Code',
            'Description' => 'DotDigital Contest Thank You Code',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0,1,2,3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 1,
            'UsesPerCustomer' => 1,
            'DiscountStep' => 0,
            'DiscountAmount' => 10,
            'DiscountQty' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'UseAutoGeneration' => true,
            'IsActive' => true,
            'SortOrder' => 1,
            'SimpleFreeShipping' => false,
            'Condition' => [
                'Children' =>
                    [
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                            'AttributeName' => 'country_id',
                            'Operator' => '==',
                            'Value' => 'US'
                        ]
                    ]
            ],
            'ActionCondition' => []
        ],
        [
            'Name' => 'Unlock 10',
            'Description' => 'New Email Signup 10 Off UNLOCK',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0,1,2,3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 2,
            'UsesPerCustomer' => 0,
            'DiscountStep' => 0,
            'DiscountAmount' => 10,
            'DiscountQty' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'UseAutoGeneration' => true,
            'IsActive' => true,
            'SortOrder' => 1,
            'SimpleFreeShipping' => false,
            'Condition' => [
                'Children' =>
                    [
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                            'AttributeName' => 'country_id',
                            'Operator' => '==',
                            'Value' => 'US'
                        ]
                    ]
            ],
            'ActionCondition' => []
        ],
        [
            'Name' => 'We Miss You !',
            'Description' => 'Win Back | We Miss You',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0,1,2,3],
            'WebsiteIds' => [],
            'IsRss' => false,
            'UsesPerCoupon' => 1,
            'UsesPerCustomer' => 1,
            'DiscountStep' => 0,
            'DiscountAmount' => 20,
            'DiscountQty' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'UseAutoGeneration' => true,
            'IsActive' => true,
            'SortOrder' => 1,
            'SimpleFreeShipping' => false,
            'Condition' => [
                'Children' =>
                    [
                        [
                            'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                            'AttributeName' => 'country_id',
                            'Operator' => '==',
                            'Value' => 'US'
                        ]
                    ]
            ],
            'ActionCondition' => []
        ]
    ];

    public function apply(): void
    {
        $this->appState->emulateAreaCode(
            AppArea::AREA_ADMINHTML,
            function () {
                $arrCreatedRule = [];
                foreach (self::ARR_RULES as $ruleData) {
                    $createdRule = $this->createCartRule($ruleData);
                }
            }
        );
    }

    private function createCartRule($data)
    {
        $cokerWebsiteId = $this->storeManager->getWebsite(CokerStoreInterface::COKER_WEBSITE_CODE)->getId();
        $newRule = $this->ruleInterfaceFactory->create();
        $newRule->setName($data['Name'])
            ->setDescription($data['Description'])
            ->setIsAdvanced($data['IsAdvanced'])
            ->setStopRulesProcessing($data['StopRulesProcessing'])
            ->setCustomerGroupIds($data['CustomerGroupIds'])
            ->setWebsiteIds([$cokerWebsiteId])
            ->setIsRss($data['IsRss'])
            ->setUsesPerCoupon($data['UsesPerCoupon'])
            ->setUsesPerCustomer($data['UsesPerCustomer'])
            ->setDiscountStep($data['DiscountStep'])
            ->setDiscountQty(!empty($data['DiscountQty']) ?$data['DiscountQty']: 0)
            ->setUseAutoGeneration(!empty($data['UseAutoGeneration']) ?$data['UseAutoGeneration']: 0)
            ->setCouponType($data['CouponType'])
            ->setSimpleAction($data['SimpleAction'])
            ->setDiscountAmount($data['DiscountAmount'])
            ->setSortOrder($data['SortOrder'])
            ->setIsActive($data['IsActive'])
            ->setSimpleFreeShipping($data['SimpleFreeShipping']);
        if (!empty($data['Condition'])) {
            $childConditions = [];
            foreach ($data['Condition']['Children'] as $condition) {
                if ($condition['Value'] == '' && !empty($condition['OptionValue'])) {
                    $attribute = $this->attributeRepository->get('4', $condition['AttributeName']);
                    $condition['Value'] = $attribute->getSource()->getOptionId($condition['OptionValue']);
                }
                $childConditions[] = $this->conditionInterfaceFactory->create()
                    ->setConditionType($condition['ConditionType'])
                    ->setAttributeName($condition['AttributeName'])
                    ->setOperator($condition['Operator'])
                    ->setValue($condition['Value']);
            }
            if ($childConditions) {
                $ruleCondition = $this->conditionInterfaceFactory->create()
                    ->setConditionType(Combine::class)
                    ->setAggregatorType('all')
                    ->setValue('1')
                    ->setConditions($childConditions);
                $newRule->setCondition($ruleCondition);
            }
        }
        if (!empty($data['ActionCondition'])) {
            $childActionConditions = [];
            foreach ($data['ActionCondition']['Children']  as $condition) {
                if ($condition['Value'] == '' && !empty($condition['OptionValue'])) {
                    $attribute = $this->attributeRepository->get('4', $condition['AttributeName']);
                    $optionValue = $attribute->getSource()->getOptionId($condition['OptionValue']);
                    $condition['Value'] = ($condition['Operator'] != '{}')?$optionValue:[$optionValue];
                }
                $childActionConditions[] = $this->conditionInterfaceFactory->create()
                    ->setConditionType($condition['ConditionType'])
                    ->setAttributeName($condition['AttributeName'])
                    ->setOperator($condition['Operator'])
                    ->setValue($condition['Value']);
            }
            if ($childActionConditions) {
                $actionCondition = $this->conditionInterfaceFactory->create()
                    ->setConditionType(Combine::class)
                    ->setAggregatorType('all')
                    ->setValue('1')
                    ->setConditions($childActionConditions);
                $newRule->setActionCondition($actionCondition);
            }
        }
        try {
            $newRule = $this->ruleRepository->save($newRule);
            if (!empty($data['CouponCode'])) {
                /** @var CouponInterface $couponCode */
                $couponCode = $this->couponInterfaceFactory->create();
                $couponCode->setRuleId($newRule->getRuleId())
                    ->setCode($data['CouponCode'])
                    ->setIsPrimary(true);
                $this->couponRepository->save($couponCode);
            }
            return $newRule;
        } catch (\Exception $exception) {
            $this->logger->debug($exception);
        }

    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [
            CreateCartPriceRules::class
        ];
    }
}
