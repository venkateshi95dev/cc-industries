<?php

namespace Crimson\Payware\Model\Payware;

/**
 * Class Request
 * @package Crimson\Payware\Model\Payware
 *
 * @method Request setRequestType(string $requestType)
 * @method Request setAuthAmt(float $authAmt)
 * @method Request setCardNumber(int $cardNumber)
 * @method Request setPaywareTransactionId(string $paywareTransactionId)
 * @method Request setExpDate(int $expDate)
 * @method Request setCvv(int $cvv)
 * @method Request setName(string $name)
 * @method Request setAddress(string $address)
 * @method Request setZip(string $zip)
 * @method Request setWebOrderNumber(string $webOrderNumber)
 *
 * @method string getRequestType()
 * @method float getAuthAmt()
 * @method int getCardNumber()
 * @method string getPaywareTransactionId()
 * @method int getExpDate()
 * @method int getCvv()
 * @method string getName()
 * @method string getAddress()
 * @method string getZip()
 * @method string getWebOrderNumber()
 */
class Request extends \Magento\Framework\DataObject
{

}