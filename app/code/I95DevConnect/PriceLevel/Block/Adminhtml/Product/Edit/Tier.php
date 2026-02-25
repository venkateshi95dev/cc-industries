<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Block\Adminhtml\Product\Edit;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AdvancedPricing;

/**
 * Class AdvancedPricing
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Tier extends AdvancedPricing
{
    public const ARGUMENTS = 'arguments';
    public const CONFIG = 'config';
    public const LABEL = 'label';
    public const DATASCOPE = 'dataScope';
    public const DATATYPE = 'dataType';
    public const FORMELEMENT = 'formElement';
    public const COMPONENTTYPE = 'componentType';

    /**
     * Get tier price dynamic rows structure
     *
     * @param string $tierPricePath
     * @return array
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
            'children' => [
                'record' => [
                    self::ARGUMENTS => [
                        'data' => [
                            self::CONFIG => [
                                self::COMPONENTTYPE => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                self::DATASCOPE => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'website_id' => [
                            self::ARGUMENTS => [
                                'data' => [
                                    self::CONFIG => [
                                        self::DATATYPE => Text::NAME,
                                        self::FORMELEMENT => Select::NAME,
                                        self::COMPONENTTYPE => Field::NAME,
                                        self::DATASCOPE => 'website_id',
                                        self::LABEL => __('Website'),
                                        'options' => $this->getWebsites(),
                                        'value' => $this->getDefaultWebsite(),
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
                                        'options' => $this->getCustomerGroups(),
                                        'value' => $this->getDefaultCustomerGroup(),
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
                                        'addbefore' => $this->locator->getStore()
                                                                     ->getBaseCurrency()
                                                                     ->getCurrencySymbol(),
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
}
