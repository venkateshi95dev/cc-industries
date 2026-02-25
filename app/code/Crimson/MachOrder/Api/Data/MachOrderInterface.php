<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 5:16 PM
 * @brief
 */

namespace Crimson\MachOrder\Api\Data;

/**
 * Interface MachOrderInterface
 * @package Crimson\MachOrder\Api\Data
 */
interface MachOrderInterface
{
    const CUSTOMER_NUMBER = 'customer_number';
    const CUSTOMER_NAME = 'customer_name';
    const CUSTOMER_STREET = 'customer_street';
    const CUSTOMER_CITY = 'customer_city';
    const CUSTOMER_STATE = 'customer_state';
    const CUSTOMER_ZIP = 'customer_zip';
    const CUSTOMER_COUNTY = 'customer_county';
    const CUSTOMER_COUNTRY = 'customer_country';
    const CUSTOMER_PHONE = 'customer_phone';
    const CUSTOMER_FAX = 'customer_fax';
    const CUSTOMER_EMAIL = 'customer_email';
    const SHIP_NAME = 'ship_name';
    const SHIP_ADD1 = 'ship_add1';
    const SHIP_ADD2 = 'ship_add2';
    const SHIP_CITY = 'ship_city';
    const SHIP_STATE = 'ship_state';
    const SHIP_ZIP = 'ship_zip';
    const SHIP_COUNTY = 'ship_county';
    const SHIP_COUNTRY = 'ship_country';
    const SHIP_PHONE = 'ship_phone';
    const SOLD_NAME = 'sold_name';
    const SOLD_ADD1 = 'sold_add1';
    const SOLD_ADD2 = 'sold_add2';
    const SOLD_CITY = 'sold_city';
    const SOLD_STATE = 'sold_state';
    const SOLD_ZIP = 'sold_zip';
    const SOLD_COUNTY = 'sold_county';
    const SOLD_COUNTRY = 'sold_country';
    const SOLD_PHONE = 'sold_phone';
    const SOLD_FAX = 'sold_fax';
    const SOLD_EMAIL = 'sold_email';
    const ORDER_EMAIL = 'order_email';
    const ORDER_DATE = 'order_date';
    const ORDER_TIME = 'order_time';
    const ORDER_BASE = 'order_base';
    const ORDER_DUE_DATE = 'order_due_date';
    const ORDER_SHIP_DATE = 'order_ship_date';
    const ORDER_INVOICE_DATE = 'order_invoice_date';
    const ORDER_POST_DATE = 'order_post_date';
    const ORDER_BKR_DATE = 'order_bkr_date';
    const ORDER_MERCH_AMT = 'order_merch_amt';
    const ORDER_SHIP_AMT = 'order_ship_amt';
    const ORDER_ADD_AMT = 'order_add_amt';
    const ORDER_COD_AMT = 'order_cod_amt';
    const ORDER_OTHER_AMT = 'order_other_amt';
    const ORDER_TAX_AMT = 'order_tax_amt';
    const ORDER_TOTAL_AMT = 'order_total_amt';
    const ORDER_PAID_AMT = 'order_paid_amt';
    const ORDER_TERMS = 'order_terms';
    const ORDER_SHIP_METHOD = 'order_ship_method';
    const ORDER_PS = 'order_ps';
    const ORDER_HOLD_CC = 'order_hold_cc';
    const ORDER_HOLD_CR = 'order_hold_cr';
    const ORDER_HOLD_CK = 'order_hold_ck';
    const ORDER_HOLD_CK_AMT = 'order_hold_ck_amt';
    const ORDER_HOLD_PAY = 'order_hold_pay';
    const ORDER_HOLD_PRICE = 'order_hold_price';
    const ORDER_HOLD_BKR = 'order_hold_bkr';
    const ORDER_HOLD_OPERATOR = 'order_hold_operator';
    const ORDER_HOLD_FRAUD = 'order_hold_fraud';
    const ORDER_ALT_REFERENCE = 'order_alt_reference';
    const ORDER_KEYCODE = 'order_keycode';
    const ORDER_CHANNEL = 'order_channel';
    const ORDER_TYPE = 'order_type';
    const ORDER_HSC = 'order_hsc';
    const ORDER_ATTENTION = 'order_attention';
    const ORDER_COMMENTS = 'order_comments';
    const ORDER_OUT1 = 'order_out1';
    const ORDER_OUT2 = 'order_out2';
    const ORDER_OUT3 = 'order_out3';
    const ORDER_OUT4 = 'order_out4';
    const ORDER_OUT5 = 'order_out5';
    const ORDER_ITEMS = 'order_items';
    const PACKAGE_INFO = 'package_info';

    /**
     * @return string|null
     */
    public function getCustomerNumber(): ?string;

    /**
     * @param $customerNumber
     *
     * @return MachOrderInterface
     */
    public function setCustomerNumber($customerNumber): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getCustomerName(): ?string;

    /**
     * @param $customerName
     *
     * @return MachOrderInterface
     */
    public function setCustomerName($customerName): MachOrderInterface;

    /**
     * @return string[]
     */
    public function getCustomerStreet(): array;

    /**
     * @param string[]|null $customerStreet
     *
     * @return MachOrderInterface
     */
    public function setCustomerStreet(?array $customerStreet): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getCustomerCity(): ?string;

    /**
     * @param $customerCity
     *
     * @return MachOrderInterface
     */
    public function setCustomerCity($customerCity): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getCustomerState(): ?string;

    /**
     * @param $customerState
     *
     * @return MachOrderInterface
     */
    public function setCustomerState($customerState): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getCustomerZip(): ?string;

    /**
     * @param $customerZip
     *
     * @return MachOrderInterface
     */
    public function setCustomerZip($customerZip): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getCustomerCounty(): ?string;

    /**
     * @param $customerCounty
     *
     * @return MachOrderInterface
     */
    public function setCustomerCounty($customerCounty): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getCustomerCountry(): ?string;

    /**
     * @param $customerCountry
     *
     * @return MachOrderInterface
     */
    public function setCustomerCountry($customerCountry): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getCustomerPhone(): ?string;

    /**
     * @param $customerPhone
     *
     * @return MachOrderInterface
     */
    public function setCustomerPhone($customerPhone): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getCustomerFax(): ?string;

    /**
     * @param $customerFax
     *
     * @return MachOrderInterface
     */
    public function setCustomerFax($customerFax): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getCustomerEmail(): ?string;

    /**
     * @param $customerEmail
     *
     * @return MachOrderInterface
     */
    public function setCustomerEmail($customerEmail): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getShipName(): ?string;

    /**
     * @param $shipName
     *
     * @return MachOrderInterface
     */
    public function setShipName($shipName): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getShipAdd1(): ?string;

    /**
     * @param $shipAdd1
     *
     * @return MachOrderInterface
     */
    public function setShipAdd1($shipAdd1): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getShipAdd2(): ?string;

    /**
     * @param $shipAdd2
     *
     * @return MachOrderInterface
     */
    public function setShipAdd2($shipAdd2): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getShipCity(): ?string;

    /**
     * @param $shipCity
     *
     * @return MachOrderInterface
     */
    public function setShipCity($shipCity): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getShipState(): ?string;

    /**
     * @param $shipState
     *
     * @return MachOrderInterface
     */
    public function setShipState($shipState): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getShipZip(): ?string;

    /**
     * @param $shipZip
     *
     * @return MachOrderInterface
     */
    public function setShipZip($shipZip): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getShipCounty(): ?string;

    /**
     * @param $shipCounty
     *
     * @return MachOrderInterface
     */
    public function setShipCounty($shipCounty): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getShipCountry(): ?string;

    /**
     * @param $shipCountry
     *
     * @return MachOrderInterface
     */
    public function setShipCountry($shipCountry): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getShipPhone(): ?string;

    /**
     * @param $shipPhone
     *
     * @return MachOrderInterface
     */
    public function setShipPhone($shipPhone): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getSoldName(): ?string;

    /**
     * @param $soldName
     *
     * @return MachOrderInterface
     */
    public function setSoldName($soldName): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getSoldAdd1(): ?string;

    /**
     * @param $soldAdd1
     *
     * @return MachOrderInterface
     */
    public function setSoldAdd1($soldAdd1): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getSoldAdd2(): ?string;

    /**
     * @param $soldAdd2
     *
     * @return MachOrderInterface
     */
    public function setSoldAdd2($soldAdd2): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getSoldCity(): ?string;

    /**
     * @param $soldCity
     *
     * @return MachOrderInterface
     */
    public function setSoldCity($soldCity): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getSoldState(): ?string;

    /**
     * @param $soldState
     *
     * @return MachOrderInterface
     */
    public function setSoldState($soldState): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getSoldZip(): ?string;

    /**
     * @param $soldZip
     *
     * @return MachOrderInterface
     */
    public function setSoldZip($soldZip): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getSoldCounty(): ?string;

    /**
     * @param $soldCounty
     *
     * @return MachOrderInterface
     */
    public function setSoldCounty($soldCounty): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getSoldCountry(): ?string;

    /**
     * @param $soldCountry
     *
     * @return MachOrderInterface
     */
    public function setSoldCountry($soldCountry): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getSoldPhone(): ?string;

    /**
     * @param $soldPhone
     *
     * @return MachOrderInterface
     */
    public function setSoldPhone($soldPhone): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getSoldFax(): ?string;

    /**
     * @param $soldFax
     *
     * @return MachOrderInterface
     */
    public function setSoldFax($soldFax): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getSoldEmail(): ?string;

    /**
     * @param $soldEmail
     *
     * @return MachOrderInterface
     */
    public function setSoldEmail($soldEmail): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderEmail(): ?string;

    /**
     * @param $orderEmail
     *
     * @return MachOrderInterface
     */
    public function setOrderEmail($orderEmail): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderDate(): ?string;

    /**
     * @param $orderDate
     *
     * @return MachOrderInterface
     */
    public function setOrderDate($orderDate): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderTime(): ?string;

    /**
     * @param $orderTime
     *
     * @return MachOrderInterface
     */
    public function setOrderTime($orderTime): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderBase(): ?string;

    /**
     * @param $orderBase
     *
     * @return MachOrderInterface
     */
    public function setOrderBase($orderBase): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderDueDate(): ?string;

    /**
     * @param $orderDueDate
     *
     * @return MachOrderInterface
     */
    public function setOrderDueDate($orderDueDate): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderShipDate(): ?string;

    /**
     * @param $orderShipDate
     *
     * @return MachOrderInterface
     */
    public function setOrderShipDate($orderShipDate): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderInvoiceDate(): ?string;

    /**
     * @param $orderInvoiceDate
     *
     * @return MachOrderInterface
     */
    public function setOrderInvoiceDate($orderInvoiceDate): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderPostDate(): ?string;

    /**
     * @param $orderPostDate
     *
     * @return MachOrderInterface
     */
    public function setOrderPostDate($orderPostDate): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderBkrDate(): ?string;

    /**
     * @param $orderBkrDate
     *
     * @return MachOrderInterface
     */
    public function setOrderBkrDate($orderBkrDate): MachOrderInterface;

    /**
     * @return float|null
     */
    public function getOrderMerchAmt(): ?float;

    /**
     * @param $orderMerchAmt
     *
     * @return MachOrderInterface
     */
    public function setOrderMerchAmt($orderMerchAmt): MachOrderInterface;

    /**
     * @return float|null
     */
    public function getOrderShipAmt(): ?float;

    /**
     * @param $orderShipAmt
     *
     * @return MachOrderInterface
     */
    public function setOrderShipAmt($orderShipAmt): MachOrderInterface;

    /**
     * @return float|null
     */
    public function getOrderAddAmt(): ?float;

    /**
     * @param $orderAddAmt
     *
     * @return MachOrderInterface
     */
    public function setOrderAddAmt($orderAddAmt): MachOrderInterface;

    /**
     * @return float|null
     */
    public function getOrderCodAmt(): ?float;

    /**
     * @param $orderCodAmt
     *
     * @return MachOrderInterface
     */
    public function setOrderCodAmt($orderCodAmt): MachOrderInterface;

    /**
     * @return float|null
     */
    public function getOrderOtherAmt(): ?float;

    /**
     * @param $orderOtherAmt
     *
     * @return MachOrderInterface
     */
    public function setOrderOtherAmt($orderOtherAmt): MachOrderInterface;

    /**
     * @return float|null
     */
    public function getOrderTaxAmt(): ?float;

    /**
     * @param $orderTaxAmt
     *
     * @return MachOrderInterface
     */
    public function setOrderTaxAmt($orderTaxAmt): MachOrderInterface;

    /**
     * @return float|null
     */
    public function getOrderTotalAmt(): ?float;

    /**
     * @param $orderTotalAmt
     *
     * @return MachOrderInterface
     */
    public function setOrderTotalAmt($orderTotalAmt): MachOrderInterface;

    /**
     * @return float|null
     */
    public function getOrderPaidAmt(): ?float;

    /**
     * @param $orderPaidAmt
     *
     * @return MachOrderInterface
     */
    public function setOrderPaidAmt($orderPaidAmt): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderTerms(): ?string;

    /**
     * @param $orderTerms
     *
     * @return MachOrderInterface
     */
    public function setOrderTerms($orderTerms): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderShipMethod(): ?string;

    /**
     * @param $orderShipMethod
     *
     * @return MachOrderInterface
     */
    public function setOrderShipMethod($orderShipMethod): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderPs(): ?string;

    /**
     * @param $orderPs
     *
     * @return MachOrderInterface
     */
    public function setOrderPs($orderPs): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderHoldCc(): ?string;

    /**
     * @param $orderHoldCc
     *
     * @return MachOrderInterface
     */
    public function setOrderHoldCc($orderHoldCc): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderHoldCr(): ?string;

    /**
     * @param $orderHoldCr
     *
     * @return MachOrderInterface
     */
    public function setOrderHoldCr($orderHoldCr): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderHoldCk(): ?string;

    /**
     * @param $orderHoldCk
     *
     * @return MachOrderInterface
     */
    public function setOrderHoldCk($orderHoldCk): MachOrderInterface;

    /**
     * @return float|null
     */
    public function getOrderHoldCkAmt(): ?float;

    /**
     * @param $orderHoldCkAmt
     *
     * @return MachOrderInterface
     */
    public function setOrderHoldCkAmt($orderHoldCkAmt): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderHoldPay(): ?string;

    /**
     * @param $orderHoldPay
     *
     * @return MachOrderInterface
     */
    public function setOrderHoldPay($orderHoldPay): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderHoldPrice(): ?string;

    /**
     * @param $orderHoldPrice
     *
     * @return MachOrderInterface
     */
    public function setOrderHoldPrice($orderHoldPrice): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderHoldBkr(): ?string;

    /**
     * @param $orderHoldBkr
     *
     * @return MachOrderInterface
     */
    public function setOrderHoldBkr($orderHoldBkr): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderHoldOperator(): ?string;

    /**
     * @param $orderHoldOperator
     *
     * @return MachOrderInterface
     */
    public function setOrderHoldOperator($orderHoldOperator): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderHoldFraud(): ?string;

    /**
     * @param $orderHoldFraud
     *
     * @return MachOrderInterface
     */
    public function setOrderHoldFraud($orderHoldFraud): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderAltReference(): ?string;

    /**
     * @param $orderAltReference
     *
     * @return MachOrderInterface
     */
    public function setOrderAltReference($orderAltReference): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderKeycode(): ?string;

    /**
     * @param $orderKeycode
     *
     * @return MachOrderInterface
     */
    public function setOrderKeycode($orderKeycode): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderChannel(): ?string;

    /**
     * @param $orderChannel
     *
     * @return MachOrderInterface
     */
    public function setOrderChannel($orderChannel): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderType(): ?string;

    /**
     * @param $orderType
     *
     * @return MachOrderInterface
     */
    public function setOrderType($orderType): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderHsc(): ?string;

    /**
     * @param $orderHsc
     *
     * @return MachOrderInterface
     */
    public function setOrderHsc($orderHsc): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderAttention(): ?string;

    /**
     * @param $orderAttention
     *
     * @return MachOrderInterface
     */
    public function setOrderAttention($orderAttention): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderComments(): ?string;

    /**
     * @param $orderComments
     *
     * @return MachOrderInterface
     */
    public function setOrderComments($orderComments): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderOut1(): ?string;

    /**
     * @param $orderOut1
     *
     * @return MachOrderInterface
     */
    public function setOrderOut1($orderOut1): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderOut2(): ?string;

    /**
     * @param $orderOut2
     *
     * @return MachOrderInterface
     */
    public function setOrderOut2($orderOut2): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderOut3(): ?string;

    /**
     * @param $orderOut3
     *
     * @return MachOrderInterface
     */
    public function setOrderOut3($orderOut3): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderOut4(): ?string;

    /**
     * @param $orderOut4
     *
     * @return MachOrderInterface
     */
    public function setOrderOut4($orderOut4): MachOrderInterface;

    /**
     * @return string|null
     */
    public function getOrderOut5(): ?string;

    /**
     * @param $orderOut5
     *
     * @return MachOrderInterface
     */
    public function setOrderOut5($orderOut5): MachOrderInterface;

    /**
     * @return \Crimson\MachOrder\Api\Data\MachOrderItemInterface[]
     */
    public function getOrderItems(): array;

    /**
     * @param \Crimson\MachOrder\Api\Data\MachOrderItemInterface[] $orderItems
     *
     * @return MachOrderInterface
     */
    public function setOrderItems($orderItems): MachOrderInterface;

    /**
     * @return \Crimson\MachOrder\Api\Data\MachPackageInterface[]
     */
    public function getPackageInfo(): array;

    /**
     * @param \Crimson\MachOrder\Api\Data\MachPackageInterface[] $packageInfo
     *
     * @return MachOrderInterface
     */
    public function setPackageInfo($packageInfo): MachOrderInterface;
}
