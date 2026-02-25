<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AdvancedPricing;
use Magento\Directory\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\Website;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Modal;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class for Tier Prices Modifier
 */
class Tier extends AdvancedPricing
{
    public const ADVANCED_PRICING = 'advanced-pricing';
    public const ARGUMENTS = 'arguments';
    public const CONFIG = "config";
    public const CHILDREN = "children";
    public const LABEL = "label";
    public const VALUE = 'value';
    public const FORMELEMENT = 'formElement';
    public const COMPONENTTYPE = 'componentType';
    public const COMPONENT = 'component';
    public const PRICEPATH = '/arguments/data/config';
    public const ADDITIONALCLASSES = 'additionalClasses';
    public const SCOPELABEL = 'scopeLabel';
    public const DATASCOPE = 'dataScope';
    public const DATATYPE = 'dataType';
    public const OPTIONS = "options";

     /**
      * @var LocatorInterface
      */
    public $locator;

    /**
     * @var ModuleManager
     */
    public $moduleManager;

    /**
     * @var GroupManagementInterface
     */
    public $groupManagement;

    /**
     * @var SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;

    /**
     * @var GroupRepositoryInterface
     */
    public $groupRepository;

    /**
     * @var Data
     */
    public $directoryHelper;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var ArrayManager
     */
    public $arrayManager;

    /**
     * @var string
     */
    public $scopeName;

    /**
     * @var array
     */
    public $meta = [];

    /**
     * Tier Constructor
     *
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupManagementInterface $groupManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ModuleManager $moduleManager
     * @param Data $directoryHelper
     * @param ArrayManager $arrayManager
     * @param string $scopeName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct( // NOSONAR
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        GroupRepositoryInterface $groupRepository,
        GroupManagementInterface $groupManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ModuleManager $moduleManager,
        Data $directoryHelper,
        ArrayManager $arrayManager,
        $scopeName = ''
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->groupRepository = $groupRepository;
        $this->groupManagement = $groupManagement;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->moduleManager = $moduleManager;
        $this->directoryHelper = $directoryHelper;
        $this->arrayManager = $arrayManager;
        $this->scopeName = $scopeName;
    }

    /**
     * Modify Meta Information
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->specialPriceDataToInline();
        $this->customizeTierPrice();

        if (isset($this->meta[self::ADVANCED_PRICING])) {
            $this->addAdvancedPriceLink();
            $this->customizeAdvancedPricing();
        }

        return $this->meta;
    }

    /**
     * Modify Data
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Prepare price fields
     *
     * Add currency symbol and validation
     *
     * @param string $fieldCode
     *
     * @return $this
     */
    protected function preparePriceFields($fieldCode)
    {
        $pricePath = $this->arrayManager->findPath($fieldCode, $this->meta, null, self::CHILDREN);

        if ($pricePath) {
            $this->meta = $this->arrayManager->set(
                $pricePath . '/arguments/data/config/addbefore',
                $this->meta,
                $this->getStore()->getBaseCurrency()->getCurrencySymbol()
            );
            $this->meta = $this->arrayManager->merge(
                $pricePath . self::PRICEPATH,
                $this->meta,
                ['validation' => ['validate-zero-or-greater' => true]]
            );
        }

        return $this;
    }

    /**
     * Customize tier price field
     *
     * @return $this|Tier
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function customizeTierPrice()
    {
        $tierPricePath = $this->arrayManager->findPath(
            ProductAttributeInterface::CODE_TIER_PRICE,
            $this->meta,
            null,
            self::CHILDREN
        );

        if ($tierPricePath) {
            $this->meta = $this->arrayManager->merge(
                $tierPricePath,
                $this->meta,
                $this->getTierPriceStructure($tierPricePath)
            );
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($tierPricePath, 0, -3)
                . '/' . ProductAttributeInterface::CODE_TIER_PRICE,
                $this->meta,
                $this->arrayManager->get($tierPricePath, $this->meta)
            );
            $this->meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($tierPricePath, 0, -2),
                $this->meta
            );
        }

        return $this;
    }

    /**
     * Retrieve allowed customer groups
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCustomerGroups()
    {
        if (!$this->moduleManager->isEnabled('Magento_Customer')) {
            return [];
        }
        $customerGroups = [
            [
                self::LABEL => __('ALL GROUPS'),
                self::VALUE => GroupInterface::CUST_GROUP_ALL,
            ]
        ];

        /** @var GroupInterface[] $groups */
        $groups = $this->groupRepository->getList($this->searchCriteriaBuilder->create());
        foreach ($groups->getItems() as $group) {
            $customerGroups[] = [
                self::LABEL => $group->getCode(),
                self::VALUE => $group->getId(),
            ];
        }

        return $customerGroups;
    }

    /**
     * Check tier_price attribute scope is global
     *
     * @return bool
     */
    public function isScopeGlobal()
    {
        return $this->locator->getProduct()
            ->getResource()
            ->getAttribute(ProductAttributeInterface::CODE_TIER_PRICE)
            ->isScopeGlobal();
    }

    /**
     * Get websites list
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getWebsites()
    {
        $websites = [
            [
                self::LABEL => __('All Websites') . ' [' . $this->directoryHelper->getBaseCurrencyCode() . ']',
                self::VALUE => 0,
            ]
        ];
        $product = $this->locator->getProduct();

        if (!$this->isScopeGlobal() && $product->getStoreId()) {
            /** @var Website $website */
            $website = $this->getStore()->getWebsite();

            $websites[] = [
                self::LABEL => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                self::VALUE => $website->getId(),
            ];
        } elseif (!$this->isScopeGlobal()) {
            $websitesList = $this->storeManager->getWebsites();
            $productWebsiteIds = $product->getWebsiteIds();
            foreach ($websitesList as $website) {
                /** @var Website $website */
                if (!in_array($website->getId(), $productWebsiteIds)) {
                    continue;
                }
                $websites[] = [
                    self::LABEL => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                    self::VALUE => $website->getId(),
                ];
            }
        }

        return $websites;
    }

    /**
     * Retrieve default value for customer group
     *
     * @return int
     * @throws LocalizedException
     */
    public function getDefaultCustomerGroup()
    {
        return $this->groupManagement->getAllCustomersGroup()->getId();
    }

    /**
     * Retrieve default value for website
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getDefaultWebsite()
    {
        if ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()) {
            return $this->storeManager->getStore($this->locator->getProduct()->getStoreId())->getWebsiteId();
        }

        return 0;
    }

    /**
     * Show group prices grid website column
     *
     * @return bool
     */
    public function isShowWebsiteColumn()
    {
        if ($this->isScopeGlobal() || $this->storeManager->isSingleStoreMode()) {
            return false;
        }
        return true;
    }

    /**
     * Show website column and switcher for group price table
     *
     * @return bool
     */
    public function isMultiWebsites()
    {
        return !$this->storeManager->isSingleStoreMode();
    }

    /**
     * Check is allow change website value for combination
     *
     * @return bool
     */
    public function isAllowChangeWebsite()
    {
        if (!$this->isShowWebsiteColumn() || $this->locator->getProduct()->getStoreId()) {
            return false;
        }
        return true;
    }

    /**
     * Add link to open Advanced Pricing Panel
     *
     * @return $this
     */
    public function addAdvancedPriceLink()
    {
        $pricePath = $this->arrayManager->findPath(
            ProductAttributeInterface::CODE_PRICE,
            $this->meta,
            null,
            self::CHILDREN
        );

        if ($pricePath) {
            $this->meta = $this->arrayManager->merge(
                $pricePath . self::PRICEPATH,
                $this->meta,
                [self::ADDITIONALCLASSES => 'admin__field-small']
            );

            $advancedPricingButton[self::ARGUMENTS]['data'][self::CONFIG] = [
                'displayAsLink' => true,
                self::FORMELEMENT => Container::NAME,
                self::COMPONENTTYPE => Container::NAME,
                self::COMPONENT => 'Magento_Ui/js/form/components/button',
                'template' => 'ui/form/components/button/container',
                'actions' => [
                    [
                        'targetName' => $this->scopeName . '.advanced_pricing_modal',
                        'actionName' => 'toggleModal',
                    ]
                ],
                'title' => __('Advanced Pricing'),
                'additionalForGroup' => true,
                'provider' => false,
                'source' => 'product_details',
                'sortOrder' =>
                    $this->arrayManager->get($pricePath . '/arguments/data/config/sortOrder', $this->meta) + 1,
            ];

            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($pricePath, 0, -1) . '/advanced_pricing_button',
                $this->meta,
                $advancedPricingButton
            );
        }

        return $this;
    }

    /**
     * Get tier price dynamic rows structure
     *
     * @param string $tierPricePath
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getTierPriceStructure($tierPricePath)
    {
        return [
            self::ARGUMENTS => [
                'data' => [
                    self::CONFIG => [
                        self::COMPONENTTYPE => 'dynamicRows',
                        self::LABEL => __('Tier Price'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        self::DATASCOPE => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => true,
                        'sortOrder' =>
                            $this->arrayManager->get($tierPricePath . '/arguments/data/config/sortOrder', $this->meta),
                    ],
                ],
            ],
            self::CHILDREN => [
                'record' => [
                    self::ARGUMENTS => [
                        'data' => [
                            self::CONFIG => [
                                self::COMPONENTTYPE => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                self::COMPONENT => 'Magento_Ui/js/dynamic-rows/record',
                                self::DATASCOPE => '',
                            ],
                        ],
                    ],
                    self::CHILDREN => [
                        'website_id' => [
                            self::ARGUMENTS => [
                                'data' => [
                                    self::CONFIG => [
                                        self::DATATYPE => Text::NAME,
                                        self::FORMELEMENT => Select::NAME,
                                        self::COMPONENTTYPE => Field::NAME,
                                        self::DATASCOPE => 'website_id',
                                        self::LABEL => __('Website'),
                                        self::OPTIONS => $this->getWebsites(),
                                        self::VALUE => $this->getDefaultWebsite(),
                                        'visible' => $this->isMultiWebsites(),
                                        'disabled' => ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()),
                                    ],
                                ],
                            ],
                        ],
                        'cust_group' => [
                            self::ARGUMENTS => [
                                'data' => [
                                    self::CONFIG => [
                                        self::FORMELEMENT => Select::NAME,
                                        self::COMPONENTTYPE => Field::NAME,
                                        self::DATATYPE => Text::NAME,
                                        self::DATASCOPE => 'cust_group',
                                        self::LABEL => __('Customer Group'),
                                        self::OPTIONS => $this->getCustomerGroups(),
                                        self::VALUE => $this->getDefaultCustomerGroup(),
                                    ],
                                ],
                            ],
                        ],
                        'price' => [
                            self::ARGUMENTS => [
                                'data' => [
                                    self::CONFIG => [
                                        self::COMPONENTTYPE => Field::NAME,
                                        self::FORMELEMENT => Input::NAME,
                                        self::DATATYPE => Price::NAME,
                                        self::LABEL => __('Price'),
                                        'enableLabel' => true,
                                        self::DATASCOPE => 'price',
                                        'addbefore' => $this->getLocator(),
                                    ],
                                ],
                            ],
                        ],
                        'price_qty' => [
                            self::ARGUMENTS => [
                                'data' => [
                                    self::CONFIG => [
                                        self::FORMELEMENT => Input::NAME,
                                        self::COMPONENTTYPE => Field::NAME,
                                        self::DATATYPE => Number::NAME,
                                        self::LABEL => __('Quantity'),
                                        self::DATASCOPE => 'price_qty',
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            self::ARGUMENTS => [
                                'data' => [
                                    self::CONFIG => [
                                        self::COMPONENTTYPE => 'actionDelete',
                                        self::DATATYPE => Text::NAME,
                                        self::LABEL => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Special price data move to inline group
     *
     * @return $this
     */
    public function specialPriceDataToInline()
    {
        $pathFrom = $this->arrayManager->findPath('special_from_date', $this->meta, null, self::CHILDREN);
        $pathTo = $this->arrayManager->findPath('special_to_date', $this->meta, null, self::CHILDREN);

        if ($pathFrom && $pathTo) {
            $this->meta = $this->arrayManager->merge(
                $this->arrayManager->slicePath($pathFrom, 0, -2) . self::PRICEPATH,
                $this->meta,
                [
                    self::LABEL => __('Special Price From'),
                    self::ADDITIONALCLASSES => 'admin__control-grouped-date',
                    'breakLine' => false,
                    self::COMPONENT => 'Magento_Ui/js/form/components/group',
                    self::SCOPELABEL =>
                        $this->arrayManager->get($pathFrom . '/arguments/data/config/scopeLabel', $this->meta),
                ]
            );
            $this->meta = $this->arrayManager->merge(
                $pathFrom . self::PRICEPATH,
                $this->meta,
                [
                    self::LABEL => __('Special Price From'),
                    self::SCOPELABEL => null,
                    self::ADDITIONALCLASSES => 'admin__field-date'
                ]
            );
            $this->meta = $this->arrayManager->merge(
                $pathTo . self::PRICEPATH,
                $this->meta,
                [
                    self::LABEL => __('To'),
                    self::SCOPELABEL => null,
                    self::ADDITIONALCLASSES => 'admin__field-date'
                ]
            );
            // Move special_to_date to special_from_date container
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($pathFrom, 0, -1) . '/special_to_date',
                $this->meta,
                $this->arrayManager->get(
                    $pathTo,
                    $this->meta
                )
            );
            $this->meta = $this->arrayManager->remove($this->arrayManager->slicePath($pathTo, 0, -2), $this->meta);
        }

        return $this;
    }

    /**
     * Customize Advanced Pricing Panel
     *
     * @return $this
     */
    public function customizeAdvancedPricing()
    {
        $this->meta[self::ADVANCED_PRICING][self::ARGUMENTS]['data'][self::CONFIG]['opened'] = true;
        $this->meta[self::ADVANCED_PRICING][self::ARGUMENTS]['data'][self::CONFIG]['collapsible'] = false;
        $this->meta[self::ADVANCED_PRICING][self::ARGUMENTS]['data'][self::CONFIG][self::LABEL] = '';

        $this->meta['advanced_pricing_modal'][self::ARGUMENTS]['data'][self::CONFIG] = [
            'isTemplate' => false,
            self::COMPONENTTYPE => Modal::NAME,
            self::DATASCOPE => '',
            'provider' => 'product_form.product_form_data_source',
            'onCancel' => 'actionDone',
            self::OPTIONS => [
                'title' => __('Advanced Pricing'),
                'buttons' => [
                    [
                        'text' => __('Done'),
                        'class' => 'action-primary',
                        'actions' => [
                            [
                                'targetName' => '${ $.name }',
                                'actionName' => 'actionDone'
                            ]
                        ]
                    ],
                ],
            ],
        ];

        $this->meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(
                static::CONTAINER_PREFIX . ProductAttributeInterface::CODE_PRICE,
                $this->meta,
                null,
                self::CHILDREN
            ),
            $this->meta,
            [
                self::ARGUMENTS => [
                    'data' => [
                        self::CONFIG => [
                            self::COMPONENT => 'Magento_Ui/js/form/components/group',
                        ],
                    ],
                ],
            ]
        );
        $apm = 'advanced_pricing_modal';
        $this->meta[$apm][self::CHILDREN][self::ADVANCED_PRICING] = $this->meta[self::ADVANCED_PRICING];
        unset($this->meta[self::ADVANCED_PRICING]);

        return $this;
    }

    /**
     * Get locator
     *
     * @return mixed
     */
    public function getLocator()
    {
        return $this->locator->getStore()
            ->getBaseCurrency()
            ->getCurrencySymbol();
    }

    /**
     * Retrieve store
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->locator->getStore();
    }
}
