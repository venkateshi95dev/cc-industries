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

namespace Ebizcharge\Ebizcharge\Block\Customer\Account;

use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Config as EbizConfig;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\ProductFactory;
use Ebizcharge\Ebizcharge\Model\Recurring;
use Ebizcharge\Ebizcharge\Model\RecurringFactory;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\Collection;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\CollectionFactory as RecurringCollectionFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product\Type;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Block\Address\Renderer\RendererInterface;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Theme\Block\Html\Pager;

/**
 * Recurrings Block class
 *
 * Class Recurrings
 */
class Recurrings extends Template
{
    /**
     * @var TranApi
     */
    protected TranApi $tranApi;

    /**
     * @var Config
     */
    protected Config $paymentConfig;

    /**
     * @var RecurringCollectionFactory
     */
    protected RecurringCollectionFactory $recurringCollectionFactory;

    /**
     * @var EbizConfig
     */
    protected EbizConfig $myConfig;

    /**
     * @var SessionFactory
     */
    protected SessionFactory $customerSession;

    /**
     * @var RecurringRepositoryInterface
     */
    protected RecurringRepositoryInterface $recurringRepository;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $messageManager;

    /**
     * @var Http
     */
    protected Http $response;

    /**
     * @var RedirectInterface
     */
    protected RedirectInterface $redirect;

    /**
     * @var AddressRepositoryInterface
     */
    protected AddressRepositoryInterface $addressRepository;

    /**
     * @var AddressConfig
     */
    protected AddressConfig $addressConfig;

    /**
     * @var Mapper
     */
    protected Mapper $addressMapper;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var CustomerRegistry
     */
    protected CustomerRegistry $customerRegistry;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $customerFactory;

    /**
     * @var ImageHelper
     */
    protected ImageHelper $_imageHelper;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $_productFactory;

    /**
     * @var RedirectFactory
     */
    protected RedirectFactory $_redirectFactory;

    /**
     * @var Escaper
     */
    protected $_escaper;

    /**
     * @var RecurringFactory
     */
    protected $_recurringFactory;

    /**
     * @var SortOrderBuilder
     */
    protected $_sortOrderBuilder;

    /**
     * @var EbizConfig
     */
    protected $_configModel;

    /**
     * Recurrings constructor.
     *
     * @param Config $paymentConfig
     * @param Context $context
     * @param EbizConfig $config
     * @param Http $response
     * @param ManagerInterface $messageManager
     * @param RecurringCollectionFactory $recurringCollectionFactory
     * @param RedirectFactory $redirectFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RecurringRepositoryInterface $recurringRepository
     * @param RecurringFactory $recurringFactory
     * @param RedirectInterface $redirect
     * @param SessionFactory $customerSession
     * @param ImageHelper $imageHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressConfig $addressConfig
     * @param Mapper $addressMapper
     * @param ProductFactory $productFactory
     * @param CustomerFactory $customerFactory
     * @param Escaper $escaper
     * @param TranApi $tranApi
     * @param EbizConfig $configModel
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerRegistry $customerRegistry
     * @param SortOrderBuilder $sortOrderBuilder
     * @param EbizchargeLogger $ebizchargeLogger
     * @param array $data
     */
    public function __construct(
        Config                       $paymentConfig,
        Context                      $context,
        EbizConfig                   $config,
        Http                         $response,
        ManagerInterface             $messageManager,
        RecurringCollectionFactory   $recurringCollectionFactory,
        RedirectFactory              $redirectFactory,
        SearchCriteriaBuilder        $searchCriteriaBuilder,
        RecurringRepositoryInterface $recurringRepository,
        RecurringFactory             $recurringFactory,
        RedirectInterface            $redirect,
        SessionFactory               $customerSession,
        ImageHelper                  $imageHelper,
        OrderRepositoryInterface     $orderRepository,
        AddressRepositoryInterface   $addressRepository,
        AddressConfig                $addressConfig,
        Mapper                       $addressMapper,
        ProductFactory               $productFactory,
        CustomerFactory              $customerFactory,
        Escaper                      $escaper,
        TranApi                      $tranApi,
        EbizConfig                   $configModel,
        ProductRepositoryInterface   $productRepository,
        CustomerRegistry             $customerRegistry,
        SortOrderBuilder             $sortOrderBuilder,
        EbizchargeLogger             $ebizchargeLogger,
        array                        $data = []
    )
    {
        parent::__construct($context, $data);

        /** @var  recurringCollectionFactory */
        $this->recurringCollectionFactory = $recurringCollectionFactory;
        /** @var  customerSession */
        $this->customerSession = $customerSession;
        /** @var  tranApi */
        $this->tranApi = $tranApi;
        /** @var  myConfig */
        $this->myConfig = $config;
        /** @var  paymentConfig */
        $this->paymentConfig = $paymentConfig;
        /** @var  recurringRepository */
        $this->recurringRepository = $recurringRepository;
        /** @var  messageManager */
        $this->messageManager = $messageManager;
        /** @var  response */
        $this->response = $response;
        /** @var  redirect */
        $this->redirect = $redirect;

        /** Verify Edit Action */
        $this->verifyEditAction();

        /** @var  orderRepository */
        $this->orderRepository = $orderRepository;
        /** @var addressRepository */
        $this->addressRepository = $addressRepository;
        /** @var addressConfig */
        $this->addressConfig = $addressConfig;
        /** @var addressMapper */
        $this->addressMapper = $addressMapper;
        /** @var productRepository */
        $this->productRepository = $productRepository;
        /** @var customerRegistry */
        $this->customerRegistry = $customerRegistry;
        /** @var searchCriteriaBuilder */
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var customerFactory */
        $this->customerFactory = $customerFactory;
        /** @var _imageHelper */
        $this->_imageHelper = $imageHelper;
        /** @var _productFactory */
        $this->_productFactory = $productFactory;
        /** @var  _redirectFactory */
        $this->_redirectFactory = $redirectFactory;
        /** @var  _recurringFactory */
        $this->_recurringFactory = $recurringFactory;
        /** @var _escaper */
        $this->_escaper = $escaper;
        /** @var  _sortOrderBuilder */
        $this->_sortOrderBuilder = $sortOrderBuilder;
        /** @var  _configModel */
        $this->_configModel = $configModel;
    }

    /**
     * Verify Edit Action
     *
     * @return void
     */
    private function verifyEditAction(): void
    {
        if ($this->getRequest()->getFullActionName() == 'ebizcharge_recurrings_edit') {
            $mid = $this->getRequest()->getParam('mid');

            if ($mid) {
                return;
            }
            //   $this->messageManager->addErrorMessage(__('Unable to update recurrings payment.'));
            /**  */
            //   $this->redirect->redirect($this->response, '*/*/index');
        }
    }

    /**
     * Get Subscription Start Date
     *
     * @param null|mixed $recurring
     * @return string|null
     */
    public function getSubscriptionStartDate($recurring = null)
    {
        $startDate = null;
        if (isset($recurring['eb_rec_start_date'])) {
            $startDate = $recurring['eb_rec_start_date'];
            $startDate = $this->tranApi->formateDateTime($startDate, 'Y-m-d');
        }
        return $startDate;
    }

    /**
     * Formate Date Time
     *
     * @param null|mixed $dateTime
     * @param string $format
     * @return string
     */
    public function formateDateTime($dateTime = null, $format = 'Y-m-d H:i:s')
    {
        return $this->_recurringFactory->create()->formateDateTime($dateTime, $format);
    }

    /**
     * @param $orderedItems
     * @return array
     */
    public function getRecurringItems($orderedItems = [])
    {
        $recurredItems = [];
        if (count($orderedItems) > 0) {
            foreach ($orderedItems as $orderedItem) {
                $buyRequest = $orderedItem->getBuyRequest();
                $recurring = (array)$buyRequest->getRecurring();
                if (isset($recurring["rec_frequency"]) && !empty($recurring["rec_frequency"])) {
                    $recurredItems[] = $orderedItem;
                }
            }
        }

        return $recurredItems;
    }

    /**
     * Get Image Place Holder
     *
     * @param string $placeHolderName
     * @return string
     */
    public function getImagePlaceHoder($placeHolderName = 'image')
    {
        return $this->_imageHelper->getDefaultPlaceholderUrl($placeHolderName);
    }

    /**
     * Format Currency
     *
     * @param int $priceValue
     * @return string
     */
    public function formateCurrency($priceValue = 0)
    {
        return $this->_productFactory->create()->getCurrencyWithFormat($priceValue);
    }

    /**
     * Get Product Link
     *
     * @param int $productId
     * @return string
     */
    public function getProductLink($productId = 0)
    {
        $product = $this->_productFactory->create()->load($productId);
        return $product->getProductUrl();
    }

    /**
     * Get Product Final Price
     *
     * @param int $productId
     * @return float
     */
    public function getProductFinalPrice($productId = 0)
    {
        /**
         * Fetching Product Price
         */
        $product = $this->_productFactory->create()->load($productId);

        $productFinalPrice = $product->getFinalPrice();
        $productType = $product->getTypeId();

        if ($productType == Type::TYPE_SIMPLE || $productType == 'configurable') {
            $productFinalPrice = $product->getPriceInfo()
                ->getPrice('final_price')
                ->getAmount()
                ->getValue();
        } elseif ($productType == Type::TYPE_BUNDLE) {
            $productFinalPrice = $product->getPriceInfo()
                ->getPrice('final_price')
                ->getAmount()
                ->getValue();
        }
        return $productFinalPrice;
    }

    /**
     * RenderRecurringStatus
     *
     * @param Recurring|null $recurring
     * @return array
     */
    public function renderRecurringStatus(Recurring $recurring = null)
    {
        /** @var  $recStatus */
        $recStatus = $this->isRecurringFieldsDisabled($recurring->getId());

        /** @var $recurringStatus */
        $recurringStatusResp = [
            'label' => Recurring::RECURRING_STATUS_LABEL_OFF,
            'class' => 'dot_red',
            'class_label' => "off-row",
            'row_class' => 'red'
        ];
        /** if current Recurring is Active */
        if ($recStatus === "") {
            $recurringStatusResp = [
                'label' => Recurring::RECURRING_STATUS_LABEL_ACTIVE,
                'class' => 'dot_green',
                'class_label' => "on-row",
                'row_class' => 'green'
            ];
        }
        /** if current Recurring is Cancelled */
        if ($recStatus === Recurring::RECURRING_STATUS_LABEL_CANCELED) {
            $recurringStatusResp = [
                'label' => __("Cancelled"),
                'class' => 'dot_red_cancelled',
                'class_label' => "cancelled-row",
                'row_class' => 'red_cancelled'
            ];
        }

        return $recurringStatusResp;
    }

    /**
     * Is Recurring Fields Disabled
     *
     * @param null|mixed $recurringId
     * @return string
     */
    public function isRecurringFieldsDisabled($recurringId = null)
    {
        $disabled = "";

        if (!$recurringId) {
            return $disabled;
        }

        $recurring = $this->_recurringFactory->create()->load($recurringId);
        $recurringStatus = $recurring->getRecStatus();

        /** @var  $recEndDate */
        $recEndDate = $recurring->getEbRecEndDate();

        /** @var  $expire */
        $expireDate = date("Y-m-d", strtotime($recEndDate));
        $isExpired = false;

        if ($expireDate < date("Y-m-d")) {
            $isExpired = true;
        }

        if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_CANCELED) {
            $disabled = "disabled";
        }
        if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_EXPIRED || $isExpired === true) {
            $disabled = "disabled";
        }
        if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_SUSPENDED) {
            $disabled = "disabled";
        }

        if (!$this->getIsCreditCardPaymentsAllowed() && !$this->getIsBankAccountPaymentsAllowed()) {
            $disabled = "disabled";
        }

        return $disabled;
    }

    /**
     * Get Is Credit Card Payments Allowed
     *
     * @return bool
     */
    public function getIsCreditCardPaymentsAllowed()
    {
        return $this->_configModel->isCreditCardEnabled();
    }

    /**
     * Get Is Bank Account Payments Allowed
     *
     * @return bool
     */
    public function getIsBankAccountPaymentsAllowed()
    {
        return $this->_configModel->isAchActive();
    }

    /**
     * Load Recurring
     *
     * @param int $recurringId
     * @return Recurring
     */
    public function loadRecurring($recurringId = 0)
    {
        return $this->_recurringFactory->create()->load($recurringId);
    }

    /**
     * Prepare Subscription Buttons Status
     *
     * @param int $recurringId
     * @return bool[]
     */
    public function prepareSubscriptionButtonsStatus($recurringId = 0)
    {
        $buttonsStatusResponse = [
            'save' => true,
            'suspend' => true,
            'unsubscribe' => true
        ];

        $recurring = $this->_recurringFactory->create()->load($recurringId);
        $recurringStatus = $recurring->getRecStatus();
        /** @var  $recEndDate */
        $recEndDate = $recurring->getEbRecEndDate();

        /** @var  $expire */
        $expireDate = date("Y-m-d H:i:s", strtotime($recEndDate));
        $isExpired = false;

        if ($expireDate < date("Y-m-d H:i:s")) {
            $isExpired = true;
        }

        if ($isExpired === false) {
            if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_ACTIVE) {
                $buttonsStatusResponse = [
                    'save' => true,
                    'suspend' => true,
                    'unsubscribe' => true
                ];
            }

            if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_EXPIRED) {
                $buttonsStatusResponse = [
                    'save' => false,
                    'suspend' => true,
                    'unsubscribe' => true
                ];
            }
            if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_SUSPENDED) {
                $buttonsStatusResponse = [
                    'save' => false,
                    'suspend' => true,
                    'unsubscribe' => true
                ];
            }

            if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_CANCELED) {
                $buttonsStatusResponse = [
                    'save' => false,
                    'suspend' => false,
                    'unsubscribe' => false
                ];
            }

        } else {
            if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_ACTIVE) {
                $buttonsStatusResponse = [
                    'save' => false,
                    'suspend' => true,
                    'unsubscribe' => true
                ];
            }
            if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_EXPIRED) {
                $buttonsStatusResponse = [
                    'save' => false,
                    'suspend' => true,
                    'unsubscribe' => true
                ];
            }

            if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_SUSPENDED) {
                $buttonsStatusResponse = [
                    'save' => false,
                    'suspend' => true,
                    'unsubscribe' => true
                ];
            }
            if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_CANCELED) {
                $buttonsStatusResponse = [
                    'save' => false,
                    'suspend' => false,
                    'unsubscribe' => false
                ];
            }
        }

        return $buttonsStatusResponse;
    }

    /**
     * Get Delete URL
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->escapeUrl($this->getUrl("ebizcharge/recurrings/deleteaction"));
    }

    /**
     * Get Recurring History Url
     *
     * @return string
     */
    public function getRecurringHistoryUrl()
    {
        return $this->escapeUrl($this->getUrl("ebizcharge/recurrings/history/"));
    }

    /**
     * Get Recurring Edit Url
     *
     * @param array $params
     * @return string
     */
    public function getRecurringEditUrl($params = [])
    {
        return $this->escapeUrl($this->getUrl(
            "ebizcharge/recurrings/edit",
            $params
        ));
    }

    /**
     * Get Esacper
     *
     * @return Escaper
     */
    public function getEscaper()
    {
        return $this->_escaper;
    }

    /**
     * Get Recurrings By Status
     *
     * @param bool $recStatus
     * @return array
     * @throws Exception
     */
    public function getRecurringsByStatus($recStatus = true)
    {
        /** @var $recurringResp */
        $recurringResp = [];

        /** @var $recurrings */
        $recurrings = $this->getRecurrings();
        $recurringItems = $recurrings->getItems();

        if (count($recurringItems) > 0) {
            foreach ($recurringItems as $recurring) {
                /** @var $currentStatus */
                $currentStatus = $this->getRecurringCurrentStatus($recurring);
                if ($currentStatus === $recStatus) {
                    $recurringResp[] = $recurring;
                }
            }
        }
        return $recurringResp;
    }

    /**
     * Get Recurring Current Status
     *
     * @param Recurring|null $recurring
     * @return bool
     * @throws Exception
     */
    public function getRecurringCurrentStatus(Recurring $recurring = null)
    {
        /** @var $recurringStatus */
        $recurringStatus = false;
        /** @var $currentStatus */
        $currentStatus = $recurring->getRecStatus();
        /** @var $startDate */
        $startDate = $recurring->getEbRecStartDate();
        /** @var $endDate */
        $endDate = $recurring->getEbRecEndDate();
        /** @var $currentDate */
        $currentDateTime = $this->getCurrentDateTime();

        /** if current Recurring is Active */
        if ($currentStatus == Recurring::EBIZCHARGE_RECURRING_STATUS_ACTIVE
            && $currentDateTime <= $endDate) {
            $recurringStatus = true;
        }
        if ($currentStatus == Recurring::EBIZCHARGE_RECURRING_STATUS_CANCELED) {
            $recurringStatus = Recurring::RECURRING_STATUS_LABEL_CANCELED;
        }

        return $recurringStatus;
    }

    /**
     * Get Current Date Time
     *
     * @return string
     */
    public function getCurrentDateTime()
    {
        return $this->_recurringFactory->create()->getCurrentDateTime();
    }

    /**
     * Get Pager child block output
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Current Recurring
     *
     * @param null|mixed $subscriptionId
     * @return Recurring|Http|HttpInterface|null
     */
    public function getCustomerSubscriptionById($subscriptionId = null)
    {
        $recurringId = $this->_request->getParam("rec_id");
        $subscriptionId = $subscriptionId == null ? $recurringId ?? null : null;

        if (!$subscriptionId) {
            $this->messageManager->addWarningMessage(__(
                "Problem found during fetching the subscription."
            ));
            return $this->response->setRedirect($this->getUrl('ebizcharge/recurrings/index'));
        }
        /** @var  $currentRecurring */
        $currentRecurring = $this->_recurringFactory->create()->load($subscriptionId);

        if ($currentRecurring) {
            return $currentRecurring->getData();
        }
        return null;
    }

    /**
     * Render Subscription Status
     *
     * @param null|mixed $recurringId
     * @return string
     */
    public function renderSubscriptionStatus($recurringId = null): string
    {
        $renderHtml = '';
        /** @var  $recurring */
        $recurring = $this->_recurringFactory->create()->load($recurringId);

        if (!$recurring) {
            return $renderHtml;
        }
        /** @var  $recurringStatus */
        $recurringStatus = $recurring->getRecStatus();
        $recurringId = $recurring->getEntityId();

        /** @var  $renderRecurringStatuses */
        $renderRecurringStatuses = [
            Recurring::EBIZCHARGE_RECURRING_STATUS_ACTIVE => $this->escapeHtml(__(
                'This subscription is active.'
            )),
            Recurring::EBIZCHARGE_RECURRING_STATUS_SUSPENDED => $this->escapeHtml(__(
                'This subscription has been suspended.'
            )),
            Recurring::EBIZCHARGE_RECURRING_STATUS_CANCELED => $this->escapeHtml(__(
                'This subscription has been canceled.'
            )),
            Recurring::EBIZCHARGE_RECURRING_STATUS_EXPIRED => $this->escapeHtml(__(
                'This subscription has been expired.'
            )),
        ];
        if ($this->isRecurringFieldsDisabled($recurringId) !== "") {
            if ($this->isRecurringExpired($recurringId)) {
                $recurringStatus = Recurring::EBIZCHARGE_RECURRING_STATUS_EXPIRED;
            }
            $renderHtml .= ' <div class="subscription-main-div">';
            $renderHtml .= '<div class="message-warning warning message"><p>' .
                $renderRecurringStatuses[$recurringStatus] . '</p></div>';
            $renderHtml .= '</div>';
        }

        return $renderHtml;
    }

    /**
     * Is Recurring Expired
     *
     * @param null|mixed $recurringId
     * @return bool
     */
    public function isRecurringExpired($recurringId = null)
    {
        $isUnvalid = false;

        /** @var  $recurring */
        $recurringObj = $this->_recurringFactory->create()->load($recurringId);
        if (!$recurringObj) {
            return $isUnvalid;
        }
        $recurring = $recurringObj->getData();

        $recStatus = $recurring['rec_status'];
        $endDate = $this->getSubscriptionEndDate($recurring);
        $currentDate = $this->tranApi->getCurrentDateTime('Y-m-d');

        /**
         * in case of expired
         * or status is not cancelled
         */
        if ($currentDate > $endDate) {
            $isUnvalid = true;
        }
        /**
         * in case of suspended recurring
         */
        if ($recStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_EXPIRED) {
            $isUnvalid = true;
        }
        /**
         * in case of suspended recurring
         */
        if ($recStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_SUSPENDED) {
            $isUnvalid = true;
        }

        /**
         * in case of cacelled recurring
         */
        if ($recStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_CANCELED) {
            $isUnvalid = true;
        }

        return $isUnvalid;
    }

    /**
     * Get Subscription End Date
     *
     * @param null|mixed $recurring
     * @return string|null
     */
    public function getSubscriptionEndDate($recurring = null)
    {
        $endDate = null;
        if (isset($recurring['eb_rec_end_date'])) {
            $endDate = $recurring['eb_rec_end_date'];
            $endDate = $this->tranApi->formateDateTime($endDate, 'Y-m-d');
        }
        return $endDate;
    }

    /**
     * Get Ebizcharge Cutomer Internal Id
     *
     * @return mixed|string
     */
    public function getEbzcCustInternalId()
    {
        $customer = $this->getCustomerDetail($this->getMageCustId());

        return $customer['ec_cust_internalid'] ?? '';
    }

    /**
     * Get customer details with customer id
     *
     * @param mixed $customerID
     * @return Customer|null
     */
    public function getCustomerDetail($customerID)
    {
        if (!empty($customerID)) {
            try {
                return $this->customerRegistry->retrieve($customerID);
            } catch (Exception $e) {
                $this->ebizchargeLogger->addError(__("Exception occurred during reteriving " . $e->getMessage()));
                return null;
            }
        }
        return null;
    }

    /**
     * Get Mage Customer Id
     *
     * @return int|null
     */
    public function getMageCustId()
    {
        return $this->customerSession->create()->getCustomerId();
    }

    /**
     * Get Customer Id
     *
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->getCustomer()->getId();
    }

    /**
     * Get Customer
     *
     * @return \Ebizcharge\Ebizcharge\Model\Customer
     */
    public function getCustomer()
    {
        $customerId = $this->customerSession->create()->getCustomerId();
        return $this->customerFactory->create()->load($customerId);
    }

    /**
     * Get MID
     *
     * @return mixed
     */
    public function getMID()
    {
        return $this->getRequest()->getParam('mid');
    }

    /**
     * Render back Authenticate
     *
     * @return Redirect|void
     */
    public function renderBackAuthenticate()
    {
        $renderAuthPage = $this->renderAuthPage();
        if (!$renderAuthPage) {
            return $this->_redirectFactory->create()->setPath('ebizcharge/recurrings/index/', []);
        }
    }

    /**
     * Render Auth Page
     *
     * @return bool
     */
    public function renderAuthPage()
    {
        $isAuthenticated = true;
        $paymentMethods = $this->getCustomerPaymentMethods();

        if (count($paymentMethods) == 0) {
            $isAuthenticated = false;
        }
        return $isAuthenticated;
    }

    /**
     * Get Customer Payment Methods
     *
     * @param null|mixed $customerId
     * @return array
     */
    public function getCustomerPaymentMethods($customerId = null)
    {
        /** @var  $customerPaymentMethods */
        $customerId = $customerId == null ? $this->getCustomerId() : $customerId;

        $customerPaymentMethods = [];

        /** @var  $ebizCustomerPaymentMethods */
        $ebizCustomerPaymentMethods = $this->customerFactory->create()->getEbizCustomerPaymentMethods($customerId);

        if (isset($ebizCustomerPaymentMethods) && count($ebizCustomerPaymentMethods) > 0) {
            $customerPaymentMethods = $ebizCustomerPaymentMethods;
        }

        return $customerPaymentMethods;
    }

    /**
     * Get customer Payment
     *
     * @param int $paymentMethodId
     * @return array
     */
    public function getCustomerPaymentMethod($paymentMethodId = 0)
    {
        $currentCustomerPayment = [];
        /** @var  $customerId */
        $customerId = $this->getCustomerId();
        /** @var  $customerPaymentMethods */
        $customerPaymentMethods = $this->getCustomerPaymentMethods($customerId);

        if (count($customerPaymentMethods) > 0) {
            foreach ($customerPaymentMethods as $customerPaymentMethod) {
                $customerPaymentMethod = (array)$customerPaymentMethod;
                $customerPaymentMethodId = $customerPaymentMethod['MethodID'] ?? '';

                if ($paymentMethodId == $customerPaymentMethodId) {
                    $currentCustomerPayment = $customerPaymentMethod;
                }
            }
        }
        return $currentCustomerPayment;
    }

    /**
     * Is Save Cards Enabled
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isSaveCardsEnabled()
    {
        $storeId = $this->_configModel->getStore()->getId();
        return $this->_configModel->getIsSaveCreditCards($storeId);
    }

    /**
     * Is Save Bank Accounts Enabled
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isSaveBankAccountsEnabled()
    {
        $storeId = $this->_configModel->getStore()->getId();
        return $this->_configModel->getIsSaveBankAccounts($storeId);
    }

    /**
     * Get Payment Method type
     *
     * @param string $paymentMethodName
     * @return string
     */
    public function getPaymentMethodType($paymentMethodName = '')
    {
        $paymentMethodType = "cc";
        // phpcs:disable
        if (strpos($paymentMethodName, strtolower('checking')) > -1 ||
            strpos($paymentMethodName, strtolower('savings')) > -1) {
            $paymentMethodType = "ach";
        }
        // phpcs:enable

        return $paymentMethodType;
    }

    /**
     * Render Customer Payment Methods DropDown
     *
     * @param mixed $customerId
     * @param mixed $selectedMethodId
     * @param mixed $isDisabled
     * @param mixed $methodType
     * @return string
     */
    public function renderCustomerPaymentMethodsDropDown(
        $customerId = null,
        $selectedMethodId = '',
        $isDisabled = "",
        $methodType = "cc"
    ): string
    {
        /** @var  $customerPaymentMethods */
        $customerPaymentMethods = $this->getCustomerPaymentMethods($customerId);
        $customerPaymentMethodsWithType = [];

        if (count($customerPaymentMethods) > 0) {
            foreach ($customerPaymentMethods as $customerPaymentMethod) {
                $paymentMethodType = $customerPaymentMethod->MethodType;

                if ($paymentMethodType === $methodType) {
                    $customerPaymentMethodsWithType["cards"][] = $customerPaymentMethod;
                } else {
                    $customerPaymentMethodsWithType["check"][] = $customerPaymentMethod;
                }
            }
        }
        /** @var  $renderedHtml */
        $renderedHtml = '<select
                        ' . $isDisabled . '
                        name="subscription[recurring_new_payment_method_id]"
                        id="recurring-payment-methods-dropdown"
                        class="payment-form-input"
                        data-validate="{required:false}"
                        >';

        /** @var  $customerCreditCardPaymentMethods */
        $customerCreditCardPaymentMethods = $customerPaymentMethodsWithType["cards"] ?? [];
        $renderedHtml .= '<option value="">' . __("Please select") . '</option>';
        if (count($customerCreditCardPaymentMethods) > 0 && $this->getIsCreditCardPaymentsAllowed()) {
            $renderedHtml .= '<optgroup label="' . __('Credit Cards') . '">';

            foreach ($customerCreditCardPaymentMethods as $cardPaymentMethod) {
                $paymentMethodName = $cardPaymentMethod->MethodName;
                $paymentMethodJson = json_decode($paymentMethodName);

                if (is_object($paymentMethodJson)) {
                    $paymentMethodName = $paymentMethodJson->a . "-" . $paymentMethodJson->b;
                }

                $paymentMethodId = $cardPaymentMethod->MethodID;
                $renderedHtml .= '<option value="' . $paymentMethodId . '|' . $paymentMethodName . '|CC"';
                if ($paymentMethodId == $selectedMethodId) {
                    $renderedHtml .= ' selected ';
                }
                $renderedHtml .= '>';
                $renderedHtml .= ucwords($paymentMethodName);
                $renderedHtml .= '</option>';
            }

            $renderedHtml .= '</optgroup>';
        }

        /** @var  $customerCreditCardPaymentMethods */
        $customerCreditCardPaymentMethods = $customerPaymentMethodsWithType["check"] ?? [];
        if (count($customerCreditCardPaymentMethods) > 0 && $this->getIsBankAccountPaymentsAllowed()) {
            $renderedHtml .= '<optgroup label="' . __('Bank Accounts') . '">';
            foreach ($customerCreditCardPaymentMethods as $achPaymentMethod) {
                $paymentMethodName = $achPaymentMethod->MethodName;
                $paymentMethodId = $achPaymentMethod->MethodID;

                $renderedHtml .= '<option value="' . $paymentMethodId . '|' . $paymentMethodName . '|ACH"';
                if ($paymentMethodId == $selectedMethodId) {
                    $renderedHtml .= ' selected ';
                }
                $renderedHtml .= '>';
                $renderedHtml .= ucwords($paymentMethodName);
                $renderedHtml .= '</option>';
            }

            $renderedHtml .= '</optgroup>';
        }

        $renderedHtml .= '</select>';

        return $renderedHtml;
    }

    /**
     * Get Un subsribe URL
     *
     * @return string
     */
    public function getUnsubscribeUrl()
    {
        return $this->getUrl(
            'ebizcharge/recurrings/unsubscribe',
            [
                '_secure' => true
            ]
        );
    }

    /**
     * Get Unsubsribe URL
     *
     * @return string
     */
    public function getUpdateSubscriptionUrl()
    {
        return $this->getUrl(
            'ebizcharge/recurrings/update',
            [
                '_secure' => true
            ]
        );
    }

    /**
     * Get CC Available Types
     *
     * @return array
     */
    public function getCcAvailableTypes(): array
    {
        $storeId = $this->_configModel->getStoreId();
        return $this->_configModel->getSelectedPaymentCardTypes($storeId);
    }

    /**
     * Get Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_recurringFactory->create()->getStoreId();
    }

    /**
     * Get Payment CC Types
     *
     * @return string[]
     */
    public function getPaymentCctypes()
    {
        return explode(',', $this->_configModel->getPaymentCctypes());
    }

    /**
     * Get configured Frequencies
     *
     * @param mixed $selectedFrequency
     * @return void
     */
    public function getConfiguredFrequencies($selectedFrequency = null)
    {
        $this->myConfig->getRecurringFrequencyOptions($selectedFrequency);
    }

    /**
     * Get CC Months
     *
     * @return array|mixed|null
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');

        if ($months === null) {
            $months[0] = __('Month');
            $months = array_merge($months, $this->paymentConfig->getMonths());
            $this->setData('cc_months', $months);
        }

        return $months;
    }

    /**
     * Get CC Years
     *
     * @return array|mixed|null
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');

        if ($years === null) {
            $years = $this->paymentConfig->getYears();
            $years = [0 => __('Year')] + $years;
            $this->setData('cc_years', $years);
        }

        return $years;
    }

    /**
     * Get Payment Save Payment
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getPaymentSavePayment()
    {
        $storeId = $this->_configModel->getStore()->getId();
        return $this->_configModel->getPaymentSavePayment($storeId);
    }

    /**
     * Get Ebizcharge Method Id
     *
     * @return mixed
     */
    public function getEbzcMethodId()
    {
        return $this->getRequest()->getParam('mid');
    }

    /**
     * Get formatted address html
     *
     * @param mixed $orderId
     * @param null|mixed $shippingAddressId
     * @return string|null
     */
    public function getCustomerShippingAddress($orderId, $shippingAddressId = null): ?string
    {
        try {
            if (empty($orderId) || !empty($shippingAddressId)) {
                $address = $this->addressRepository->getById($shippingAddressId);
            } else {
                $order = $this->orderRepository->get($orderId);
                $address = $order->getShippingAddress();
            }

            /** @var RendererInterface $renderer */
            $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();

            return $renderer->renderArray($this->addressMapper->toFlatArray($address));
        } catch (Exception $e) {
            /** Logger */
            $this->ebizchargeLogger->addCritical(__(
                "Exception occurred during rendering array Error:" . $e->getMessage()
            ));

            return null;
        }
    }

    /**
     * Get recurring product
     *
     * @param int $productId
     * @return ProductInterface|null
     */
    public function getProduct(int $productId): ?ProductInterface
    {
        try {
            return $this->productRepository->getById($productId);
        } catch (Exception $e) {
            $this->ebizchargeLogger->addCritical(__(
                "Exception occurred during getting product: " . $e->getMessage()
            ));
            return null;
        }
    }



    /**
     * If indefinite recurring is enabled in config
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function indefiniteRecurring(): bool
    {
        $storeId = $this->_configModel->getStore()->getId();
        return $this->_configModel->indefiniteRecurring($storeId);
    }

    /**
     * Get Ebizcharge Customer Id
     *
     * @return int
     */
    public function getEbzcCustId()
    {
        $customer = $this->getCustomer();
        return $customer->getEcCustId();
    }

    /**
     * Check is Avs Cvv Zip enabled/disabled
     *
     * @return bool
     */
    public function isAvsCvvZipEnabled(): bool
    {
        $storeId = $this->_configModel->getStore()->getId();
        return $this->_configModel->isAvsCvvZipEnabled($storeId);
    }

    /**
     * Is Subscription Enabled
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isSubscriptionEnabled(): bool
    {
        $storeId = $this->_configModel->getStore()->getId();
        return $this->_configModel->isRecurringActive($storeId);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isSurchargeEnabled(): bool
    {
        $storeId = $this->_configModel->getStore()->getId();
        return $this->customerFactory->create()->isSurchargeEnabled($storeId);
    }

    /**
     * Prepare Layout
     *
     * @return $this|Recurrings
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var  $recurringCollection */
        $recurringCollection = $this->getRecurringCollection();

        if ($recurringCollection) {

            /** @var  $pager */
            $pager = $this->getLayout()
                ->createBlock(
                    Pager::class,
                    'ebizcharge.recurrings.list.pager'
                )->setCollection($recurringCollection)
                ->setAvailableLimit($this->tranApi->getDefaultLimits())
                ->setShowPerPage(true);

            /**
             * Setting Child
             */
            $this->setChild('pager', $pager);

            /**
             * Recurring Collection
             */
            $recurringCollection->load();
        }

        return $this;
    }

    /**
     * Get recurring collection for pagination
     *
     * @return array|Collection
     */
    public function getRecurringCollection()
    {
        if (!($customerId = $this->getMageCustId())) {
            return [];
        }

        /** @var  $storeId */
        $storeId = $this->myConfig->getStore()->getId();

        /** @var  $recurringCollection */
        $recurringCollection =
            $this->recurringCollectionFactory
                ->create()
                ->addFieldToFilter(Recurring::MAGE_CUST_ID, $customerId)
                ->addFieldToFilter(
                    Recurring::REC_STATUS,
                    ['neq' => Recurring::EBIZCHARGE_RECURRING_STATUS_CANCELED]
                )
                ->addFieldToFilter(Recurring::EB_REC_STORE_ID, $storeId)
                ->setOrder(Recurring::ENTITY_ID, SortOrder::SORT_DESC)
                ->setPageSize($this->getPageSize())
                ->setCurPage($this->getPageNumber());

        return $recurringCollection;
    }

    /**
     * Get page size limit for page
     *
     * @return mixed
     */
    public function getPageSize()
    {
        return $this->getRequest()->getParam('limit', TranApi::DEFAULT_PAGE_LISTING_LIMIT);
    }

    /**
     * Current page number for pagination
     *
     * @return mixed
     */
    public function getPageNumber()
    {
        return $this->getRequest()->getParam('p');
    }

}
