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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\CustomerEdit\Tab\View;

use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\Payment;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Block\Widget\Grid\Extended as Extended;
use Magento\Backend\Helper\Data;
use Magento\Customer\Model\CustomerIdProvider as CustomerIdProvider;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory as DataObjectFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

/**
 * Customer payment methods listing
 *
 * Class PaymentMethods
 */
class PaymentMethods extends Extended
{
    /**
     * Card add path url
     *
     * @const CARD_ADD_PATH
     */
    public const CARD_ADD_PATH = 'ebizcharge_ebizcharge/cards/add/';

    /**
     * Bank account add path url
     *
     * @const BANK_ACCOUNT_ADD_PATH
     */
    public const BANK_ACCOUNT_ADD_PATH = 'ebizcharge_ebizcharge/ach/add/';

    /**
     * Get core Registry
     *
     * @var Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Ebiz SOAP API Model
     *
     * @var TranApi
     */
    protected $_soapApiModel;

    /**
     * @var DataObjectFactory
     */
    protected $_dataObjectFactory;

    /**
     * @var CustomerIdProvider
     */
    protected $_customerIdProvider;

    /**
     * @var CustomerRepository
     */
    protected $_customerRepository;

    /**
     * @var EbizchargeLogger
     */
    protected $_ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * PaymentMethods constructor.
     *
     * @param CustomerRepository $customerRepository
     * @param CustomerFactory $customerFactory
     * @param CustomerIdProvider $customerIdProvider
     * @param DataObjectFactory $dataObjectFactory
     * @param TranApi $soapApiModel
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $collectionFactory
     * @param Registry $coreRegistry
     * @param EbizchargeLogger $ebizchargeLogger
     * @param array $data
     */
    public function __construct(
        CustomerRepository $customerRepository,
        CustomerFactory $customerFactory,
        CustomerIdProvider $customerIdProvider,
        DataObjectFactory $dataObjectFactory,
        TranApi $soapApiModel,
        Context $context,
        Data $backendHelper,
        CollectionFactory $collectionFactory,
        Registry $coreRegistry,
        EbizchargeLogger $ebizchargeLogger,
        array $data = []
    ) {
        /** Parent Reconstruct the Method */
        parent::__construct($context, $backendHelper, $data);

        /** @var _coreRegistry */
        $this->_coreRegistry = $coreRegistry;
        /** @var _collectionFactory */
        $this->_collectionFactory = $collectionFactory;
        /** @var  tranApi */
        $this->_soapApiModel = $soapApiModel;
        /** @var  dataObjectFactory */
        $this->_dataObjectFactory = $dataObjectFactory;
        /** @var  customerIdProvider */
        $this->_customerIdProvider = $customerIdProvider;
        /** @var  customerRepository */
        $this->_customerRepository = $customerRepository;
        /** @var  ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
    }

    /**
     * Get Headers Visibility
     *
     * @return bool
     */
    public function getHeadersVisibility()
    {
        return $this->getCollection()->getSize() >= 0;
    }

    /**
     * Get Customer
     *
     * @return \Ebizcharge\Ebizcharge\Model\Customer
     */
    public function getCustomer()
    {
        return $this->_customerFactory->create()->load($this->getRequest()->getParam('id'));
    }

    /**
     * Get collection object
     *
     * @return Collection
     */
    public function getCollection()
    {
        $customerId = $this->getCustomer()->getId();
        /**
         * @var Collection $collection
         */
        $collection = $this->_collectionFactory->create();
        $paymentMethods = $this->_customerFactory->create()->getEbizCustomerPaymentMethods($customerId);
        $bankAccount = true;

        $this->getData('block_name') == 'customer_cards_tab' && $bankAccount = false;

        if ($paymentMethods != null) {
            return $this->getPaymentMethodsCollection($collection, $paymentMethods, $bankAccount);
        }

        return $collection;
    }

    /**
     * Get Ebiz Customer Id
     *
     * @return mixed
     */
    public function getEbizCustId()
    {
        $customerId = $this->getRequest()->getParam('id');
        $ebizCustomerId = $this->_customerFactory->create()->load($customerId)->getEcCustId();
        return $ebizCustomerId;
    }

    /**
     * Get payment methods collection
     *
     * @param Collection $collection
     * @param mixed $paymentCards
     * @param bool $bankAccount
     * @return Collection
     * @throws Exception
     */
    public function getPaymentMethodsCollection(Collection $collection, $paymentCards, bool $bankAccount)
    {
        foreach ($paymentCards as $paymentCard) {
            if (!$bankAccount && $paymentCard->MethodType ===
                PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_CREDIT_CARD_CODE) {
                $collection->addItem($this->getCardDataObject($paymentCard));
            } elseif ($bankAccount && $paymentCard->MethodType ===
                PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_BANK_ACCOUNT_CODE) {
                $collection->addItem($this->getBankAccountDataObject($paymentCard));
            }
        }
        return $collection;
    }

    /**
     * Get Card Data Object
     *
     * @param mixed $paymentCard
     * @return DataObject
     */
    public function getCardDataObject($paymentCard)
    {
        $paymentCardData = (array)$paymentCard;
        $methodName = isset($paymentCardData["MethodName"]) ? $paymentCardData["MethodName"] : "";

        if (isset($paymentCardData["MethodName"])) {
            $methodNameData = (array)json_decode($methodName);

            if (isset($methodNameData["a"])) {
                $methodName = $methodNameData["a"];
            }
            if (isset($methodNameData["a"]) && isset($methodNameData["b"])) {
                $methodName = $methodNameData["a"]."-".$methodNameData["b"];
            }
        }

        return $this->_dataObjectFactory->create()->setData(
            [
                'cardHolder' => $methodName,
                'cardNumber' => $paymentCard->CardNumber,
                'cardType' => $paymentCard->CardType,
                'expirationDate' => $paymentCard->CardExpiration,
                'methodId' => $paymentCard->MethodID,
                'methodName' => $paymentCard->MethodName,
                'isDefault' => !$paymentCard->SecondarySort ? 'Yes' : 'No',
                'ebizCustId' => $this->getEbizCustId(),
                'customerId' => $this->_customerIdProvider->getCustomerId(),
            ]
        );
    }

    /**
     * Get Bank Account Data Object
     *
     * @param mixed $paymentCard
     * @return DataObject
     */
    public function getBankAccountDataObject($paymentCard)
    {
        return $this->_dataObjectFactory->create()->setData(
            [
                'accountHolder' => $paymentCard->AccountHolderName,
                'accountType' => ucfirst($paymentCard->AccountType) . ' ' . $paymentCard->Account,
                'methodId' => $paymentCard->MethodID,
                'methodName' => $paymentCard->MethodName,
                'isDefault' => !$paymentCard->SecondarySort ? 'Yes' : 'No',
                'ebizCustId' => $this->getEbizCustId(),
                'customerId' => $this->_customerIdProvider->getCustomerId(),
            ]
        );
    }

    /**
     * Get Row Url
     *
     * @param mixed $item
     * @return string
     */
    public function getRowUrl($item)
    {
        return '';
    }

    /**
     * Get Customer Payment Methods
     *
     * @return array|null
     */
    public function getCustomerPaymentMethods()
    {
        return $this->_soapApiModel->getCustomerPaymentMethods($this->getEbizCustId());
    }

    /**
     * Generate export button
     *
     * @return string
     * @throws LocalizedException
     */
    public function getExportButtonHtml()
    {
        // phpcs:disable
        $style = '
                    <style> #' . $this->getData('block_name') . '_export { display: none;} .admin__control-support-text { display: none;}  </style>';
        // phpcs:enable
        $html = $this->getLayout()->createBlock(Button::class)->setData(
            [
                'label' => __($this->getAddButtonTitle()),
                'onclick' => $this->getJsObjectName() . '.doExport()',
                'class' => 'task',
            ]
        )->toHtml();

        return $html . ' ' . $style;
    }

    /**
     * Parent construct Method
     *
     * @return void
     * @throws FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId($this->getData('block_name'));
        $this->setSortable(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    /**
     * Prepare Collection Method
     *
     * @return PaymentMethods
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->getCollection());
        return parent::_prepareCollection();
    }

    /**
     * Prepare Columns
     *
     * @return PaymentMethods
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        foreach ($this->getGridColumns() as $columnId => $columnData) {
            $this->addColumn($columnId, $columnData);
        }

        $this->addExportType($this->getAddButtonUrl() . $this->_customerIdProvider->getCustomerId() .
            '/', $this->getAddButtonTitle());
        return parent::_prepareColumns();
    }

    /**
     * Get Grid Columns
     *
     * @return array[]
     */
    public function getGridColumns()
    {
        if ($this->getData('block_name') == 'customer_cards_tab') {
            return $this->getCustomerCardColumns();
        }
        return $this->getBankAccountColumns();
    }

    /**
     * Get customer cards grid columns
     *
     * @return array[]
     */
    public function getCustomerCardColumns(): array
    {
        return [
            'cardHolder' => [
                'index' => 'cardHolder',
                'header' => __('Credit Card')
            ],
            'cardType' => [
                'header' => __('Card Type & Number'),
                'index' => 'cardType',
                'renderer' => CardType::class,
                'class' => 'photo',
            ],
            'expirationDate' => [
                'header' => __('Expiration Date'),
                'index' => 'expirationDate',
            ],
            'isDefault' => [
                'header' => __('Default'),
                'index' => 'isDefault',
            ],
            'actions' => [
                'header' => __('Actions'),
                'type' => 'select',
                'renderer' => ActionColumn::class,
                'resizeEnabled' => true,
                'resizeDefaultWidth' => 10,
                'is_system' => true,
                'filter' => false,
                'sortable' => false,
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select',
            ]
        ];
    }

    /**
     * Get customer bank account grid columns
     *
     * @return array[]
     */
    public function getBankAccountColumns(): array
    {
        return [
            'accountHolder' => [
                'header' => __('Account Holder'),
                'index' => 'accountHolder',
            ],
            'accountType' => [
                'header' => __('Account Type & Number'),
                'index' => 'accountType',
                'renderer' => CardType::class,
                'class' => 'photo',
            ],
            'isDefault' => [
                'header' => __('Default'),
                'index' => 'isDefault',
            ],
            'actions' => [
                'header' => __('Actions'),
                'type' => 'select',
                'renderer' => ActionColumn::class,
                'resizeEnabled' => true,
                'resizeDefaultWidth' => 10,
                'is_system' => true,
                'filter' => false,
                'sortable' => false,
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select',
            ]
        ];
    }

    /**
     * Get Customer store Id
     *
     * @return int|null
     */
    public function getCustomerStoreId()
    {
        try {
            return $this->_customerRepository->getById($this->_customerIdProvider->getCustomerId())->getStoreId();
        } catch (Exception $e) {
            $this->_ebizchargeLogger->addError(__("Exception : " . $e->getMessage()));
            return null;
        }
    }
}
