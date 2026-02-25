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

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column\Recurring;

use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Recurring Customer Billing Address Ui Column Component
 *
 * Class CustomerEmail
 */
class BillingAddressRenderer extends Column
{
    /**
     * @var Escaper
     */
    protected Escaper $_escaper;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var UrlInterface
     */
    protected UrlInterface $_urlInterface;

    /**
     * CustomerEmail constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param CustomerFactory $customerFactory
     * @param UrlInterface $urlInterface
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        Escaper            $escaper,
        CustomerFactory    $customerFactory,
        UrlInterface       $urlInterface,
        array              $components = [],
        array              $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        /** @var  escaper */
        $this->_escaper = $escaper;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var  _urlInterface */
        $this->_urlInterface = $urlInterface;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $customerBillingAddressId = $item['billing_address_id'];
                $columnName = nl2br($this->_escaper->escapeHtml($item[$this->getData('name')]));
                $item[$this->getData('name')] = $this->getLabelInfo($columnName, $customerBillingAddressId);
            }
        }

        return $dataSource;
    }

    /**
     * Get Label Info
     *
     * @param mixed $gridLabel
     * @param null|mixed $customerBillingAddressId
     * @return string
     */
    private function getLabelInfo($gridLabel, $customerBillingAddressId = null): string
    {
        $addressHtml = $customerBillingAddressId;
        $cssClass = 'bank-account-card-icon';

        try {
            /** @var  $customer */
            $customerAddress = $this->_customerFactory->create()->loadCustomerAddressById($customerBillingAddressId);
            $customerAddressStreet = $customerAddress->getStreet() ?? [];

            if ($customerAddress) {
                $address1 = isset($customerAddressStreet[0]) ? $customerAddressStreet[0] : "";
                $address2 = isset($customerAddressStreet[1]) ? $customerAddressStreet[1] : "";

                if (!empty($address1)) {
                    $addressHtml = $address1;
                }
                if (!empty($address2)) {
                    $addressHtml .= " " . $address2;
                }
                if (!empty($customerAddress->getCity())) {
                    $addressHtml .= " " . $customerAddress->getCity() ?? "";
                }
                if (!empty($customerAddress->getRegion())) {
                    $region = $customerAddress->getRegion() ? $customerAddress->getRegion()->getRegion() : "";
                    $addressHtml .= ", " . $region;

                }
                if (!empty($customerAddress->getCountryId())) {
                    $addressHtml .= " " . $customerAddress->getCountryId() ?? "";
                }
            }

            return '<span class="' . $cssClass . '"><span>' . $addressHtml . '</span></span>';

        } catch (LocalizedException $localizedException) {
            return '<span class="' . $cssClass . '"><span>' . $customerBillingAddressId . '</span></span>';
        }
    }
}
