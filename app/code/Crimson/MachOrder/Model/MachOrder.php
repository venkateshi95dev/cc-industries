<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 5:21 PM
 * @brief
 */

namespace Crimson\MachOrder\Model;

use Crimson\MachOrder\Api\Data\MachOrderInterface;
use Magento\Framework\DataObject;

/**
 * Class MachOrder
 * @package Crimson\MachOrder\Model
 */
class MachOrder extends DataObject implements MachOrderInterface
{
    public function getCustomerNumber(): ?string
    {
        return $this->_getData(self::CUSTOMER_NUMBER);
    }

    public function setCustomerNumber($customerNumber): MachOrderInterface
    {
        return $this->setData(self::CUSTOMER_NUMBER, $customerNumber);
    }

    public function getCustomerName(): ?string
    {
        return $this->_getData(self::CUSTOMER_NAME);
    }

    public function setCustomerName($customerName): MachOrderInterface
    {
        return $this->setData(self::CUSTOMER_NAME, $customerName);
    }

    public function getCustomerStreet(): array
    {
        return $this->_getData(self::CUSTOMER_STREET) ?: [];
    }

    public function setCustomerStreet(?array $customerStreet): MachOrderInterface
    {
        return $this->setData(self::CUSTOMER_STREET, $customerStreet);
    }

    public function getCustomerCity(): ?string
    {
        return $this->_getData(self::CUSTOMER_CITY);
    }

    public function setCustomerCity($customerCity): MachOrderInterface
    {
        return $this->setData(self::CUSTOMER_CITY, $customerCity);
    }

    public function getCustomerState(): ?string
    {
        return $this->_getData(self::CUSTOMER_STATE);
    }

    public function setCustomerState($customerState): MachOrderInterface
    {
        return $this->setData(self::CUSTOMER_STATE, $customerState);
    }

    public function getCustomerZip(): ?string
    {
        return $this->_getData(self::CUSTOMER_ZIP);
    }

    public function setCustomerZip($customerZip): MachOrderInterface
    {
        return $this->setData(self::CUSTOMER_ZIP, $customerZip);
    }

    public function getCustomerCounty(): ?string
    {
        return $this->_getData(self::CUSTOMER_COUNTY);
    }

    public function setCustomerCounty($customerCounty): MachOrderInterface
    {
        return $this->setData(self::CUSTOMER_COUNTY, $customerCounty);
    }

    public function getCustomerCountry(): ?string
    {
        return $this->_getData(self::CUSTOMER_COUNTRY);
    }

    public function setCustomerCountry($customerCountry): MachOrderInterface
    {
        return $this->setData(self::CUSTOMER_COUNTRY, $customerCountry);
    }

    public function getCustomerPhone(): ?string
    {
        return $this->_getData(self::CUSTOMER_PHONE);
    }

    public function setCustomerPhone($customerPhone): MachOrderInterface
    {
        return $this->setData(self::CUSTOMER_PHONE, $customerPhone);
    }

    public function getCustomerFax(): ?string
    {
        return $this->_getData(self::CUSTOMER_FAX);
    }

    public function setCustomerFax($customerFax): MachOrderInterface
    {
        return $this->setData(self::CUSTOMER_FAX, $customerFax);
    }

    public function getCustomerEmail(): ?string
    {
        return $this->_getData(self::CUSTOMER_EMAIL);
    }

    public function setCustomerEmail($customerEmail): MachOrderInterface
    {
        return $this->setData(self::CUSTOMER_EMAIL, $customerEmail);
    }

    public function getShipName(): ?string
    {
        return $this->_getData(self::SHIP_NAME);
    }

    public function setShipName($shipName): MachOrderInterface
    {
        return $this->setData(self::SHIP_NAME, $shipName);
    }

    public function getShipAdd1(): ?string
    {
        return $this->_getData(self::SHIP_ADD1);
    }

    public function setShipAdd1($shipAdd1): MachOrderInterface
    {
        return $this->setData(self::SHIP_ADD1, $shipAdd1);
    }

    public function getShipAdd2(): ?string
    {
        return $this->_getData(self::SHIP_ADD2);
    }

    public function setShipAdd2($shipAdd2): MachOrderInterface
    {
        return $this->setData(self::SHIP_ADD2, $shipAdd2);
    }

    public function getShipCity(): ?string
    {
        return $this->_getData(self::SHIP_CITY);
    }

    public function setShipCity($shipCity): MachOrderInterface
    {
        return $this->setData(self::SHIP_CITY, $shipCity);
    }

    public function getShipState(): ?string
    {
        return $this->_getData(self::SHIP_STATE);
    }

    public function setShipState($shipState): MachOrderInterface
    {
        return $this->setData(self::SHIP_STATE, $shipState);
    }

    public function getShipZip(): ?string
    {
        return $this->_getData(self::SHIP_ZIP);
    }

    public function setShipZip($shipZip): MachOrderInterface
    {
        return $this->setData(self::SHIP_ZIP, $shipZip);
    }

    public function getShipCounty(): ?string
    {
        return $this->_getData(self::SHIP_COUNTY);
    }

    public function setShipCounty($shipCounty): MachOrderInterface
    {
        return $this->setData(self::SHIP_COUNTY, $shipCounty);
    }

    public function getShipCountry(): ?string
    {
        return $this->_getData(self::SHIP_COUNTRY);
    }

    public function setShipCountry($shipCountry): MachOrderInterface
    {
        return $this->setData(self::SHIP_COUNTRY, $shipCountry);
    }

    public function getShipPhone(): ?string
    {
        return $this->_getData(self::SHIP_PHONE);
    }

    public function setShipPhone($shipPhone): MachOrderInterface
    {
        return $this->setData(self::SHIP_PHONE, $shipPhone);
    }

    public function getSoldName(): ?string
    {
        return $this->_getData(self::SOLD_NAME);
    }

    public function setSoldName($soldName): MachOrderInterface
    {
        return $this->setData(self::SOLD_NAME, $soldName);
    }

    public function getSoldAdd1(): ?string
    {
        return $this->_getData(self::SOLD_ADD1);
    }

    public function setSoldAdd1($soldAdd1): MachOrderInterface
    {
        return $this->setData(self::SOLD_ADD1, $soldAdd1);
    }

    public function getSoldAdd2(): ?string
    {
        return $this->_getData(self::SOLD_ADD2);
    }

    public function setSoldAdd2($soldAdd2): MachOrderInterface
    {
        return $this->setData(self::SOLD_ADD2, $soldAdd2);
    }

    public function getSoldCity(): ?string
    {
        return $this->_getData(self::SOLD_CITY);
    }

    public function setSoldCity($soldCity): MachOrderInterface
    {
        return $this->setData(self::SOLD_CITY, $soldCity);
    }

    public function getSoldState(): ?string
    {
        return $this->_getData(self::SOLD_STATE);
    }

    public function setSoldState($soldState): MachOrderInterface
    {
        return $this->setData(self::SOLD_STATE, $soldState);
    }

    public function getSoldZip(): ?string
    {
        return $this->_getData(self::SOLD_ZIP);
    }

    public function setSoldZip($soldZip): MachOrderInterface
    {
        return $this->setData(self::SOLD_ZIP, $soldZip);
    }

    public function getSoldCounty(): ?string
    {
        return $this->_getData(self::SOLD_COUNTY);
    }

    public function setSoldCounty($soldCounty): MachOrderInterface
    {
        return $this->setData(self::SOLD_COUNTY, $soldCounty);
    }

    public function getSoldCountry(): ?string
    {
        return $this->_getData(self::SOLD_COUNTRY);
    }

    public function setSoldCountry($soldCountry): MachOrderInterface
    {
        return $this->setData(self::SOLD_COUNTRY, $soldCountry);
    }

    public function getSoldPhone(): ?string
    {
        return $this->_getData(self::SOLD_PHONE);
    }

    public function setSoldPhone($soldPhone): MachOrderInterface
    {
        return $this->setData(self::SOLD_PHONE, $soldPhone);
    }

    public function getSoldFax(): ?string
    {
        return $this->_getData(self::SOLD_FAX);
    }

    public function setSoldFax($soldFax): MachOrderInterface
    {
        return $this->setData(self::SOLD_FAX, $soldFax);
    }

    public function getSoldEmail(): ?string
    {
        return $this->_getData(self::SOLD_EMAIL);
    }

    public function setSoldEmail($soldEmail): MachOrderInterface
    {
        return $this->setData(self::SOLD_EMAIL, $soldEmail);
    }

    public function getOrderEmail(): ?string
    {
        return $this->_getData(self::ORDER_EMAIL);
    }

    public function setOrderEmail($orderEmail): MachOrderInterface
    {
        return $this->setData(self::ORDER_EMAIL, $orderEmail);
    }

    public function getOrderDate(): ?string
    {
        return $this->_getData(self::ORDER_DATE);
    }

    public function setOrderDate($orderDate): MachOrderInterface
    {
        return $this->setData(self::ORDER_DATE, $orderDate);
    }

    public function getOrderTime(): ?string
    {
        return $this->_getData(self::ORDER_TIME);
    }

    public function setOrderTime($orderTime): MachOrderInterface
    {
        return $this->setData(self::ORDER_TIME, $orderTime);
    }

    public function getOrderBase(): ?string
    {
        return $this->_getData(self::ORDER_BASE);
    }

    public function setOrderBase($orderBase): MachOrderInterface
    {
        return $this->setData(self::ORDER_BASE, $orderBase);
    }

    public function getOrderDueDate(): ?string
    {
        return $this->_getData(self::ORDER_DUE_DATE);
    }

    public function setOrderDueDate($orderDueDate): MachOrderInterface
    {
        return $this->setData(self::ORDER_DUE_DATE, $orderDueDate);
    }

    public function getOrderShipDate(): ?string
    {
        return $this->_getData(self::ORDER_SHIP_DATE);
    }

    public function setOrderShipDate($orderShipDate): MachOrderInterface
    {
        return $this->setData(self::ORDER_SHIP_DATE, $orderShipDate);
    }

    public function getOrderInvoiceDate(): ?string
    {
        return $this->_getData(self::ORDER_INVOICE_DATE);
    }

    public function setOrderInvoiceDate($orderInvoiceDate): MachOrderInterface
    {
        return $this->setData(self::ORDER_INVOICE_DATE, $orderInvoiceDate);
    }

    public function getOrderPostDate(): ?string
    {
        return $this->_getData(self::ORDER_POST_DATE);
    }

    public function setOrderPostDate($orderPostDate): MachOrderInterface
    {
        return $this->setData(self::ORDER_POST_DATE, $orderPostDate);
    }

    public function getOrderBkrDate(): ?string
    {
        return $this->_getData(self::ORDER_BKR_DATE);
    }

    public function setOrderBkrDate($orderBkrDate): MachOrderInterface
    {
        return $this->setData(self::ORDER_BKR_DATE, $orderBkrDate);
    }

    public function getOrderMerchAmt(): ?float
    {
        return $this->_getData(self::ORDER_MERCH_AMT) ? (float) $this->_getData(self::ORDER_MERCH_AMT) : null;
    }

    public function setOrderMerchAmt($orderMerchAmt): MachOrderInterface
    {
        return $this->setData(self::ORDER_MERCH_AMT, $orderMerchAmt);
    }

    public function getOrderShipAmt(): ?float
    {
        return $this->_getData(self::ORDER_SHIP_AMT) ? (float) $this->_getData(self::ORDER_SHIP_AMT) : null;
    }

    public function setOrderShipAmt($orderShipAmt): MachOrderInterface
    {
        return $this->setData(self::ORDER_SHIP_AMT, $orderShipAmt);
    }

    public function getOrderAddAmt(): ?float
    {
        return $this->_getData(self::ORDER_ADD_AMT) ? (float) $this->_getData(self::ORDER_ADD_AMT) : null;
    }

    public function setOrderAddAmt($orderAddAmt): MachOrderInterface
    {
        return $this->setData(self::ORDER_ADD_AMT, $orderAddAmt);
    }

    public function getOrderCodAmt(): ?float
    {
        return $this->_getData(self::ORDER_COD_AMT) ? (float) $this->_getData(self::ORDER_COD_AMT) : null;
    }

    public function setOrderCodAmt($orderCodAmt): MachOrderInterface
    {
        return $this->setData(self::ORDER_COD_AMT, $orderCodAmt);
    }

    public function getOrderOtherAmt(): ?float
    {
        return $this->_getData(self::ORDER_OTHER_AMT) ? (float) $this->_getData(self::ORDER_OTHER_AMT) : null;
    }

    public function setOrderOtherAmt($orderOtherAmt): MachOrderInterface
    {
        return $this->setData(self::ORDER_OTHER_AMT, $orderOtherAmt);
    }

    public function getOrderTaxAmt(): ?float
    {
        return $this->_getData(self::ORDER_TAX_AMT) ? (float) $this->_getData(self::ORDER_TAX_AMT) : null;
    }

    public function setOrderTaxAmt($orderTaxAmt): MachOrderInterface
    {
        return $this->setData(self::ORDER_TAX_AMT, $orderTaxAmt);
    }

    public function getOrderTotalAmt(): ?float
    {
        return $this->_getData(self::ORDER_TOTAL_AMT) ? (float) $this->_getData(self::ORDER_TOTAL_AMT) : null;
    }

    public function setOrderTotalAmt($orderTotalAmt): MachOrderInterface
    {
        return $this->setData(self::ORDER_TOTAL_AMT, $orderTotalAmt);
    }

    public function getOrderPaidAmt(): ?float
    {
        return $this->_getData(self::ORDER_PAID_AMT) ? (float) $this->_getData(self::ORDER_PAID_AMT) : null;
    }

    public function setOrderPaidAmt($orderPaidAmt): MachOrderInterface
    {
        return $this->setData(self::ORDER_PAID_AMT, $orderPaidAmt);
    }

    public function getOrderTerms(): ?string
    {
        return $this->_getData(self::ORDER_TERMS);
    }

    public function setOrderTerms($orderTerms): MachOrderInterface
    {
        return $this->setData(self::ORDER_TERMS, $orderTerms);
    }

    public function getOrderShipMethod(): ?string
    {
        return $this->_getData(self::ORDER_SHIP_METHOD);
    }

    public function setOrderShipMethod($orderShipMethod): MachOrderInterface
    {
        return $this->setData(self::ORDER_SHIP_METHOD, $orderShipMethod);
    }

    public function getOrderPs(): ?string
    {
        return $this->_getData(self::ORDER_PS);
    }

    public function setOrderPs($orderPs): MachOrderInterface
    {
        return $this->setData(self::ORDER_PS, $orderPs);
    }

    public function getOrderHoldCc(): ?string
    {
        return $this->_getData(self::ORDER_HOLD_CC);
    }

    public function setOrderHoldCc($orderHoldCc): MachOrderInterface
    {
        return $this->setData(self::ORDER_HOLD_CC, $orderHoldCc);
    }

    public function getOrderHoldCr(): ?string
    {
        return $this->_getData(self::ORDER_HOLD_CR);
    }

    public function setOrderHoldCr($orderHoldCr): MachOrderInterface
    {
        return $this->setData(self::ORDER_HOLD_CR, $orderHoldCr);
    }

    public function getOrderHoldCk(): ?string
    {
        return $this->_getData(self::ORDER_HOLD_CK);
    }

    public function setOrderHoldCk($orderHoldCk): MachOrderInterface
    {
        return $this->setData(self::ORDER_HOLD_CK, $orderHoldCk);
    }

    public function getOrderHoldCkAmt(): ?float
    {
        return $this->_getData(self::ORDER_HOLD_CK_AMT) ? (float) $this->_getData(self::ORDER_HOLD_CK_AMT) : null;
    }

    public function setOrderHoldCkAmt($orderHoldCkAmt): MachOrderInterface
    {
        return $this->setData(self::ORDER_HOLD_CK_AMT, $orderHoldCkAmt);
    }

    public function getOrderHoldPay(): ?string
    {
        return $this->_getData(self::ORDER_HOLD_PAY);
    }

    public function setOrderHoldPay($orderHoldPay): MachOrderInterface
    {
        return $this->setData(self::ORDER_HOLD_PAY, $orderHoldPay);
    }

    public function getOrderHoldPrice(): ?string
    {
        return $this->_getData(self::ORDER_HOLD_PRICE);
    }

    public function setOrderHoldPrice($orderHoldPrice): MachOrderInterface
    {
        return $this->setData(self::ORDER_HOLD_PRICE, $orderHoldPrice);
    }

    public function getOrderHoldBkr(): ?string
    {
        return $this->_getData(self::ORDER_HOLD_BKR);
    }

    public function setOrderHoldBkr($orderHoldBkr): MachOrderInterface
    {
        return $this->setData(self::ORDER_HOLD_BKR, $orderHoldBkr);
    }

    public function getOrderHoldOperator(): ?string
    {
        return $this->_getData(self::ORDER_HOLD_OPERATOR);
    }

    public function setOrderHoldOperator($orderHoldOperator): MachOrderInterface
    {
        return $this->setData(self::ORDER_HOLD_OPERATOR, $orderHoldOperator);
    }

    public function getOrderHoldFraud(): ?string
    {
        return $this->_getData(self::ORDER_HOLD_FRAUD);
    }

    public function setOrderHoldFraud($orderHoldFraud): MachOrderInterface
    {
        return $this->setData(self::ORDER_HOLD_FRAUD, $orderHoldFraud);
    }

    public function getOrderAltReference(): ?string
    {
        return $this->_getData(self::ORDER_ALT_REFERENCE);
    }

    public function setOrderAltReference($orderAltReference): MachOrderInterface
    {
        return $this->setData(self::ORDER_ALT_REFERENCE, $orderAltReference);
    }

    public function getOrderKeycode(): ?string
    {
        return $this->_getData(self::ORDER_KEYCODE);
    }

    public function setOrderKeycode($orderKeycode): MachOrderInterface
    {
        return $this->setData(self::ORDER_KEYCODE, $orderKeycode);
    }

    public function getOrderChannel(): ?string
    {
        return $this->_getData(self::ORDER_CHANNEL);
    }

    public function setOrderChannel($orderChannel): MachOrderInterface
    {
        return $this->setData(self::ORDER_CHANNEL, $orderChannel);
    }

    public function getOrderType(): ?string
    {
        return $this->_getData(self::ORDER_TYPE);
    }

    public function setOrderType($orderType): MachOrderInterface
    {
        return $this->setData(self::ORDER_TYPE, $orderType);
    }

    public function getOrderHsc(): ?string
    {
        return $this->_getData(self::ORDER_HSC);
    }

    public function setOrderHsc($orderHsc): MachOrderInterface
    {
        return $this->setData(self::ORDER_HSC, $orderHsc);
    }

    public function getOrderAttention(): ?string
    {
        return $this->_getData(self::ORDER_ATTENTION);
    }

    public function setOrderAttention($orderAttention): MachOrderInterface
    {
        return $this->setData(self::ORDER_ATTENTION, $orderAttention);
    }

    public function getOrderComments(): ?string
    {
        return $this->_getData(self::ORDER_COMMENTS);
    }

    public function setOrderComments($orderComments): MachOrderInterface
    {
        return $this->setData(self::ORDER_COMMENTS, $orderComments);
    }

    public function getOrderOut1(): ?string
    {
        return $this->_getData(self::ORDER_OUT1);
    }

    public function setOrderOut1($orderOut1): MachOrderInterface
    {
        return $this->setData(self::ORDER_OUT1, $orderOut1);
    }

    public function getOrderOut2(): ?string
    {
        return $this->_getData(self::ORDER_OUT2);
    }

    public function setOrderOut2($orderOut2): MachOrderInterface
    {
        return $this->setData(self::ORDER_OUT2, $orderOut2);
    }

    public function getOrderOut3(): ?string
    {
        return $this->_getData(self::ORDER_OUT3);
    }

    public function setOrderOut3($orderOut3): MachOrderInterface
    {
        return $this->setData(self::ORDER_OUT3, $orderOut3);
    }

    public function getOrderOut4(): ?string
    {
        return $this->_getData(self::ORDER_OUT4);
    }

    public function setOrderOut4($orderOut4): MachOrderInterface
    {
        return $this->setData(self::ORDER_OUT4, $orderOut4);
    }

    public function getOrderOut5(): ?string
    {
        return $this->_getData(self::ORDER_OUT5);
    }

    public function setOrderOut5($orderOut5): MachOrderInterface
    {
        return $this->setData(self::ORDER_OUT5, $orderOut5);
    }

    public function getOrderItems(): array
    {
        return $this->_getData(self::ORDER_ITEMS) ?: [];
    }

    public function setOrderItems($orderItems): MachOrderInterface
    {
        return $this->setData(self::ORDER_ITEMS, $orderItems);
    }

    public function getPackageInfo(): array
    {
        return $this->_getData(self::PACKAGE_INFO) ?: [];
    }

    public function setPackageInfo($packageInfo): MachOrderInterface
    {
        return $this->setData(self::PACKAGE_INFO, $packageInfo);
    }
}
