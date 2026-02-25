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

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column\PaymentHistory;

use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Customer\Model\Address;
use Magento\Framework\Url;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * This class is used to add different classes to status as per status value
 *
 * Class ResultCardInfo
 */
class RenderComplexData extends Column
{
    /**
     * Payment Store ID
     *
     * @const: STORE_ID
     */
    public const STORE_ID = 'store_id';

    /**
     * Payment customer ID
     *
     * @const: CUSTOMER_ID
     */
    public const CUSTOMER_ID = 'customer_id';

    /**
     * Payment Response
     *
     * @const: PAYMENT_RESPONSE
     */
    public const PAYMENT_RESPONSE = 'payment_response';

    /**
     * Payment Payment Detail
     *
     * @const: PAYMENT_DETAIL
     */
    public const PAYMENT_DETAIL = 'payment_detail';

    /**
     * Payment Line Items
     *
     * @const: PAYMENT_RESPONSE
     */
    public const PAYMENT_LINE_ITEMS = 'payment_line_items';

    /**
     * Payment Check Data
     *
     * @const: PAYMENT_RESPONSE
     */
    public const PAYMENT_CHECK_DATA = 'payment_check_data';

    /**
     * Payment Billing Address
     *
     * @const: PAYMENT_BILLING_ADDRESS
     */
    public const PAYMENT_BILLING_ADDRESS = 'payment_billing_address';

    /**
     * Payment Billing Address Address
     *
     * @const: PAYMENT_BILLING_ADDRESS_ADDRESS
     */
    public const PAYMENT_BILLING_ADDRESS_ADDRESS = 'payment_billing_address_address';

    /**
     * Billing Address City
     *
     * @const: PAYMENT_BILLING_ADDRESS_CITY
     */
    public const PAYMENT_BILLING_ADDRESS_CITY = 'payment_billing_address_city';

    /**
     * Billing Address state
     *
     * @const: PAYMENT_BILLING_ADDRESS_STATE
     */
    public const PAYMENT_BILLING_ADDRESS_STATE = 'payment_billing_address_state';

    /**
     * Billing Address Country
     *
     * @const: PAYMENT_BILLING_ADDRESS_COUNTRY
     */
    public const PAYMENT_BILLING_ADDRESS_COUNTRY = 'payment_billing_address_country';

    /**
     * Billing Address Zip Code
     *
     * @const: PAYMENT_BILLING_ADDRESS_ZIPCODE
     */
    public const PAYMENT_BILLING_ADDRESS_ZIPCODE = 'payment_billing_address_zipcode';

    /**
     * Payment shipping Address
     *
     * @const: PAYMENT_SHIPPING_ADDRESS
     */
    public const PAYMENT_SHIPPING_ADDRESS = 'payment_shipping_address';

    /**
     * Payment Shipping Address Address
     *
     * @const: PAYMENT_SHIPPING_ADDRESS_ADDRESS
     */
    public const PAYMENT_SHIPPING_ADDRESS_ADDRESS = 'payment_shipping_address_address';

    /**
     * Shipping Address City
     *
     * @const: PAYMENT_SHIPPING_ADDRESS_CITY
     */
    public const PAYMENT_SHIPPING_ADDRESS_CITY = 'payment_shipping_address_city';

    public const PAYMENT_HISTORY_VIEW_URL = 'ebizcharge_ebizcharge/recurrings/transactionview';
    /**
     * Shipping Address state
     *
     * @const: PAYMENT_SHIPPING_ADDRESS_STATE
     */
    public const PAYMENT_SHIPPING_ADDRESS_STATE = 'payment_shipping_address_state';

    /**
     * Shipping Address Country
     *
     * @const: PAYMENT_SHIPPING_ADDRESS_COUNTRY
     */
    public const PAYMENT_SHIPPING_ADDRESS_COUNTRY = 'payment_shipping_address_country';

    /**
     * Shipping Address Zip Code
     *
     * @const: PAYMENT_SHIPPING_ADDRESS_ZIPCODE
     */
    public const PAYMENT_SHIPPING_ADDRESS_ZIPCODE = 'payment_shipping_address_zipcode';

    /**
     * Payment Shipping Address Phone
     *
     * @const: PAYMENT_SHIPPING_ADDRESS_PHONE
     */
    public const PAYMENT_SHIPPING_ADDRESS_PHONE = 'payment_shipping_address_phone';

    /**
     * Status Failed
     *
     * @cosnt: PAYMENT_STATUS_FAILED
     */
    public const PAYMENT_STATUS_FAILED = 'failed';

    /**
     *  Payment Status Settled
     *
     * @cosnt: PAYMENT_STATUS_SETTLED
     */
    public const PAYMENT_STATUS_SETTLED = 'settled';

    /**
     * Payment Status
     *
     * @const: PAYMENT_STATUS
     */
    public const PAYMENT_STATUS = 'payment_status';

    /**
     * Payment Entity Id
     *
     * @const: ENTITY_ID
     */
    public const ENTITY_ID = 'entity_id';

    /**
     * @var Config
     */
    protected Config $_configModel;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var Address
     */
    protected Address $_customerAddress;

    /**
     * @var Url
     */
    protected Url $_urlFactory;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected \Magento\Backend\Model\Url $_backendUrlManager;

    /**
     * RenderComplexData constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CustomerFactory $customerFactory
     * @param Address $customerAddress
     * @param Config $configModel
     * @param \Magento\Backend\Model\Url $backendUrlManager
     * @param Url $urlFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface           $context,
        UiComponentFactory         $uiComponentFactory,
        CustomerFactory            $customerFactory,
        Address                    $customerAddress,
        Config                     $configModel,
        \Magento\Backend\Model\Url $backendUrlManager,
        Url                        $urlFactory,
        array                      $components = [],
        array                      $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        /** @var _configModel */
        $this->_configModel = $configModel;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _customerAddress */
        $this->_customerAddress = $customerAddress;
        $this->_urlFactory = $urlFactory;
        $this->_backendUrlManager = $backendUrlManager;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            /** @var  $resultIndex */
            $resultIndex = $this->getData('name');
            /** @var  $mainColumnIndex */
            $mainColumnIndex = $this->getData('name');

            $counter = 0;
            /** @var  $item */
            foreach ($dataSource['data']['items'] as & $item) {
                $counter++;

                /** Item Result Index */
                if (isset($item[$resultIndex])) {
                    $item[$resultIndex] = $this->renderPaymentResponseHtml(
                        $resultIndex,
                        $item,
                        $item[$resultIndex],
                        $mainColumnIndex,
                        $counter
                    );
                } else {
                    $item[$resultIndex] = $this->renderPaymentResponseHtml(
                        $resultIndex,
                        $item,
                        $mainColumnIndex
                    );
                }
            }

        }
        return $dataSource;
    }

    /**
     * Render Payment Response Html
     *
     * @param mixed $resultIndex
     * @param array $item
     * @param array $resultItems
     * @param string $mainColumnIndx
     * @param int $counter
     * @return string
     */
    protected function renderPaymentResponseHtml(
        $resultIndex,
        $item = [],
        $resultItems = [],
        $mainColumnIndx = '',
        $counter = 0
    ) {
        $response = '';

        switch ($mainColumnIndx) {
            case self::STORE_ID:
                $response = $this->renderPaymentResponseDetailBlock($resultItems, $mainColumnIndx, $counter, $item);
                break;
            case self::PAYMENT_RESPONSE:
                $response = $this->renderPaymentResponseBlock($resultItems, $mainColumnIndx, $counter, $item);
                break;
            case self::PAYMENT_LINE_ITEMS:
                $response = $this->renderPaymentLineItemsBlock($resultItems, $mainColumnIndx, $counter, $item);
                break;
            case self::PAYMENT_CHECK_DATA:
                $response = $this->renderPaymentCheckDataBlock($resultItems, $mainColumnIndx, $counter, $item);
                break;
            case self::PAYMENT_BILLING_ADDRESS:
                $response = $this->renderPaymentBillingAddressBlock($resultItems, $mainColumnIndx);
                break;
            case self::PAYMENT_BILLING_ADDRESS_ADDRESS:
                $response = $this->renderPaymentBillingAddressAddressBlock($resultItems, $mainColumnIndx);
                break;
            case self::PAYMENT_BILLING_ADDRESS_CITY:
                $response = $this->renderPaymentBillingAddressCityBlock($resultItems, $mainColumnIndx);
                break;
            case self::PAYMENT_BILLING_ADDRESS_ZIPCODE:
                $response = $this->renderPaymentBillingAddressZipcodeBlock($resultItems, $mainColumnIndx);
                break;
            case self::PAYMENT_BILLING_ADDRESS_STATE:
                $response = $this->renderPaymentBillingAddressStateBlock($resultItems, $mainColumnIndx);
                break;
            case self::PAYMENT_BILLING_ADDRESS_COUNTRY:
                $response = $this->renderPaymentBillingAddressCountryBlock($resultItems, $mainColumnIndx);
                break;
            case self::PAYMENT_SHIPPING_ADDRESS_ADDRESS:
                $response = $this->renderPaymentShippingAddressAddressBlock($resultItems, $mainColumnIndx);
                break;
            case self::PAYMENT_SHIPPING_ADDRESS_CITY:
                $response = $this->renderPaymentShippingAddressCityBlock($resultItems, $mainColumnIndx);
                break;
            case self::PAYMENT_SHIPPING_ADDRESS_ZIPCODE:
                $response = $this->renderPaymentShippingAddressZipcodeBlock($resultItems, $mainColumnIndx);
                break;
            case self::PAYMENT_SHIPPING_ADDRESS_COUNTRY:
                $response = $this->renderPaymentShippingAddressCountryBlock($resultItems, $mainColumnIndx);
                break;
            case self::PAYMENT_SHIPPING_ADDRESS_PHONE:
                $response = $this->renderPaymentShippingAddressPhoneBlock($resultItems, $mainColumnIndx);
                break;
            case self::PAYMENT_SHIPPING_ADDRESS:
                $response = $this->renderPaymentShippingAddressBlock($resultItems, $mainColumnIndx);
                break;
            case self::PAYMENT_STATUS:
                $response = $this->renderPaymentStatusBlock($resultItems, $mainColumnIndx, $counter, $item);
                break;
            case self::PAYMENT_DETAIL:
                $response = $this->renderPaymentDetailBlock($resultItems, $mainColumnIndx, $counter, $item);
                break;

        }
        $response .= '<div id="popup-window-base"> </div>
                    <div id="popup-window" class="popup-window" >
                    <div class="popup-window-content" id="popup-window-content"></div>
                    <div class="close-btn" style="text-align: right;">
                    <button title="Close Button" onclick="closeDiv()">OK</button></div></div>
      <script>

                function toggleContents(id){

                    let divContents = jQuery("#"+id);
                    let popUpWindowBase = jQuery("#popup-window-base");
                    let popupWindow = jQuery("#popup-window");
                    let popUpWindowContent = jQuery("#popup-window-content");

                    popUpWindowContent.html((divContents.html()));
                    popUpWindowBase.show();
                    popupWindow.show();
                }
                function closeDiv(){
                   let popUpWindowBase = jQuery("#popup-window-base");
                   let popupWindow = jQuery("#popup-window");
                  popUpWindowBase.hide();
                  popupWindow.hide();
                }
            </script>';

        return $response;
    }

    /**
     * Render Payment Response Detail Block
     *
     * @param mixed $columnVal
     * @param mixed $mainColumnIndx
     * @param mixed $counter
     * @param mixed $item
     * @return string
     */
    protected function renderPaymentResponseDetailBlock($columnVal, $mainColumnIndx, $counter, $item)
    {
        /** @var $entityId */
        $entityId = $item[self::ENTITY_ID];
        $transactionDetailUrl = $this->getBackendUrl(self::PAYMENT_HISTORY_VIEW_URL, ['id' => $entityId]);
        $html = '<div class="main-grid-content-listing" >&#x2794; <a href="' . $transactionDetailUrl .
            '" class="grid-listing-text" >View Transaction Details</a> </div>';

        return $html;
    }

    /**
     * Get Backend URL
     *
     * @param string $urlController
     * @param array $params
     * @return string|null
     */
    public function getBackendUrl($urlController = '', $params = [])
    {
        return $this->_backendUrlManager->getUrl($urlController, $params);
    }

    /**
     * Render Payment Response
     *
     * @param mixed $resultItems
     * @param mixed $mainIndex
     * @param int $counter
     * @param array $items
     * @return string
     */
    protected function renderPaymentResponseBlock($resultItems, $mainIndex, $counter = 1, $items = [])
    {
        $html = '<div id="payment-response-' . $counter . '" class="popup-contents-div" >
<h3><b>Payment Response:</b></h3><hr/><ul  class="line-items-grid-listings">';
        $paymentResponseRows = (array)json_decode($resultItems);
        $html .= '<ul>';
        if (count($paymentResponseRows) > 0) {
            foreach ($paymentResponseRows as $key => $val) {
                $html .= '<li>' . $key . ' = ' . $val . '</li>';
            }
        }
        $html .= '</ul> </div>';
        $html .= '<div class="main-grid-content-listing" >&#x2794; <a href="#" class="grid-listing-text"
 onclick="toggleContents(\'payment-response-' . $counter . '\')"  >View Payment Response</a> </div>';

        return $html;
    }

    /**
     * Render Payment Line items
     *
     * @param mixed $resultItems
     * @param mixed $mainIndex
     * @param int $counter
     * @param array $items
     * @return string
     */
    protected function renderPaymentLineItemsBlock($resultItems, $mainIndex, $counter = 1, $items = [])
    {
        $html = '<div id="line-items-' . $counter . '" class="popup-contents-div" >
<h3><b>Line Items:</b></h3><hr/><ul  class="line-items-grid-listings">';
        $lineItems = json_decode($resultItems);

        /** @var $lineItems */
        $lineItems = (array)$lineItems;

        if (count($lineItems) > 0) {
            foreach ($lineItems as $key => $val) {
                $lineItem = (array)$val;
                foreach ($lineItem as $k => $v) {
                    $html .= '<li>' . $k . ' = ' . $v . '</li>';
                }
            }
        }
        $html .= '</ul></div>';
        $html .= '<div class="main-grid-content-listing" >&#x2794; <a href="#" class="grid-listing-text"
 onclick="toggleContents(\'line-items-' . $counter . '\')"  > View Line Items</a> </div>';

        return $html;
    }

    /**
     * Render Payment Check Data Block
     *
     * @param mixed $resultItems
     * @param mixed $mainIndex
     * @param int $counter
     * @param array $items
     * @return string
     */
    protected function renderPaymentCheckDataBlock($resultItems, $mainIndex, $counter = 1, $items = [])
    {
        $html = '<div id="payment-check-data-' . $counter . '" class="popup-contents-div" >
<h3><b>' . __('Payments Check Data:') . '</b></h3><hr/><ul  class="line-items-grid-listings">';
        $paymentResponseRows = (array)json_decode($resultItems);
        $html .= '<ul>';
        if (count($paymentResponseRows) > 0) {
            foreach ($paymentResponseRows as $key => $val) {
                $html .= '<li>' . $key . ' = ' . $val . '</li>';
            }
        }
        $html .= '</ul> </div>';
        $html .= '<div class="main-grid-content-listing">&#x2794; <a class="grid-listing-text"  href="#"
 onclick="toggleContents(\'payment-check-data-' . $counter . '\')"  >View Check Data</a> </div>';

        return $html;
    }

    /**
     * Render Payment Billing Address Block
     *
     * @param mixed $resultItems
     * @param mixed $mainIndex
     * @return string
     */
    protected function renderPaymentBillingAddressBlock($resultItems, $mainIndex)
    {
        $html = '';
        return $html;
    }

    /**
     * Render Payment Billing Address Address Block
     *
     * @param mixed $resultItems
     * @param mixed $mainColumnIndx
     * @return string
     */
    public function renderPaymentBillingAddressAddressBlock($resultItems, $mainColumnIndx)
    {
        /** @var $billingData */
        $billingData = $resultItems[self::PAYMENT_BILLING_ADDRESS];
        /** @var  $billingAddress */
        $billingAddress = json_decode($billingData);
        /** @var  $billingAddress */
        $billingAddress = (array)$billingAddress;
        /** @var $street */
        $street = isset($billingAddress['Street']) ? $billingAddress['Street'] : '';
        $street2 = isset($billingAddress['Street2']) ? $billingAddress['Street'] : '';
        $address = ($street !== '') ? $street : '*';

        if ($street2 !== '') {
            $address .= ' ' . ($street2 !== '') ? $street2 : '*';
        }

        return '<span><span>' . $address . '</span></span>';
    }

    /**
     * Render Payment Billing Address City Block
     *
     * @param mixed $resultItems
     * @param mixed $mainColumnIndx
     * @return string
     */
    public function renderPaymentBillingAddressCityBlock($resultItems, $mainColumnIndx)
    {
        $attribute = 'City';
        $mainColumnIndx = self::PAYMENT_BILLING_ADDRESS;
        return $this->renderPaymentCustomColumnData($attribute, $resultItems, $mainColumnIndx);
    }

    /**
     * Render Payment Custom Column Data
     *
     * @param mixed $attribute
     * @param mixed $resultItems
     * @param mixed $mainColumnIndx
     * @return string
     */
    public function renderPaymentCustomColumnData($attribute, $resultItems, $mainColumnIndx)
    {
        /** @var $billingData */
        $billingData = $resultItems[$mainColumnIndx];
        $billingData = json_decode($billingData);
        /** @var $billingData */
        $billingData = (array)$billingData;
        /** @var $field */
        $field = isset($billingData[$attribute]) ? $billingData[$attribute] : '';
        $field = ($field !== '') ? $field : '*';

        return '<span><span>' . $field . '</span></span>';
    }

    /**
     * Zip Code Block
     *
     * @param mixed $resultItems
     * @param mixed $mainColumnIndx
     * @return string
     */
    public function renderPaymentBillingAddressZipcodeBlock($resultItems, $mainColumnIndx)
    {
        $attribute = 'Zipcode';
        $mainColumnIndx = self::PAYMENT_BILLING_ADDRESS;
        return $this->renderPaymentCustomColumnData($attribute, $resultItems, $mainColumnIndx);
    }

    /**
     * Render Payment Billing Address State Block
     *
     * @param mixed $resultItems
     * @param mixed $mainIndex
     * @return string
     */
    public function renderPaymentBillingAddressStateBlock($resultItems, $mainIndex)
    {
        $attribute = 'State';
        $mainColumnIndx = self::PAYMENT_BILLING_ADDRESS;
        return $this->renderPaymentCustomColumnData($attribute, $resultItems, $mainColumnIndx);
    }

    /**
     * Render Payment Billing Address Country
     *
     * @param mixed $resultItems
     * @param mixed $mainIndex
     * @return string
     */
    public function renderPaymentBillingAddressCountryBlock($resultItems, $mainIndex)
    {
        $attribute = 'Country';
        $mainColumnIndx = self::PAYMENT_BILLING_ADDRESS;
        return $this->renderPaymentCustomColumnData($attribute, $resultItems, $mainColumnIndx);
    }

    /**
     * Render Payment Shipping Address Address Block
     *
     * @param mixed $resultItems
     * @param mixed $mainColumnIndx
     * @return string
     */
    public function renderPaymentShippingAddressAddressBlock($resultItems, $mainColumnIndx)
    {
        /** @var $billingData */
        $billingData = $resultItems[self::PAYMENT_SHIPPING_ADDRESS];
        /** @var  $billingAddress */
        $billingAddress = json_decode($billingData);
        /** @var  $billingAddress */
        $billingAddress = (array)$billingAddress;
        /** @var $street */
        $street = isset($billingAddress['Street']) ? $billingAddress['Street'] : '';
        $street2 = isset($billingAddress['Street2']) ? $billingAddress['Street'] : '';
        $address = ($street !== '') ? $street : '*';

        if ($street2 !== '') {
            $address .= ' ' . ($street2 !== '') ? $street2 : '*';
        }

        return '<span><span>' . $address . '</span></span>';
    }

    /**
     * Render Payment Shipping Address City Block
     *
     * @param mixed $resultItems
     * @param mixed $mainColumnIndx
     * @return string
     */
    public function renderPaymentShippingAddressCityBlock($resultItems, $mainColumnIndx)
    {
        $attribute = 'City';
        $mainColumnIndx = self::PAYMENT_SHIPPING_ADDRESS;
        return $this->renderPaymentCustomColumnData($attribute, $resultItems, $mainColumnIndx);
    }

    /**
     * Render Payment Shipping Address Zip Code Block
     *
     * @param mixed $resultItems
     * @param mixed $mainColumnIndx
     * @return string
     */
    public function renderPaymentShippingAddressZipcodeBlock($resultItems, $mainColumnIndx)
    {
        $attribute = 'Zipcode';
        $mainColumnIndx = self::PAYMENT_SHIPPING_ADDRESS;
        return $this->renderPaymentCustomColumnData($attribute, $resultItems, $mainColumnIndx);
    }

    /**
     * Render Payment Billing Address Country
     *
     * @param mixed $resultItems
     * @param mixed $mainIndex
     * @return string
     */
    public function renderPaymentShippingAddressCountryBlock($resultItems, $mainIndex)
    {
        $attribute = 'Country';
        $mainColumnIndx = self::PAYMENT_SHIPPING_ADDRESS;
        return $this->renderPaymentCustomColumnData($attribute, $resultItems, $mainColumnIndx);
    }

    /**
     * Render Payment Shipping Address Phone Block
     *
     * @param mixed $resultItems
     * @param mixed $mainIndex
     * @return string
     */
    public function renderPaymentShippingAddressPhoneBlock($resultItems, $mainIndex)
    {
        $attribute = 'Phone';
        $mainColumnIndx = self::PAYMENT_SHIPPING_ADDRESS;
        return $this->renderPaymentCustomColumnData($attribute, $resultItems, $mainColumnIndx);
    }

    /**
     * Render Payment Shipping Address Block
     *
     * @param mixed $resultItems
     * @param mixed $mainIndex
     * @return string
     */
    protected function renderPaymentShippingAddressBlock($resultItems, $mainIndex)
    {
        $html = '';
        return $html;
    }

    /**
     * Render Payment Status Block
     *
     * @param mixed $resultItems
     * @param mixed $mainIndex
     * @param int $counter
     * @param array $items
     * @return string
     */
    protected function renderPaymentStatusBlock($resultItems, $mainIndex, $counter = 1, $items = [])
    {
        /** @var $paymentStatus */
        $paymentStatus = $items['result_status'];
        $viewMore = false;

        if (strtolower($paymentStatus) == 'approved') {
            $paymentStatus = self::PAYMENT_STATUS_SETTLED;
            $paymentClass = 'settled-class';
        } else {
            $paymentClass = 'error-class';
            $paymentStatus = self::PAYMENT_STATUS_FAILED;
            $viewMore = __('&#x2934; ...');
        }

        $html = '<div id="payment-Status-' . $counter . '" class="popup-contents-div" >
<h3><b>';
        $html .= __('Payment Status Logs:');
        $html .= '</b></h3> <hr/><ul  class="line-items-grid-listings">';

        $html .= '<ul>';
        $html .= '<li>' . $resultItems . '</li>';
        $html .= '</ul> </div>';
        $html .= '<div class="main-grid-content-listing listing-center" >';
        $html .= '<span class="' . $paymentClass . '">' . strtoupper($paymentStatus) . '</span>';

        if ($viewMore) {
            $html .= '<a href="#" class="grid-listing-text payment-status-more"
             onclick="toggleContents(\'payment-Status-' . $counter . '\')"  >';
            $html .= $viewMore . '</a>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render Payment Detail Block
     *
     * @param mixed $resultItems
     * @param mixed $mainIndex
     * @param int $counter
     * @param array $items
     * @return string
     */
    protected function renderPaymentDetailBlock($resultItems, $mainIndex, $counter = 1, $items = [])
    {
        $html = '<div id="payment-response-detail-' . $counter .
            '" class="popup-contents-div"  ><h3><b>Payment Details:</b></h3><hr/><ul class="line-items-grid-listings">';
        $paymentResponseRows = (array)json_decode($resultItems);
        $html .= '<ul>';

        if (count($paymentResponseRows) > 0) {
            foreach ($paymentResponseRows as $key => $val) {
                $html .= '<li>' . $key . ' = ' . $val . '</li>';
            }
        }
        $html .= '</ul> </div>';
        $html .= '<div class="main-grid-content-listing" >&#x2794; <a href="#" class="grid-listing-text"
 onclick="toggleContents(\'payment-response-detail-' . $counter . '\')"  > View Payment Details</a> </div>';

        return $html;
    }

    /**
     * Render Payment Shipping Address State Block
     *
     * @param mixed $resultItems
     * @param mixed $mainIndex
     */
    public function renderPaymentShippingAddressStateBlock($resultItems, $mainIndex)
    {
        $attribute = 'State';
        $mainColumnIndx = self::PAYMENT_SHIPPING_ADDRESS;
        return $this->renderPaymentCustomColumnData($attribute, $resultItems, $mainColumnIndx);
    }
}
