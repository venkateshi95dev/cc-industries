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

class CreateCartPriceRules implements DataPatchInterface
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
            'Name' => 'FREE Shipping Apparel (Standing)',
            'Description' => 'FREE Shipping Apparel (Standing)',
            'IsAdvanced' => true,
            'StopRulesProcessing' => true,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 0,
            'UsesPerCustomer' => 0,
            'DiscountStep' => 0,
            'DiscountAmount' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_NO_COUPON,
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => true,
            'SimpleFreeShipping' => true,
            'SortOrder' => 0,
            'Condition' => [

                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'country_id',
                        'Operator' => '==',
                        'Value' => 'US'
                    ]
                ]
            ],
            'ActionCondition' => [

                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                        'AttributeName' => 'coker_product_type',
                        'Operator' => '==',
                        'OptionValue' => 'Apparel',
                        'Value' => ''
                    ]
                ]
            ]
        ],
        [
            'Name' => 'Radial T/A Free Shipping',
            'Description' => 'Radial T/A Free Shipping',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 0,
            'UsesPerCustomer' => 0,
            'DiscountStep' => 0,
            'DiscountAmount' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_NO_COUPON,
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => true,
            'SortOrder' => 0,
            'SimpleFreeShipping' => true,
            'Condition' => [
                'Children' => [
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
                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                        'AttributeName' => 'tire_series',
                        'Operator' => '==',
                        'OptionValue' => 'Radial TA',
                        'Value' => ''
                    ]
                ]
            ]
        ],
        [
            'Name' => 'FREE Shipping Over $99',
            'Description' => 'FREE Shipping Over $99',
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
            'CouponCode' => 'JULY4SAVE',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => false,
            'SimpleFreeShipping' => true,
            'SortOrder' => 1,
            'Condition' => [
                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'country_id',
                        'Operator' => '==',
                        'Value' => 'US'
                    ],
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'base_subtotal',
                        'Operator' => '>=',
                        'Value' => '98.00'
                    ]
                ]
            ],
            'ActionCondition' => []
        ],
        [
            'Name' => 'FREE Shipping Over $499',
            'Description' => 'FREE Shipping Over $499',
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
            'CouponCode' => 'FREESHIP499',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => false,
            'SimpleFreeShipping' => true,
            'SortOrder' => 1,
            'Condition' => [
                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'country_id',
                        'Operator' => '==',
                        'Value' => 'US'
                    ],
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'base_subtotal',
                        'Operator' => '>=',
                        'Value' => '498.00'
                    ]
                ]
            ],
            'ActionCondition' => []
        ],
        [
            'Name' => '10% Welcome Back',
            'Description' => '10% Welcome Back',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 999999999,
            'UsesPerCustomer' => 999999999,
            'DiscountStep' => 0,
            'DiscountAmount' => 10,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'CouponCode' => 'WELCOMEBACK10',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => false,
            'SimpleFreeShipping' => false,
            'SortOrder' => 1,
            'Condition' => [
                'Children' => [
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
            'Name' => 'HAGERTY 20%',
            'Description' => '20% HAGERTY',
            'IsAdvanced' => true,
            'StopRulesProcessing' => true,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 999999999,
            'UsesPerCustomer' => 999999999,
            'DiscountStep' => 0,
            'DiscountAmount' => 20,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'CouponCode' => 'HAGERTY20',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => false,
            'SimpleFreeShipping' => false,
            'SortOrder' => 1,
            'Condition' => [],
            'ActionCondition' => []
        ],
        [
            'Name' => 'ISNOFS',
            'Description' => 'ISNOFS',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 0,
            'UsesPerCustomer' => 0,
            'DiscountStep' => 0,
            'DiscountAmount' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'CouponCode' => 'ISNOFS',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => false,
            'SimpleFreeShipping' => true,
            'SortOrder' => 0,
            'Condition' => [

                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'country_id',
                        'Operator' => '==',
                        'Value' => 'US'
                    ]
                ]
            ],
            'ActionCondition' => [

                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                        'AttributeName' => 'inventory_disposition',
                        'Operator' => '==',
                        'OptionValue' => 'OS10',
                        'Value' => ''
                    ]
                ]
            ]
        ],
        [
            'Name' => 'CHATTMOTORCARFEST',
            'Description' => 'CHATTMOTORCARFEST Free Shipping',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 0,
            'UsesPerCustomer' => 0,
            'DiscountStep' => 0,
            'DiscountAmount' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'CouponCode' => 'CHATTMOTORCARFEST',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => false,
            'SimpleFreeShipping' => true,
            'SortOrder' => 1,
            'Condition' => [

                'Children' => [
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
            'Name' => 'GREATRACER23',
            'Description' => '2023 Great Racer Offer',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 0,
            'UsesPerCustomer' => 0,
            'DiscountStep' => 0,
            'DiscountAmount' => 15,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'CouponCode' => 'GREATRACER23',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => false,
            'SimpleFreeShipping' => false,
            'SortOrder' => 0,
            'Condition' => [

                'Children' => [
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
            'Name' => 'Hagerty Deal of the Week 10%',
            'Description' => 'HAGERTYDOW',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 1000,
            'UsesPerCustomer' => 1000,
            'DiscountStep' => 0,
            'DiscountAmount' => 10,
            'DiscountQty' => 99999999,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'CouponCode' => 'HAGERTYDOW',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => false,
            'SimpleFreeShipping' => false,
            'SortOrder' => 1,
            'Condition' => [
                'AggregatorType' => 'any',
                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'country_id',
                        'Operator' => '==',
                        'Value' => 'US'
                    ],
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'country_id',
                        'Operator' => '==',
                        'Value' => 'CA'
                    ]
                ]
            ],
            'ActionCondition' => []
        ],
        [
            'Name' => 'Hagerty Drivers Club 10% Off (module version)',
            'Description' => 'Hagerty Drivers Club 10% Off (module version)',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 1000,
            'UsesPerCustomer' => 1000,
            'DiscountStep' => 0,
            'DiscountAmount' => 10,
            'DiscountQty' => 99999999,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'CouponCode' => 'use-hagerty-drivers-club-10-code-2023',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => true,
            'SimpleFreeShipping' => false,
            'SortOrder' => 1,
            'Condition' => [
                'AggregatorType'=>'any',
                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'country_id',
                        'Operator' => '==',
                        'Value' => 'US'
                    ],
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'country_id',
                        'Operator' => '==',
                        'Value' => 'CA'
                    ]
                ]
            ],
            'ActionCondition' => []
        ],
        [
            'Name' => 'HAGERTYDRIVERSCLUB10',
            'Description' => 'Hagerty Drivers Club 10% 2023',
            'IsAdvanced' => true,
            'StopRulesProcessing' => true,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => false,
            'UsesPerCoupon' => 99999,
            'UsesPerCustomer' => 0,
            'DiscountStep' => 0,
            'DiscountAmount' => 10,
            'DiscountQty' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'CouponCode' => 'HAGERTYDRIVERSCLUB10',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => true,
            'SimpleFreeShipping' => false,
            'SortOrder' => 1,
            'Condition' => [
                'Children' => [
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
            'Name' => 'Hagerty Driver Club (Employee Version)',
            'Description' => 'Hagerty Driver Club (Employee Version)',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 1000,
            'UsesPerCustomer' => 1000,
            'DiscountStep' => 0,
            'DiscountAmount' => 10,
            'DiscountQty' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'CouponCode' => 'HAGERTY10EMPLOYEE',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => true,
            'SimpleFreeShipping' => false,
            'SortOrder' => 50,
            'Condition' => [],
            'ActionCondition' => []
        ],
        [
            'Name' => 'HCCA15',
            'Description' => 'HCCA 15% OFF',
            'IsAdvanced' => true,
            'StopRulesProcessing' => true,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => 1,
            'UsesPerCoupon' => 0,
            'UsesPerCustomer' => 0,
            'DiscountStep' => 0,
            'DiscountAmount' => 15,
            'DiscountQty' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'CouponCode' => 'HCCA15',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => true,
            'SimpleFreeShipping' => false,
            'SortOrder' => 0,
            'Condition' => [
                'Children' => [
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
            'Name' => 'HAMB Industry Alliance Discount',
            'Description' => 'HAMB Industry Alliance Discount',
            'IsAdvanced' => true,
            'StopRulesProcessing' => true,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => 1,
            'UsesPerCoupon' => 999999999,
            'UsesPerCustomer' =>1000,
            'DiscountStep' => 0,
            'DiscountAmount' => 15,
            'DiscountQty' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'CouponCode' => 'alli169',
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => true,
            'SimpleFreeShipping' => false,
            'SortOrder' => 10,
            'Condition' => [
                'AggregatorType'=>'any',
                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'country_id',
                        'Operator' => '==',
                        'Value' => 'US'
                    ],
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'country_id',
                        'Operator' => '==',
                        'Value' => 'NO'
                    ]
                ]
            ],
            'ActionCondition' => []
        ],
        [
            'Name' => 'GARAGE-DD-FS25',
            'Description' => 'GARAGE-DD-FS25',
            'IsAdvanced' => true,
            'StopRulesProcessing' => true,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 0,
            'UsesPerCustomer' => 0,
            'DiscountStep' => 0,
            'DiscountAmount' => 25,
            'CouponType' => RuleInterface::COUPON_TYPE_NO_COUPON,
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => true,
            'SimpleFreeShipping' => false,
            'SortOrder' => 0,
            'Condition' => [

                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'country_id',
                        'Operator' => '==',
                        'Value' => 'US'
                    ],
                    [
                        'ConditionType' => \Amasty\Conditions\Model\Rule\Condition\Product::class,
                        'AttributeName' => 'quantity_in_stock',
                        'Operator' => '>=',
                        'Value' => '1'
                    ]
                ]
            ],
            'ActionCondition' => [

                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                        'AttributeName' => 'inventory_disposition',
                        'Operator' => '==',
                        'OptionValue' => 'DD25',
                        'Value' => ''
                    ]
                ]
            ]
        ],
        [
            'Name' => 'GARAGE-OS-FS10',
            'Description' => 'GARAGE-OS-FS10',
            'IsAdvanced' => true,
            'StopRulesProcessing' => true,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 0,
            'UsesPerCustomer' => 0,
            'DiscountStep' => 0,
            'DiscountAmount' => 10,
            'CouponType' => RuleInterface::COUPON_TYPE_NO_COUPON,
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => true,
            'SimpleFreeShipping' => false,
            'SortOrder' => 0,
            'Condition' => [

                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'country_id',
                        'Operator' => '==',
                        'Value' => 'US'
                    ],
                    [
                        'ConditionType' => \Amasty\Conditions\Model\Rule\Condition\Product::class,
                        'AttributeName' => 'quantity_in_stock',
                        'Operator' => '>=',
                        'Value' => '1'
                    ]
                ]
            ],
            'ActionCondition' => [
                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                        'AttributeName' => 'inventory_disposition',
                        'Operator' => '==',
                        'OptionValue' => 'OS10',
                        'Value' => ''
                    ]
                ]
            ]
        ],
        [
            'Name' => 'GARAGE-OB-FS50',
            'Description' => 'GARAGE-OB-FS50',
            'IsAdvanced' => true,
            'StopRulesProcessing' => true,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => true,
            'UsesPerCoupon' => 0,
            'UsesPerCustomer' => 0,
            'DiscountStep' => 0,
            'DiscountAmount' => 50,
            'CouponType' => RuleInterface::COUPON_TYPE_NO_COUPON,
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'IsActive' => true,
            'SimpleFreeShipping' => false,
            'SortOrder' => 0,
            'Condition' => [
                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'country_id',
                        'Operator' => '==',
                        'Value' => 'US'
                    ],
                    [
                        'ConditionType' => \Amasty\Conditions\Model\Rule\Condition\Product::class,
                        'AttributeName' => 'quantity_in_stock',
                        'Operator' => '>=',
                        'Value' => '1'
                    ]
                ]
            ],
            'ActionCondition' => [
                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                        'AttributeName' => 'inventory_disposition',
                        'Operator' => '==',
                        'OptionValue' => 'OB50',
                        'Value' => ''
                    ]
                ]
            ]
        ],
        [
            'Name' => 'Abandoned Cart Discount | Email Promo',
            'Description' => 'Abandoned Cart Discount | Email Promo',
            'IsAdvanced' => true,
            'StopRulesProcessing' => false,
            'CustomerGroupIds' => [0, 1, 2, 3],
            'WebsiteIds' => [],
            'IsRss' => 1,
            'UsesPerCoupon' => 0,
            'UsesPerCustomer' =>2,
            'DiscountStep' => 0,
            'DiscountAmount' => 10,
            'DiscountQty' => 0,
            'CouponType' => RuleInterface::COUPON_TYPE_SPECIFIC_COUPON,
            'SimpleAction' => RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
            'UseAutoGeneration' => true,
            'IsActive' => true,
            'SimpleFreeShipping' => false,
            'SortOrder' => 1,
            'Condition' => [
                'AggregatorType'=>'any',
                'Children' => [
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'country_id',
                        'Operator' => '==',
                        'Value' => 'US'
                    ],
                    [
                        'ConditionType' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'AttributeName' => 'country_id',
                        'Operator' => '==',
                        'Value' => 'NO'
                    ]
                ]
            ],
            'ActionCondition' => []
        ],
    ];

    public function apply(): void
    {
        $this->appState->emulateAreaCode(
            AppArea::AREA_ADMINHTML,
            function () {
                $arrCreatedRule = [];
                foreach (self::ARR_RULES as $ruleData) {
                    $createdRule = $this->createCartRule($ruleData);
                    $arrCreatedRule[] = $createdRule->getRuleId();
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
            ->setIsActive($data['IsActive'])
            ->setSortOrder($data['SortOrder'])
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
                $ConditionClass = !empty($data['Condition']['Class']) ? $data['Condition']['Class'] : Combine::class;
                $AggregatorType = !empty($data['Condition']['AggregatorType']) ? $data['Condition']['AggregatorType'] : 'all';
                $Value = !empty($data['Condition']['Value']) ? $data['Condition']['Value'] : '1';
                $ruleCondition = $this->conditionInterfaceFactory->create()
                    ->setConditionType($ConditionClass)
                    ->setAggregatorType($AggregatorType)
                    ->setValue($Value)
                    ->setConditions($childConditions);
                $newRule->setCondition($ruleCondition);
            }
        }
        if (!empty($data['ActionCondition'])) {
            $childActionConditions = [];
            foreach ($data['ActionCondition']['Children'] as $condition) {
                if ($condition['Value'] == '' && !empty($condition['OptionValue'])) {
                    $attribute = $this->attributeRepository->get('4', $condition['AttributeName']);
                    $condition['Value'] = $attribute->getSource()->getOptionId($condition['OptionValue']);
                }
                $childActionConditions[] = $this->conditionInterfaceFactory->create()
                    ->setConditionType($condition['ConditionType'])
                    ->setAttributeName($condition['AttributeName'])
                    ->setOperator($condition['Operator'])
                    ->setValue($condition['Value']);
            }
            if ($childActionConditions) {
                $ConditionClass = !empty($data['ActionCondition']['Class']) ? $data['ActionCondition']['Class'] : Combine::class;
                $AggregatorType = !empty($data['ActionCondition']['AggregatorType']) ? $data['ActionCondition']['AggregatorType'] : 'all';
                $Value = !empty($data['ActionCondition']['Value']) ? $data['ActionCondition']['Value'] : '1';
                $actionCondition = $this->conditionInterfaceFactory->create()
                    ->setConditionType($ConditionClass)
                    ->setAggregatorType($AggregatorType)
                    ->setValue($Value)
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
            CreateCokerWebiste::class,
            CreateWVWebiste::class,
            SetShareCustomerAccountsToWebsite::class
        ];
    }
}
