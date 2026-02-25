<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/27/2019 10:48 AM
 * @brief
 */

namespace Crimson\MachOrder\Model\Api;

use Crimson\Checkout\Model\Service\OrderBackorders;
use Crimson\InStorePickup\Service\InStorePickupMethod;
use Crimson\MachBase\Model\Api\AbstractApi;
use Crimson\MachBase\Model\Api\ApiContext;
use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCustomer\Model\Service\GetMachCustomerNumber;
use Crimson\MachCustomer\Model\Service\SetMachCustomerNumber;
use Crimson\MachOrder\Model\Api\Build\OrderItem as OrderItemBuilder;
use Crimson\MachOrder\Model\Config;
use Crimson\MachOrder\Model\Service\PaymentDetails;
use Crimson\MachOrder\Model\Service\PaymentTerms;
use Crimson\MachShipping\Model\Shipping\Carrier\Source\Method;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as GetDateTime;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Crimson\Checkout\Model\Service\IsOrderDropship;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Attribute as OrderAttributeResource;
use Magento\Sales\Model\ResourceModel\Order\Status\History as OrderStatusHistoryResource;
use Crimson\MachOrder\Model\Order\Attributes\SaveHandler as MachOrderOrderAttributesSaveHandler;
use Crimson\MachCustomer\Model\Order\Attributes\SaveHandler as MachCustomerOrderAttributesSaveHandler;

/**
 * Class AddOrder
 * @package Crimson\MachOrder\Model\Api
 */
class AddOrder extends AbstractApi
{
    CONST ORDER_SUBTOTAL_HSC_VALUE = 75;

    /**
     * @var OrderItemBuilder
     */
    protected $buildOrderItem;
    /**
     * @var GetDateTime
     */
    protected $dateTime;
    /**
     * @var GetMachCustomerNumber
     */
    protected $getMachCustomerNumber;
    /**
     * @var HealthCheck
     */
    protected $healthCheck;
    /**
     * @var MachCustomerNumber
     */
    protected $machCustomerNumber;
    /**
     * @var Config
     */
    protected $machOrderConfig;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var PaymentTerms
     */
    protected $paymentTerms;
    /**
     * @var SetMachCustomerNumber
     */
    protected $setMachCustomerNumber;
    /**
     * @var OrderBackorders
     */
    protected $orderBackorders;

    /**
     * @var IsOrderDropship
     */
    protected $isOrderDropship;

    /**
     * @var OrderAttributeResource
     */
    protected $orderAttributeResource;

    /**
     * @var OrderStatusHistoryResource
     */
    protected $orderStatusHistoryResource;

    /**
     * @var MachCustomerOrderAttributesSaveHandler
     */
    protected $machCustomerOrderAttributesSaveHandler;

    /**
     * @var MachOrderOrderAttributesSaveHandler
     */
    protected $machOrderOrderAttributesSaveHandler;

    public function __construct(
        ApiContext $apiContext,
        HealthCheck $healthCheck,
        MachConfig $machConfig,
        OrderItemBuilder $buildOrderItem,
        Config $machOrderConfig,
        GetMachCustomerNumber $getMachCustomerNumber,
        SetMachCustomerNumber $setMachCustomerNumber,
        GetDateTime $dateTime,
        PaymentTerms $paymentTerms,
        OrderRepositoryInterface $orderRepository,
        OrderBackorders $orderBackorders,
        IsOrderDropship $isOrderDropship,
        Subscriber $subscriberResource,
        protected \Crimson\MachShipping\Model\Config $shippingConfig,
        protected InStorePickupMethod $inStorePickupMethod,
        protected PaymentDetails $paymentDetails,
        OrderAttributeResource $orderAttributeResource = null,
        OrderStatusHistoryResource $orderStatusHistoryResource = null,
        MachCustomerOrderAttributesSaveHandler $machCustomerOrderAttributesSaveHandler = null,
        MachOrderOrderAttributesSaveHandler $machOrderOrderAttributesSaveHandler = null
    ) {
        parent::__construct($apiContext, $machConfig, $subscriberResource);
        $this->dateTime              = $dateTime;
        $this->orderRepository       = $orderRepository;
        $this->buildOrderItem        = $buildOrderItem;
        $this->machOrderConfig       = $machOrderConfig;
        $this->healthCheck           = $healthCheck;
        $this->setMachCustomerNumber = $setMachCustomerNumber;
        $this->getMachCustomerNumber = $getMachCustomerNumber;
        $this->paymentTerms          = $paymentTerms;
        $this->orderBackorders       = $orderBackorders;
        $this->isOrderDropship       = $isOrderDropship;

        if (!$orderAttributeResource) {
            $orderAttributeResource = \Magento\Framework\App\ObjectManager::getInstance()->get(OrderAttributeResource::class);
        }
        $this->orderAttributeResource = $orderAttributeResource;

        if (!$orderStatusHistoryResource) {
            $orderStatusHistoryResource = \Magento\Framework\App\ObjectManager::getInstance()->get(OrderStatusHistoryResource::class);
        }
        $this->orderStatusHistoryResource = $orderStatusHistoryResource;

        if (!$machCustomerOrderAttributesSaveHandler) {
            $machCustomerOrderAttributesSaveHandler = \Magento\Framework\App\ObjectManager::getInstance()->get(MachCustomerOrderAttributesSaveHandler::class);
        }
        $this->machCustomerOrderAttributesSaveHandler = $machCustomerOrderAttributesSaveHandler;

        if (!$machOrderOrderAttributesSaveHandler) {
            $machOrderOrderAttributesSaveHandler = \Magento\Framework\App\ObjectManager::getInstance()->get(MachOrderOrderAttributesSaveHandler::class);
        }
        $this->machOrderOrderAttributesSaveHandler = $machOrderOrderAttributesSaveHandler;
    }

    /**
     * @param OrderInterface|Order $order
     * @param bool                 $force
     *
     * @return bool
     * @throws LocalizedException
     * @throws \Exception
     */
    public function addOrder(Order $order, bool $force = false): bool
    {
        if (!$this->healthCheck->isUp()) {
            //will be attempted again on next cron run.
            return false;
        }

        $action = self::CALL_ADD_ORDER;
        $this->debugLog('Beginning ' . $action . ' Call ' . $force . ' for ' . $order->getIncrementId());
        $status = false;

        //if order is held or already submitted, but not forced, exit.
        if (($order->getExtensionAttributes()->getMachSubmitted()
                || $order->getExtensionAttributes()->getMachSubmitHold())
            && !$force
        ) {
            $this->debugLog('order is held or already submitted for ' . $order->getIncrementId());

            return false;
        }

        //If payment processing is not complete, exit
        //ZIP-687
        //ZIP-762-755 Excluding also Money Order and returning false.
        $payment_method_code = $order->getPayment()->getMethodInstance()->getCode();
        if (($order->getBaseTotalDue() > 0)
            && $payment_method_code !== 'cashondelivery'
            && $payment_method_code !== 'checkmo') {
            $payment = $order->getPayment();
            if ($payment->getMethod() === 'paypal_express') {
                $this->debugLog('payment still due - rejecting for now ' . $order->getIncrementId());

                return false;
            }
            if ($payment->getBaseAmountAuthorized() != $order->getBaseTotalDue()
                && $payment_method_code !== 'm2epropayment') {
                $this->debugLog('payment due mismatch - rejecting for now ' . $order->getIncrementId());

                return false;
            }
            if ($payment_method_code === 'm2epropayment' && $order->getStatus() !== 'pending') {

                $this->debugLog('issue with m2epro payment' . $order->getIncrementId());

                return false;
            }
        }

        if (strlen($order->getState()) < 1) {
            $this->debugLog('order state not expected ' . $order->getState() . ' ' . $order->getIncrementId());

            return false;
        }

        // Now we review the HSC value before send the Order, and update the Order HSC value on Magento.
        $hsc = $this->_getHscLastValue($order);
        if ($hsc == "Y") {
            $order->getExtensionAttributes()->setMachHsc(1);
        } else {
            $order->getExtensionAttributes()->setMachHsc(0);
        }

        //if the order has been submitted and we are here, this is a resubmission.
        $resubmit = (bool)$order->getExtensionAttributes()->getMachSubmitted();

        try {
            $baseGrandTotal   = round($order->getBaseGrandTotal(), 2);
            $paymentTermsCode = $this->_getPaymentTerms($order);
            $billingAddress   = $order->getBillingAddress();
            $shippingAddress  = $order->getShippingAddress();
            if (!$shippingAddress) {
                $shippingAddress = new DataObject();
            }

            $arguments = array(
                $this->_soapVar($this->getSecurityCode(), 'SecurityCode'),
                /*
                 * 1 – Add Order to Mach System
                 * 2 – Add Catalog Request to Mach System
                 * 3 – Add a new customer (no order or catalog request)
                 */
                $this->_soapVar(1, 'ActionCode'),
                $this->_soapVar($order->getIncrementId(), 'ReferenceNumber'),
            );

            $billAdvDate = !empty($billingAddress->getShipAdvDate())
                ? $this->getIntegrationDate($billingAddress->getShipAdvDate())->format('m/d/y')
                : "";

            $shipAdvDate = !empty($shippingAddress->getShipAdvDate())
                ? $this->getIntegrationDate($shippingAddress->getShipAdvDate())->format('m/d/y')
                : "";

            $addOrderInData = array(
                $this->_soapVar($this->getMachCustomerNumber->get($order), 'CustNumber'),
                $this->_soapVar($billingAddress->getFirstname() . ' ' . $billingAddress->getLastname(), 'BillName'),
                $this->_soapVar($billingAddress->getStreet()[0] ?? null, 'BillAdd1'),
                $this->_soapVar(($billingAddress->getStreet()[1] ?? null), 'BillAdd2'),
                $this->_soapVar($billingAddress->getCity(), 'BillCity'),
                $this->_soapVar($billingAddress->getRegionCode() ?: $billingAddress->getRegion(), 'BillState'),
                $this->_soapVar($billingAddress->getPostcode(), 'BillZip'),
                $this->_soapVar(null, 'BillCounty'),
                $this->_soapVar($billingAddress->getCountryId(), 'BillCountry'),
                $this->_soapVar($billingAddress->getTelephone(), 'BillPhone'),
                $this->_soapVar($billingAddress->getFax(), 'BillFax'),
                $this->_soapVar($billingAddress->getEmail(), 'BillEmail'),
                $this->_soapVar(null, 'BillPIN'),
                $this->_soapVar(null, 'BillContact'),
                $this->_soapVar(null, 'BillTitle'),
                $this->_soapVar(
                    $this->_castBoolToString($this->_isNewsletterSubscribed($billingAddress)),
                    'BillMailList'
                ),
                $this->_soapVar(
                    $this->_castBoolToString($this->_isNewsletterSubscribed($billingAddress)),
                    'BillRentList'
                ),
                $this->_soapVar(
                    $this->_castBoolToString($this->_isNewsletterSubscribed($billingAddress)),
                    'BillEmailList'
                ),
                $this->_soapVar(null, 'BillOpen1'),
                $this->_soapVar(null, 'BillOpen2'),
                $this->_soapVar(null, 'BillOpen3'),
                $this->_soapVar(null, 'BillOpen4'),
                $this->_soapVar(null, 'BillOpen5'),
                $this->_soapVar(null, 'TaxExemptNumber'),
                $this->_soapVar(null, 'SoldAccount'),
                $this->_soapVar(null, 'ShipAccount'),
                $this->_soapVar($shippingAddress->getName(), 'ShipName'),
                $this->_soapVar($shippingAddress->getStreetLine(1), 'ShipAdd1'),
                $this->_soapVar(($shippingAddress->getStreetLine(2) ?: null), 'ShipAdd2'),
                $this->_soapVar($shippingAddress->getCity(), 'ShipCity'),
                $this->_soapVar($shippingAddress->getRegionCode() ?: $shippingAddress->getRegion(), 'ShipState'),
                $this->_soapVar($shippingAddress->getPostcode(), 'ShipZip'),
                $this->_soapVar(null, 'ShipCounty'),
                $this->_soapVar($shippingAddress->getCountryId(), 'ShipCountry'),
                $this->_soapVar($shippingAddress->getTelephone(), 'ShipPhone'),
                $this->_soapVar($order->getCustomerEmail(), 'OrderEmailAddress'),
                $this->_soapVar($this->_castBoolToString($resubmit), 'Resubmitted'),
                //dates should be in EST (zip locale)
                $this->_soapVar($this->getIntegrationDate($order->getCreatedAt())->format('m/d/y'), 'OrderDate'),
                $this->_soapVar($this->getIntegrationDate($order->getCreatedAt())->format('H:i:s'), 'OrderTime'),
                $this->_soapVar(null, 'DueDate'),
                $this->_soapVar('Y', 'UpdateAccount'),

                $this->_soapVar($order->getBaseSubtotal(), 'MerchAmt'),
                $this->_soapVar(round($order->getBaseShippingAmount(), 2), 'ShipAmt'),
                $this->_soapVar(round($order->getBaseAdditionalHandlingAmount(), 2), 'AddAmt'),
                $this->_soapVar(null, 'CODAmt'),
                $this->_soapVar(null, 'OtherAmt'),
                $this->_soapVar(null, 'GCNum1'),
                $this->_soapVar(null, 'GCAmt1'),
                $this->_soapVar(null, 'GCNum2'),
                $this->_soapVar(null, 'GCAmt2'),
                $this->_soapVar(null, 'GCNum3'),
                $this->_soapVar(null, 'GCAmt3'),
                $this->_soapVar(null, 'GCNum4'),
                $this->_soapVar(null, 'GCAmt4'),
                $this->_soapVar(null, 'GCNum5'),
                $this->_soapVar(null, 'GCAmt5'),
                $this->_soapVar(round($order->getBaseTaxAmount(), 2), 'TaxAmt'),
                $this->_soapVar($baseGrandTotal, 'TotalAmt'),
                $this->_soapVar(null, 'TaxCode'),
                $this->_soapVar($paymentTermsCode, 'PaymentTerms'),

                $this->_soapVar($this->_getMachShippingCode($order), 'ShipVia'),
                $this->_soapVar($order->getCouponCode(), 'KeyCode'),
                $this->_soapVar('Regular', 'OrderType'),
                $this->_soapVar('W', 'Channel'),
                $this->_soapVar(null, 'MachOrderNumber'),
                $this->_soapVar(null, 'Attention'),

                $this->_soapVar(null, 'PriceLvl'),
                $this->_soapVar($hsc, 'HSC'),
                $this->_soapVar(null, 'ResCom'),
                $this->_soapVar(null, 'OrderGiftFlag'),
                $this->_soapVar(null, 'OrderGiftMsgLine'),
                $this->_soapVar(null, 'OrderOpenCode1'),
                $this->_soapVar(null, 'OrderOpenCode2'),
                $this->_soapVar(null, 'OrderOpenCode3'),
                $this->_soapVar(null, 'OrderOpenCode4'),
                $this->_soapVar(null, 'OrderOpenCode5'),
                $this->_soapVar(null, 'OrderOpenCode6'),
                $this->_soapVar(null, 'OrderOpenCode7'),
                $this->_soapVar(null, 'OrderOpenCode8'),
                $this->_soapVar(null, 'OrderOpenCode9'),
                $this->_soapVar(null, 'OrderOpenCode10'),
                $this->_soapVar(null, 'OrderOpenCode11'),
                $this->_soapVar(null, 'OrderOpenCode12'),
                $this->_soapVar(null, 'OrderOpenCode13'),
                $this->_soapVar(null, 'OrderOpenCode14'),
                $this->_soapVar(null, 'OrderOpenCode15'),
                $this->_soapVar(null, 'BillOpen6'),
                $this->_soapVar(null, 'BillOpen7'),
                $this->_soapVar(null, 'BillOpen8'),
                $this->_soapVar(null, 'BillOpen9'),
                $this->_soapVar(null, 'BillOpen10'),
                $this->_soapVar(null, 'BillOpen11'),
                $this->_soapVar(null, 'BillOpen12'),
                $this->_soapVar(null, 'BillOpen13'),
                $this->_soapVar(null, 'BillOpen14'),
                $this->_soapVar(null, 'BillOpen15'),

                //will updated bill to address in mach if different.
                $this->_soapVar(null, 'BillUpdateFlag'),
                $this->_soapVar(null, 'Reserved15'),
                $this->_soapVar(null, 'Reserved16'),
                $this->_soapVar(null, 'Reserved17'),
                $this->_soapVar(null, 'Reserved18'),
                $this->_soapVar(null, 'Reserved19'),
                $this->_soapVar(null, 'Reserved20'),
                $this->_soapVar(null, 'Reserved21'),
                $this->_soapVar(null, 'Reserved22'),
                $this->_soapVar(null, 'Reserved23'),
                $this->_soapVar(null, 'Reserved24'),
                $this->_soapVar(null, 'Reserved25'),
                $this->_soapVar(null, 'Reserved26'),

                //billing address validation fields
                $this->_soapVar($this->_castBoolToString($billingAddress->getShipAdv()), 'BillAdv'),
                $this->_soapVar($billAdvDate, 'BillAdvDate'),
                $this->_soapVar($billingAddress->getShipAdvDpi(), 'BillAdvDPI'),
                //(B)usiness OR (R)esidential
                $this->_soapVar($billingAddress->getShipAdvDi(), 'BillAdvDI'),
                $this->_soapVar(null, 'BillAdvOpen1'),
                $this->_soapVar(null, 'BillAdvOpen2'),

                //shipping address validation fields
                $this->_soapVar($this->_castBoolToString($shippingAddress->getShipAdv()), 'ShipAdv'),
                $this->_soapVar($shipAdvDate, 'ShipAdvDate'),
                $this->_soapVar($shippingAddress->getShipAdvDpi(), 'ShipAdvDPI'),
                //(B)usiness OR (R)esidential
                $this->_soapVar($shippingAddress->getShipAdvDi(), 'ShipAdvDI'),
                $this->_soapVar(null, 'ShipAdvOpen1'),
                $this->_soapVar(null, 'ShipAdvOpen2'),

                $this->_soapVar(null, 'FreightCode'),

                $this->_soapVar(null, 'Filler'),
                $this->_soapVar(null, 'Filler2'),
            );

            try {
                /** @var InfoInterface $infoInstance */
                $infoInstance = $order->getPayment()->getMethodInstance()->getInfoInstance();

                switch ($paymentTermsCode) {
                    case PaymentTerms::PAYMENT_TERMS_CHECK:
                        $addOrderInData[] = $this->_soapVar('Unknown', 'CheckNumber');
                        $addOrderInData[] = $this->_soapVar($baseGrandTotal, 'CheckAmt');
                        break;
                    case PaymentTerms::PAYMENT_TERMS_PAYPAL:
                        $addOrderInData[] = $this->_soapVar(
                            $order->getPayment()->getLastTransId(), 'PayPalTransactionID'
                        );
                        $addOrderInData[] = $this->_soapVar($baseGrandTotal, 'PayPalAmount');
                        break;
                    case PaymentTerms::PAYMENT_TERMS_PAYWARE:
                        $addOrderInData[] = $this->_soapVar(
                            ($order->getPayment()->getLastTransId()
                                ?: $order->getPayment()->getCcTransId()), 'PaywareTransactionID'
                        );
                        /** @noinspection PhpMethodParametersCountMismatchInspection */
                        $addOrderInData[] = $this->_soapVar(
                            $order->getPayment()->getAdditionalInformation('auth_code'),
                            'PaywareAuthCode'
                        );

                        $authDate = $infoInstance->getAdditionalInformation('auth_date');
                        if ($authDate) {
                            //we don't need to register the transaction time from Payware's response.
                            //we are only registering in auth_date the date for the transaction from Payware's response
                            //that info also comes configured to Eastern time, so we don't need to convert to Eastern
                            //again here.
                            $authDate = new \DateTime($authDate);
                            $authFinalDate = $authDate->format('m/d/y');
                        } else {
                            $authDate = $this->getIntegrationDate($order->getCreatedAt());
                            $authFinalDate = $authDate->format('m/d/y');
                        }

                        $addOrderInData[] = $this->_soapVar($authFinalDate, 'PaywareAuthDate');

                        $addOrderInData[] = $this->_soapVar($baseGrandTotal, 'PaywareAuthAmount');
                        $addOrderInData[] = $this->_soapVar(
                            $infoInstance->getAdditionalInformation('payware_card'), 'PaywarePartialCard'
                        );
                        $addOrderInData[] = $this->_soapVar(
                            $this->getPaywareFormattedExpirationDate($order), 'PaywareExpDate'
                        );
                        $addOrderInData[] = $this->_soapVar(
                            $infoInstance->getAdditionalInformation('avs_code'), 'PaywareAVSCode'
                        );
                        $addOrderInData[] = $this->_soapVar(
                            $infoInstance->getAdditionalInformation('cvv_code'), 'PaywareCVVCode'
                        );
                        $addOrderInData[] = $this->_soapVar(
                            $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname(), 'PaywareNameOnCard'
                        );
                        $addOrderInData[] = $this->_soapVar(
                            $billingAddress->getStreet()[0] ?? null, 'PaywareCardAddress'
                        );
                        $addOrderInData[] = $this->_soapVar($billingAddress->getPostcode(), 'PaywareCardZip');
                        break;
                    case PaymentTerms::PAYMENT_TERMS_CREDIT_CARD:
                        $addOrderInData[] = $this->_soapVar($order->getPayment()->getCcNumber(), 'CCN');
                        $addOrderInData[] = $this->_soapVar(
                            $this->getPaywareFormattedExpirationDate($order), 'CCEXP'
                        );
                        $addOrderInData[] = $this->_soapVar(
                            $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname(), 'NameOnCard'
                        );
                        $addOrderInData[] = $this->_soapVar($billingAddress->getStreet()[0] ?? null, 'CardAddress');
                        $addOrderInData[] = $this->_soapVar($billingAddress->getPostcode(), 'CardZip');
                        $addOrderInData[] = $this->_soapVar($order->getPayment()->getCcCid(), 'CVV');
                        break;
                    case PaymentTerms::PAYMENT_TERMS_NMI:
                        $transaction = $this->paymentDetails->getTransactionByOrder($order);
                        $addOrderInData[] = $this->_soapVar(($order->getPayment()->getLastTransId() ?? $order->getPayment()->getCcTransId()), 'PaywareTransactionID');
                        $addOrderInData[] = $this->_soapVar($this->paymentDetails->getAuthCode($transaction), 'PaywareAuthCode');

                        $authDate = $transaction->getCreatedAt();
                        if (!$authDate) {
                            $authDate = $order->getCreatedAt();
                        }
                        $authFinalDate = $this->getIntegrationDate($authDate)->format('m/d/y');
                        $addOrderInData[] = $this->_soapVar($authFinalDate, 'PaywareAuthDate');
                        $addOrderInData[] = $this->_soapVar($order->getPayment()->getBaseAmountAuthorized(), 'PaywareAuthAmount');
                        $addOrderInData[] = $this->_soapVar($infoInstance->getAdditionalInformation('card_number'), 'PaywarePartialCard');
                        $addOrderInData[] = $this->_soapVar($infoInstance->getAdditionalInformation('card_exp'), 'PaywareExpDate');
                        $addOrderInData[] = $this->_soapVar($this->paymentDetails->getAvsCode($transaction), 'PaywareAVSCode');
                        $addOrderInData[] = $this->_soapVar($this->paymentDetails->getCvvCode($transaction), 'PaywareCVVCode');
                        $addOrderInData[] = $this->_soapVar($billingAddress->getFirstname() . ' ' . $billingAddress->getLastname(), 'PaywareNameOnCard');
                        $addOrderInData[] = $this->_soapVar($billingAddress->getStreet()[0] ?? null, 'PaywareCardAddress');
                        $addOrderInData[] = $this->_soapVar($billingAddress->getPostcode(), 'PaywareCardZip');
                        break;
                }
            } catch (LocalizedException $e) {
                $message = 'Unable to set payment data on ' . $action . ' call: ' . $e->getMessage();
                $this->infoLog($message);
            } catch (\Exception $e) {
                $message = $this->apiContext->buildExceptionMessage($e);
                $message = __('Unable to set payment data on %1 call: %2', $action, $message);
                $this->criticalLog($message);
            }

            //build item array
            foreach ($order->getAllVisibleItems() as $orderItem) {
                /** @var Item $orderItem */
                if ($orderItem->getProductType() === Configurable::TYPE_CODE) {
                    $childOrderItems = $orderItem->getChildrenItems();
                    $childOrderItem  = reset($childOrderItems);
                    $orderItemData   = $this->buildOrderItem->build($childOrderItem);
                } else {
                    $orderItemData = $this->buildOrderItem->build($orderItem);
                }
                $addOrderInData[] = $this->_soapVar($orderItemData, 'OrderItem');
            }

            $coreChargeSkus = $order->getData('core_charge_sku_list');

            if ($coreChargeSkus) {
                if (!is_array($coreChargeSkus)) {
                    $coreChargeSkus = $this->json->unserialize($coreChargeSkus);
                }

                foreach ($coreChargeSkus as $index => $coreChargeData) {
                    $coreChargeSku = $coreChargeData['core_charge_sku'] ?? null;
                    if (!$coreChargeSku) {
                        //backwards compatibility with sku as index.
                        if (!is_numeric($index)) {
                            $coreChargeSku = $index;
                        }
                    }

                    if (array_key_exists('iskit', $coreChargeData) && $coreChargeData['iskit'] == 0)
                        //do not add core charge items to order when product is a kit
                        //ZIP-612
                    {
                        $qty = $coreChargeData['qty'] ?? 1;
                        $orderItem = $order->getItemsCollection()->getNewEmptyItem();
                        $orderItem->addData(
                            [
                                'sku'            => $this->getMachSku($coreChargeSku),
                                'qty_ordered'    => $qty,
                                'product_type'   => Type::TYPE_SIMPLE,
                                'base_price'     => isset($coreChargeData['core_charge_base_price_amount']) ? round($coreChargeData['core_charge_base_price_amount'], 4) : 0.0000,
                                'base_row_total' => isset($coreChargeData['core_charge_amount']) ? round($coreChargeData['core_charge_amount'], 4) : 0.0000,
                                'name'           => 'Core Charge - ' . $coreChargeSku,
                            ]
                        );

                        $orderItemData    = $this->_buildAddOrderItem($orderItem);
                        $addOrderInData[] = $this->_soapVar($orderItemData, 'OrderItem');
                    }
                }
            }

            $arguments[] = $this->_soapVar($addOrderInData, 'ADD_ORDER_IN');

            //convert to data object so we can pass by reference through event manager
            $arguments = new DataObject([
                'arguments' => $arguments,
            ]);

            $this->eventManager->dispatch('mach_add_order_before_request', [
                'order' => $order,
                'arguments' => $arguments,
            ]);

            //extract modified argument list back into array for making api request.
            $arguments = $arguments->getData('arguments');

            $response = $this->makeRequest($action, $arguments);

            if (!isset($response->ERROR_OUT->ErrorNumber)) {
                throw new LocalizedException(__('Invalid response received, unable to determine if order was added.'));
            } else {
                $errorNumber  = $response->ERROR_OUT->ErrorNumber;
                $errorMessage = $response->ERROR_OUT->ErrorMsg;

                $saveOrderNumber = false;
                if (!empty($response->OrderNumber) && !$order->getExtensionAttributes()->getMachOrderNumber()) {
                    $saveOrderNumber = true;
                }

                if (!$this->_isSuccess($action, $errorNumber) && $saveOrderNumber === false) {
                    $errorCount     = (int)$order->getExtensionAttributes()->getMachSubmitErrorCount() + 1;
                    $message        = 'Error occurred attempting to add order %5$s to MACH ERP.<br/>';
                    $errorThreshold = $this->machOrderConfig->getAddOrderErrorThreshold();

                    if ($errorCount >= $errorThreshold) {
                        $message .= '<strong>Error count of %1$s has exceeded threshold of %2$s; no more attempts will be made automatically.</strong><br/>';
                        $order->getExtensionAttributes()->setMachSubmitHold(true);
                    }

                    $message .= 'Error Number: %3%s. Returned Message: %4$s';
                    $message = sprintf(
                        $message, $errorCount, $errorThreshold, $errorNumber, $errorMessage, $order->getIncrementId()
                    );

                    $order->addCommentToStatusHistory($message);
                    $order->getExtensionAttributes()
                        ->setMachSubmitted(true)
                        ->setMachSubmitErrorCount($errorCount);

                    $this->infoLog($message);
                } else {
                    $machOrderNumber = $response->OrderNumber;

                    $order->getExtensionAttributes()
                        ->setMachOrderNumber($machOrderNumber)
                        ->setMachSubmitted(true)
                        ->setMachSubmittedAt($this->dateTime->gmtDate());
                    $order->setExtOrderId($machOrderNumber);

                    if (!empty($response->ADD_ORDER_OUT->CustNumber)) {
                        $machCustomerNumber = $response->ADD_ORDER_OUT->CustNumber;

                        $this->setMachCustomerNumber->set($order->getCustomerEmail(), $machCustomerNumber);
                        $order->getExtensionAttributes()->setMachCustomerNumber($machCustomerNumber);
                        $order->setData('ext_customer_id', $machCustomerNumber);
                    } else {
                        $order->getExtensionAttributes()->setMachCustomerNumber(
                            $this->getMachCustomerNumber->get($order)
                        );
                        $order->setData('ext_customer_id', $this->getMachCustomerNumber->get($order));
                    }

                    $message = 'Order #%1$s was successfully submitted to MACH ERP.  MACH Order Number: %2$s';
                    $message = sprintf($message, $order->getIncrementId(), $machOrderNumber);

                    $order->setState(Order::STATE_PROCESSING, 'infulfillment', $message);
                    $order->addCommentToStatusHistory($message, 'infulfillment', false);

                    $this->infoLog($message);
                }

            }

            // [ZIP-1669] Order not being saved with repository or order
            // resource because we only want to perform a partial update of the
            // order's data, otherwise the "email_sent" attribute could
            // potentially be out of date (because the model was loaded before
            // the model in the attributes are saved in the
            // `sales_send_order_emails` job) and overwrite the value of that
            // attribute, resulting in duplicate order emails being sent.
            $this->orderAttributeResource->saveAttribute($order, ['status', 'ext_customer_id', 'ext_order_id']);
            foreach ($order->getStatusHistories() as $statusHistory) {
                $statusHistory->setParentId($order->getId());
                $statusHistory->setOrder($order);
                $this->orderStatusHistoryResource->save($statusHistory);
            }
            $this->machCustomerOrderAttributesSaveHandler->saveAttributes($order);
            $this->machOrderOrderAttributesSaveHandler->saveAttributes($order);

            $status = true;
        } catch (LocalizedException $e) {
            $order->addStatusToHistory(false, 'Error adding to MACH, message: ' . $e->getMessage());
            $this->orderRepository->save($order);
            $this->infoLog($e);
            throw $e;
        } catch (\Exception $e) {
            $this->criticalLog($e);
            throw $e;
        } finally {
            $message = __('Finished %1.  Result: %2', $action, ($status ? 'PASS' : 'FAIL'));
            $this->debugLog($message);
        }

        return true;
    }

    /**
     * @param OrderInterface $order
     *
     * Now we review the HSC value before send the Order, new conditions, to set Y or N, were added.
     * Also when checking the backorders element on each Order we need to make a live inventory(INV_INFO) call to Mach.
     *
     * @return string
     */
    protected function _getHscLastValue(OrderInterface $order): string
    {
        $isDropship  = $this->isOrderDropship->doesCartHaveDropships($order);
        $isBackOrder = $this->orderBackorders->doesOrderHaveBackOrders($order);
        $shippingMethod = $order->getShippingMethod();

        //international orders HSC = Y automatically
        if ($shippingMethod == Config::ATYPICAL_REGIONS_SHIPPING_METHOD_CODE) {
            $hsc = 'Y';
            return $hsc;
        } else {
            //US orders with dropships HSC = N automatically
            if ($isDropship || !$isBackOrder) {
                $hsc = 'N';
                return $hsc;
            }

            //If we are here then it is no dropship Order with backorders
            //US Post Office conditions
            if ($shippingMethod == Config::TABLE_SUREPOST_SHIPPING_METHOD) {
                $hsc = 'Y';
                return $hsc;
            } elseif (in_array($shippingMethod, Config::MACH_UPS_GROUND_SHIPPING_METHOD_CODE) ||
                      $shippingMethod == Config::MACH_DOWN_SHIPPING_METHOD_CODE) {

                //for Mach shipping methods we nee to analyze if the Order Subtotal Amount is less/greater than $75
                $baseSubTotal   = round($order->getBaseSubtotal(), 2);
                if ($baseSubTotal <= self::ORDER_SUBTOTAL_HSC_VALUE) {
                    $hsc = 'Y';
                    return $hsc;
                } else {
                    //finally we analyze what the customer selected on the HSC checkbox, if we are here it is because
                    //this is a US Order with no dropships and with backorders, also Subtotal Amount is greater
                    //than $75 and it is using a Mach ground shipping method(Up or Down).
                    if ($order->getExtensionAttributes()->getMachHsc()) {
                        $hsc = 'Y';
                        return $hsc;
                    }
                }
            }

            return 'N';
        }
    }

    /**
     * @param Item $orderItem
     *
     * @return array
     * @throws LocalizedException
     */
    protected function _buildAddOrderItem(Item $orderItem): array
    {
        return $this->buildOrderItem->build($orderItem);
    }

    /**
     * @param OrderInterface $order
     *
     * @return string
     */
    protected function _getPaymentTerms(OrderInterface $order): ?string
    {
        return $this->paymentTerms->get($order);
    }

    /**
     * @param OrderInterface|Order $order
     *
     * @return string
     */
    protected function _getMachShippingCode(OrderInterface $order): string
    {
        $shippingMethod = $order->getShippingMethod();

        if ($this->inStorePickupMethod->is((string) $shippingMethod)) {
            return 'STP';
        }

        $packageWeight  = $order->getWeight();
        $packageValue = $order->getBaseSubtotal();

        //if it is in the form mach_503, return 503;
        if (strpos($shippingMethod ?: '', 'mach_') === 0) {
            return substr($shippingMethod, 5);
        }

        //atypicalregions_atypicalregions
        //atypicalusregions_atypicalusregions
        if (strpos($shippingMethod ?: '', 'atypical') === 0) {
            return $this->machOrderConfig->getAtypicalShippingMethodCode();
        }

        //Carrier in use on MACH (and we know there are only 2)
        $carrier = $this->shippingConfig->getCarrierInUse();
        if ($shippingMethod == 'flatrate_flatrate') {

            if ($packageWeight <= 1 && $packageValue <= 200) {
                return $this->_calculateCodeToReturn($carrier, '508C', '209');
            } elseif ($packageWeight > 1 && $packageWeight <= 9 && $packageValue <= 200) {
                return $this->_calculateCodeToReturn($carrier, '508', '209');
            }

            return $carrier == Method::MACH_FEDEX_CARRIER_CODE ? 'FED01' : 'GND';
        }

        if ($shippingMethod == Config::TABLE_SUREPOST_SHIPPING_METHOD) {
            return $this->machOrderConfig->getMachTableRateSurePostCode()
                ?: Config::TABLE_SUREPOST_MACH_CODE;
        }

        if ($shippingMethod == Config::TABLE_ZIPFLATRATE_SHIPPING_METHOD) {

            if ($packageWeight <= 1 && $packageValue <= 200) {
                return $this->_calculateCodeToReturn($carrier, '508C', '209');
            } elseif ($packageWeight > 1 && $packageWeight <= 9 && $packageValue <= 200) {
                return $this->_calculateCodeToReturn($carrier, '508', '209');
            }

            return $this->machOrderConfig->getMachTableRateZipFlatRateCode($order->getStoreId())
                ?: Config::TABLE_ZIPFLATRATE_MACH_CODE;
        }

        if ($shippingMethod == 'freeshipping_freeshipping' || $order->getBaseShippingAmount() < 0.01) {

            if ($packageWeight <= 1 && $packageValue <= 200) {
                return $this->_calculateCodeToReturn($carrier, '508C', '209');
            } elseif ($packageWeight > 1 && $packageWeight <= 9 && $packageValue <= 200) {
                return $this->_calculateCodeToReturn($carrier, '508', '209');
            }

            return $carrier == Method::MACH_FEDEX_CARRIER_CODE ? 'FED01' : 'GND';
        }

        return $this->machOrderConfig->getMachFallbackShippingCode();
    }

    private function _calculateCodeToReturn($carrier, $possibleUPSCode, $possibleFedExCode, $defaultCodeToReturn = 'GND'): string
    {
        if ($carrier === Method::MACH_UPS_CARRIER_CODE) {
            return (string)$possibleUPSCode;
        }

        if ($carrier === Method::MACH_FEDEX_CARRIER_CODE) {
            return (string)$possibleFedExCode;
        }

        return (string)$defaultCodeToReturn;
    }

    /**
     * @param OrderAddressInterface $address
     *
     * @return string
     */
    protected function _getAddressDeliveryIndicator(OrderAddressInterface $address): string
    {
        return '';
    }

    /**
     * Return date in MM/YY format
     *
     * @param OrderInterface|Order $order
     *
     * @return string
     */
    protected function getPaywareFormattedExpirationDate(OrderInterface $order): ?string
    {
        $payment = $order->getPayment();
        if (!$payment->getCcExpMonth() || !$payment->getCcExpYear()) {
            return null;
        }

        return sprintf('%s/%s', $payment->getCcExpMonth(), substr($payment->getCcExpYear(), -2));
    }
}
