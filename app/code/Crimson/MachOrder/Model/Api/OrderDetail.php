<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 5:14 PM
 * @brief
 */

namespace Crimson\MachOrder\Model\Api;

use Crimson\MachBase\Model\Api\AbstractApi;
use Crimson\MachBase\Model\Api\ApiContext;
use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachOrder\Api\Data\MachOrderInterface;
use Crimson\MachOrder\Api\Data\MachOrderInterfaceFactory;
use Crimson\MachOrder\Model\Service\GetMachOrderNumber;
use Crimson\MachOrder\Model\Service\ScheduleOrderUpdate;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class OrderDetail
 * @package Crimson\MachOrder\Model\Api
 */
class OrderDetail extends AbstractApi
{
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var GetMachOrderNumber
     */
    protected $getMachOrderNumber;
    /**
     * @var HealthCheck
     */
    protected $healthCheck;
    /**
     * @var MachOrderInterfaceFactory
     */
    protected $machOrderInterfaceFactory;
    /**
     * @var ScheduleOrderUpdate
     */
    protected $scheduleOrderUpdate;

    public function __construct(
        ApiContext $apiContext,
        MachConfig $machConfig,
        Subscriber $subscriberResource,
        HealthCheck $healthCheck,
        MachOrderInterfaceFactory $machOrderInterfaceFactory,
        ScheduleOrderUpdate $scheduleOrderUpdate,
        GetMachOrderNumber $getMachOrderNumber,
        DataObjectHelper $dataObjectHelper,
    ) {
        parent::__construct($apiContext, $machConfig, $subscriberResource);
        $this->healthCheck                    = $healthCheck;
        $this->machOrderInterfaceFactory      = $machOrderInterfaceFactory;
        $this->scheduleOrderUpdate            = $scheduleOrderUpdate;
        $this->getMachOrderNumber             = $getMachOrderNumber;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param OrderInterface $order
     * @param string|null    $alternativeMachOrderNumber
     *
     * @return MachOrderInterface|null
     * @throws LocalizedException
     * @throws \Exception
     */
    public function get(OrderInterface $order, $alternativeMachOrderNumber = null): ?MachOrderInterface
    {
        if (!$this->healthCheck->isUp()) {
            $this->scheduleOrderUpdate->execute($order);

            return null;
        }

        $action = self::CALL_ORDER_DETAIL;
        $this->debugLog(__('Beginning %1 Call', $action));
        $status = false;
        $machOrder = $this->machOrderInterfaceFactory->create();

        $orderNumber = $alternativeMachOrderNumber;
        if (!$orderNumber) {
            $orderNumber = $this->getMachOrderNumber->get($order);
            $this->debugLog(__('Trying orderNumber from mach %1 %2', $orderNumber, $order->getIncrementId()));
        }

        if (!$orderNumber) {
            $this->debugLog(__('Still no order number from mach for %1', $order->getIncrementId()));

            return null;
        }

        try {
            $arguments = array(
                $this->_soapVar($this->getSecurityCode(), 'SecurityCode'),
                /*
                 * 1 -Order/Quote Number returns Detail Order/Quote Information
                 */
                $this->_soapVar(1, 'ActionCode'),
                $this->_soapVar($orderNumber, 'OrderNumberIn'),
            );

            $response = $this->makeRequest($action, $arguments);

            if (!isset($response->ERROR_OUT->ErrorNumber)) {
                throw new LocalizedException(
                    __('Invalid response received, unable to determine if customer info exists.')
                );
            } else {
                $errorNumber  = $response->ERROR_OUT->ErrorNumber;
                $errorMessage = $response->ERROR_OUT->ErrorMsg;
                if (!$this->_isSuccess($action, $errorNumber)) {
                    $message = 'Error occurred attempting to retrieve order info from MACH ERP.<br/>';
                    $message .= 'Error Number: %1. Returned Message: %2 <br/>';
                    $message .= 'Order Number: %3';
                    $message = __($message, $errorNumber, $errorMessage, $order->getIncrementId());

                    $this->infoLog($message);

                    throw new LocalizedException($message);
                } else {
                    $orderData = $response->ORDER_DETAIL_OUT;

                    $result = array(
                        'customer_number'     => $orderData->CustNumber,
                        'customer_name'       => $orderData->CustName,
                        'customer_street'     => array(
                            $orderData->CustAdd1,
                            $orderData->CustAdd2,
                        ),
                        'customer_city'       => $orderData->CustCity,
                        'customer_state'      => $orderData->CustState,
                        'customer_zip'        => $orderData->CustZip,
                        'customer_county'     => $orderData->CustCounty,
                        'customer_country'    => $orderData->CustCountry,
                        'customer_phone'      => $orderData->CustPhone,
                        'customer_fax'        => $orderData->CustFax,
                        'customer_email'      => $orderData->CustEmail,
                        'ship_name'           => $orderData->ShipName,
                        'ship_add1'           => $orderData->ShipAdd1,
                        'ship_add2'           => $orderData->ShipAdd2,
                        'ship_city'           => $orderData->ShipCity,
                        'ship_state'          => $orderData->ShipState,
                        'ship_zip'            => $orderData->ShipZip,
                        'ship_county'         => $orderData->ShipCounty,
                        'ship_country'        => $orderData->ShipCountry,
                        'ship_phone'          => $orderData->ShipPhone,
                        'sold_name'           => $orderData->SoldName,
                        'sold_add1'           => $orderData->SoldAdd1,
                        'sold_add2'           => $orderData->SoldAdd2,
                        'sold_city'           => $orderData->SoldCity,
                        'sold_state'          => $orderData->SoldState,
                        'sold_zip'            => $orderData->SoldZip,
                        'sold_county'         => $orderData->SoldCounty,
                        'sold_country'        => $orderData->SoldCountry,
                        'sold_phone'          => $orderData->SoldPhone,
                        'sold_fax'            => $orderData->SoldFax,
                        'sold_email'          => $orderData->SoldEmail,
                        'order_email'         => $orderData->OrderEmail,
                        'order_date'          => $orderData->OrderDate,
                        'order_time'          => $orderData->OrderTime,
                        'order_base'          => $orderData->OrderBase,
                        'order_due_date'      => $orderData->OrderDueDate,
                        'order_ship_date'     => $orderData->OrderShipDate,
                        'order_invoice_date'  => $orderData->OrderInvoiceDate,
                        'order_post_date'     => $orderData->OrderPostDate,
                        'order_bkr_date'      => $orderData->OrderBkrDate,
                        'order_merch_amt'     => $orderData->OrderMerchAmt,
                        'order_ship_amt'      => $orderData->OrderShipAmt,
                        'order_add_amt'       => 0,
                        'order_cod_amt'       => 0,
                        'order_other_amt'     => $orderData->OrderOtherAmt,
                        'order_tax_amt'       => $orderData->OrderTaxAmt,
                        'order_total_amt'     => $orderData->OrderTotalAmt,
                        'order_paid_amt'      => $orderData->OrderPaidAmt,
                        'order_terms'         => $orderData->OrderTerms,
                        'order_ship_method'   => $orderData->OrderShipMethod,
                        'order_ps'            => $orderData->OrderPS,
                        'order_hold_cc'       => $orderData->OrderHoldCC,
                        'order_hold_cr'       => $orderData->OrderHoldCR,
                        'order_hold_ck'       => $orderData->OrderHoldCK,
                        'order_hold_ck_amt'   => $orderData->OrderHoldCKAmt,
                        'order_hold_pay'      => $orderData->OrderHoldPay,
                        'order_hold_price'    => $orderData->OrderHoldPrice,
                        'order_hold_bkr'      => $orderData->OrderHoldBkr,
                        'order_hold_operator' => $orderData->OrderHoldOperator,
                        'order_hold_fraud'    => $orderData->OrderHoldFraud,
                        'order_alt_reference' => $orderData->OrderAltReference,
                        'order_keycode'       => $orderData->OrderKeycode,
                        'order_channel'       => $orderData->OrderChannel,
                        'order_type'          => $orderData->OrderType,
                        'order_hsc'           => $orderData->OrderHSC,
                        'order_attention'     => $orderData->OrderAttention,
                        'order_comments'      => $orderData->OrderComments,
                        'order_out1'          => $orderData->OrderOut1,
                        'order_out2'          => $orderData->OrderOut2,
                        'order_out3'          => $orderData->OrderOut3,
                        'order_out4'          => $orderData->OrderOut4,
                        'order_out5'          => $orderData->OrderOut5,
                        'order_items'         => [],
                        //todo: build package info
                        'package_info'        => [],
                    );

                    $result['order_add_amt'] = (isset($orderData->OrderAddAmt) ? (float) $orderData->OrderAddAmt : 0);
                    $result['order_add_amt'] += (isset($orderData->OrderAdd1Amt) ? (float) $orderData->OrderAdd1Amt : 0);
                    $result['order_add_amt'] += (isset($orderData->OrderAdd2Amt) ? (float) $orderData->OrderAdd2Amt : 0);

                    $packageInfo = $orderData->PackageInfo;
                    if (!is_array($packageInfo)) {
                        $packageInfo = array($packageInfo);
                    }

                    foreach ($packageInfo as $packageData) {
                        $package = [
                            'package_id'      => $packageData->PackageId,
                            'tracking_number' => $packageData->PackageTrackNbr,
                            'package_link'    => $packageData->PackageLink,
                            'box_details'     => [],
                        ];

                        //build box items.
                        if (isset($packageData->BoxDetail)) {
                            $boxDetails = $packageData->BoxDetail;
                            if (!is_array($boxDetails)) {
                                $boxDetails = array($boxDetails);
                            }
                            foreach ($boxDetails as $box) {
                                $package['box_details'][] = [
                                    'sku' => $box->PackageItems,
                                    'qty' => $box->PackageQtys,
                                ];
                            }
                        }

                        $result['package_info'][$packageData->PackageId] = $package;
                    }

                    $orderItems = $orderData->OrderItems;
                    if (!is_array($orderItems)) {
                        $orderItems = array($orderItems);
                    }
                    foreach ($orderItems as $orderItem) {
                        $result['order_items'][] = [
                            'sku'             => $orderItem->ItemNumber,
                            'qty_ordered'     => $orderItem->ItemQtyOrd,
                            'qty_shipped'     => $orderItem->ItemQtyShip,
                            'qty_backordered' => $orderItem->ItemQtyBkr,
                        ];
                    }

                    $this->debugLog($result);
                    $this->dataObjectHelper->populateWithArray($machOrder, $result, MachOrderInterface::class);
                    $status = true;

                    return $machOrder;
                }
            }
        } catch (LocalizedException $e) {
            $this->infoLog($e);
            throw $e;
        } catch (\Exception $e) {
            $this->criticalLog($e);
            throw $e;
        } finally {
            $this->debugLog('Finished ' . $action . '.  Result: ' . ($status ? 'PASS' : 'FAIL'));
            $this->debugLog('-------------------------------------------');
        }

        return null;
    }
}
