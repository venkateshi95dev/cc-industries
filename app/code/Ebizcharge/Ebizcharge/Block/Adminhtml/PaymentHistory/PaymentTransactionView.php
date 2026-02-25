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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\PaymentHistory;

use Ebizcharge\Ebizcharge\Model\PaymentHistoryFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\ProductMetadataFactory as ProductMetadataFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Payment Transaction View Block class
 *
 * Class PaymentTransactionView
 */
class PaymentTransactionView extends Template
{
    /**
     * Payment Transaction Payment Response
     *
     * @const PAYMENT_TRANSACTION_PAYMENT_RESPONSE
     */
    public const PAYMENT_TRANSACTION_PAYMENT_RESPONSE = 'payment_response';

    /**
     *  Payment Transaction Payment Line Items
     *
     * @cosnt: PAYMENT_TRANSACTION_PAYMENT_LINE_ITEMS
     */
    public const PAYMENT_TRANSACTION_PAYMENT_LINE_ITEMS = 'payment_line_items';

    /**
     * Payment Transaction Payment Detail
     *
     * @const: PAYMENT_TRANSACTION_PAYMENT_DETAIL
     */
    public const PAYMENT_TRANSACTION_PAYMENT_DETAIL = 'payment_detail';

    /**
     * Payment Transaction Check Trace
     *
     * @const PAYMENT_TRANSACTION_PAYMENT_CHECK_TRACE
     */
    public const PAYMENT_TRANSACTION_PAYMENT_CHECK_TRACE = 'payment_check_trace';

    /**
     * Payment transaction Payment Check Data
     *
     * @const PAYMENT_TRANSACTION_PAYMENT_CHECK_DATA
     */
    public const PAYMENT_TRANSACTION_PAYMENT_CHECK_DATA = 'payment_check_data';

    /**
     * Payment Transaction Payment Billing Address
     *
     * @const PAYMENT_TRANSACTION_PAYMENT_BILLING_ADDRESS
     */
    public const PAYMENT_TRANSACTION_PAYMENT_BILLING_ADDRESS = 'payment_billing_address';

    /**
     * Payment Transaction Payment Shipping Address
     *
     * @const PAYMENT_TRANSACTION_PAYMENT_SHIPPING_ADDRESS
     */
    public const PAYMENT_TRANSACTION_PAYMENT_SHIPPING_ADDRESS = 'payment_shipping_address';

    /**
     * Payment Transaction Detail Card Data
     *
     * @const: PAYMENT_TRANSACTION_PAYMENT_DETAIL_CARD_DATA
     */
    public const PAYMENT_TRANSACTION_PAYMENT_DETAIL_CARD_DATA = 'payment_detail_card_data';

    /**
     * Payment Card Info
     *
     * @const: PAYMENT_TRANSACTION_PAYMENT_CARD_INFO
     */
    public const PAYMENT_TRANSACTION_PAYMENT_CARD_INFO = 'payment_card_info';

    /**
     * Payment Date Time
     *
     * @const: PAYMENT_TRANSACTION_PAYMENT_DATE_TIME
     */
    public const PAYMENT_TRANSACTION_PAYMENT_DATE_TIME = 'payment_datetime';

    /**
     * Payment AVS Result
     *
     * @const: PAYMENT_TRANSACTION_PAYMENT_AVS_RESULT
     */
    public const PAYMENT_TRANSACTION_PAYMENT_AVS_RESULT = 'avs_result';

    /**
     * Payment Card Code Result
     *
     * @const: PAYMENT_TRANSACTION_PAYMENT_CARD_CODE_RESULT
     */
    public const PAYMENT_TRANSACTION_PAYMENT_CARD_CODE_RESULT = 'card_code_result';

    /**
     *  Payment Result Card Info
     *
     * @const: PAYMENT_TRANSACTION_PAYMENT_RESULT_CARD_INFO
     */
    public const PAYMENT_TRANSACTION_PAYMENT_RESULT_CARD_INFO = 'result_card_info';

    /**
     *  Payment Status
     *
     * @const: PAYMENT_TRANSACTION_PAYMENT_STATUS
     */
    public const PAYMENT_TRANSACTION_PAYMENT_STATUS = 'payment_status';
    public const PAYMENT_TRANSACTION_PAYMENT_ENTITY_ID = 'entity_id';
    public const PAYMENT_TRANSACTION_PAYMENT_REF_NUMBER = 'payment_ref_number';
    public const PAYMENT_TRANSACTION_PAYMENT_CUSTOMER_ID = 'customer_id';
    public const PAYMENT_TRANSACTION_PAYMENT_CUSTOMER_EMAIL = 'customer_email';
    public const PAYMENT_TRANSACTION_PAYMENT_CUSTOMER_NAME = 'customer_name';
    public const PAYMENT_TRANSACTION_PAYMENT_SOURCE = 'payment_source';
    public const PAYMENT_TRANSACTION_PAYMENT_AMOUNT = 'payment_amount';
    public const PAYMENT_TRANSACTION_PAYMENT_STORE_ID = 'store_id';
    public const PAYMENT_TRANSACTION_PAYMENT_TRANSACTION_TYPE = 'payment_transaction_type';

    /**
     * Payment Json Objects array
     *
     * @var string[]
     */
    public array $_paymentJsonObjects;

    /**
     * Payment Labels to change array
     *
     * @var array|string[]
     */
    public array $_paymentLabelsToChange;

    /**
     * Payment Data to Show array
     *
     * @var array|string[]
     */
    public array $_paymentDataToShow;

    /**
     * @var PaymentHistoryFactory
     */
    protected PaymentHistoryFactory $_paymentHistoryFactory;

    /**
     * @var ProductMetadataFactory
     */
    protected ProductMetadataFactory $_productMetadata;

    /**
     * @param Context $context
     * @param ProductMetadataFactory $productMetadata
     * @param PaymentHistoryFactory $paymentHistoryFactory
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        Context $context,
        ProductMetadataFactory $productMetadata,
        PaymentHistoryFactory $paymentHistoryFactory,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        /** @var  _productMetadata */
        $this->_productMetadata = $productMetadata;

        /** @var  _paymentHistoryFactory */
        $this->_paymentHistoryFactory = $paymentHistoryFactory;

        /** @var  _paymentJsonObjects */
        $this->_paymentJsonObjects = [
            self::PAYMENT_TRANSACTION_PAYMENT_RESPONSE,
            self::PAYMENT_TRANSACTION_PAYMENT_DETAIL,
            self::PAYMENT_TRANSACTION_PAYMENT_BILLING_ADDRESS,
            self::PAYMENT_TRANSACTION_PAYMENT_CHECK_DATA,
            self::PAYMENT_TRANSACTION_PAYMENT_LINE_ITEMS,
            self::PAYMENT_TRANSACTION_PAYMENT_SHIPPING_ADDRESS,
            self::PAYMENT_TRANSACTION_PAYMENT_CHECK_TRACE,
            self::PAYMENT_TRANSACTION_PAYMENT_DETAIL_CARD_DATA
        ];

        /** @var _paymentLabelsToChange */
        $this->_paymentLabelsToChange = [
            self::PAYMENT_TRANSACTION_PAYMENT_CARD_INFO => 'Payment Method Info',
            self::PAYMENT_TRANSACTION_PAYMENT_DATE_TIME => 'Payment Date & Time',
            self::PAYMENT_TRANSACTION_PAYMENT_AVS_RESULT => 'AVS Result',
            self::PAYMENT_TRANSACTION_PAYMENT_CARD_CODE_RESULT => 'CVV2/CVC Result'
        ];

        /** @var  _paymentDataToShow */
        $this->_paymentDataToShow = [
            self::PAYMENT_TRANSACTION_PAYMENT_ENTITY_ID,
            self::PAYMENT_TRANSACTION_PAYMENT_REF_NUMBER,
            self::PAYMENT_TRANSACTION_PAYMENT_CUSTOMER_ID,
            self::PAYMENT_TRANSACTION_PAYMENT_CUSTOMER_EMAIL,
            self::PAYMENT_TRANSACTION_PAYMENT_CUSTOMER_NAME,
            self::PAYMENT_TRANSACTION_PAYMENT_SOURCE,
            self::PAYMENT_TRANSACTION_PAYMENT_AMOUNT,
            self::PAYMENT_TRANSACTION_PAYMENT_CARD_INFO,
            self::PAYMENT_TRANSACTION_PAYMENT_DATE_TIME,
            self::PAYMENT_TRANSACTION_PAYMENT_STORE_ID,
            self::PAYMENT_TRANSACTION_PAYMENT_STATUS,
            self::PAYMENT_TRANSACTION_PAYMENT_TRANSACTION_TYPE,
            self::PAYMENT_TRANSACTION_PAYMENT_AVS_RESULT,
            self::PAYMENT_TRANSACTION_PAYMENT_CARD_CODE_RESULT
        ];

        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    /**
     * Get Transaction Data
     *
     * @return PaymentHistory
     */
    public function getTransactionData()
    {
        /** @var  $transactionId */
        $transactionId = $this->getRequest()->getParam('id');
        return $this->_paymentHistoryFactory->create()->load($transactionId) ?? false;
    }

    /**
     * Prepare Payment Card data
     *
     * @param string $paymentData
     * @param array $paymentDataObj
     * @return string
     */
    public function preparePaymentCardData($paymentData = '', $paymentDataObj = [])
    {
        $html = $paymentData;
        $html .= ' ending in ';
        $paymentResults = explode('-', $paymentDataObj[self::PAYMENT_TRANSACTION_PAYMENT_RESULT_CARD_INFO]);
        $html .= $paymentResults[0];
        return $html;
    }

    /**
     * Prepare Payment Status
     *
     * @param mixed $paymentStatus
     * @return string|string[]
     */
    public function preparePaymentStatus($paymentStatus)
    {
        return str_replace('.', '', $paymentStatus);
    }
}
