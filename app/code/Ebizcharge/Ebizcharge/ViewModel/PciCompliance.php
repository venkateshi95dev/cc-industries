<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\ViewModel;

use Ebizcharge\Ebizcharge\Model\Config as EbizConfig;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Quote\Model\Quote;

/**
 * PCI Compliance ViewModel class
 *
 * Class PciCompliance
 */
class PciCompliance implements ArgumentInterface
{
    /**
     * AVS/CVV Validation controller
     *
     * @const AVS_CVV_VALIDATION_CONTROLLER
     */
    public const AVS_CVV_VALIDATION_URL = 'ebizcharge/cards/validatecvvavscards';

    /**
     * Set PCI add new method response URL
     *
     * @const AVS_CVV_VALIDATION_CONTROLLER
     */
    public const SET_PCI_ADD_METHOD_RESPONSE_URL = 'ebizcharge/pcicompliance/addnewmethodresponse';

    /**
     * @var EbizConfig
     */
    private EbizConfig $ebizConfig;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @var Multishipping
     */
    private Multishipping $multishipping;

    /**
     * @param EbizConfig $ebizConfig
     * @param UrlInterface $urlBuilder
     * @param Multishipping $multishipping
     */
    public function __construct(
        EbizConfig   $ebizConfig,
        UrlInterface $urlBuilder,
        Multishipping $multishipping
    ) {
        $this->ebizConfig = $ebizConfig;
        $this->urlBuilder = $urlBuilder;
        $this->multishipping = $multishipping;
    }

    /**
     * Get card AVS validation URL
     *
     * @return string
     */
    public function getCardAVSValidationURL(): string
    {
        return $this->urlBuilder->getUrl(
            self::AVS_CVV_VALIDATION_URL,
            ['_secure' => true]
        );
    }

    /**
     * Is PCI Compliance enabled
     *
     * @return bool
     */
    public function isPciComplianceEnabled(): bool
    {
        return $this->ebizConfig->getPciComplianceEnabled();
    }

    /**
     * Get PCI add new payment method response setting URL
     *
     * @return string
     */
    public function getPciAddNewMethodResponseUrl(): string
    {
        return $this->urlBuilder->getUrl(
            self::SET_PCI_ADD_METHOD_RESPONSE_URL,
            ['_secure' => true]
        );
    }

    /**
     * Check is recurring enabled
     *
     * @return bool
     */
    public function isRecurringEnabled(): bool
    {
        $storeId = $this->ebizConfig->getStoreId();
        return $this->ebizConfig->isRecurringEnabled($storeId);
    }

    /**
     * Get multi shipping checkout quote
     *
     * @return Quote
     */
    public function getMultiShippingQuote(): Quote
    {
        return $this->multishipping->getQuote();
    }

    /**
     * Get quote items data
     *
     * @param mixed $items
     * @return array
     */
    public function getQuoteItemsData($items = []): array
    {
        $itemsData = [];
        if (!$items) {
            return $itemsData;
        }

        /** @var $item */
        foreach ($items as $item) {
            $itemsData[] = $item->getData();
        }
        return $itemsData;
    }
}
