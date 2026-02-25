<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/11/2019 4:00 PM
 * @brief
 */

namespace Crimson\MachCatalog\Model\Api;

use Crimson\MachBase\Model\Api\AbstractApi;
use Crimson\MachBase\Model\Api\ApiContext;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCatalog\Model\Api\Result\PriceResult;
use Crimson\MachCatalog\Model\Api\Result\PriceResultFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Newsletter\Model\ResourceModel\Subscriber;

/**
 * Class Price
 * @package Crimson\MachCatalog\Model\Api
 */
class Price extends AbstractApi
{

    protected DateTime $dateTime;
    protected PriceResultFactory $priceResultFactory;

    public function __construct(
        ApiContext $apiContext,
        MachConfig $machConfig,
        Subscriber $subscriberResource,
        PriceResultFactory $priceResultFactory,
        DateTime $dateTime
    ) {
        parent::__construct($apiContext, $machConfig, $subscriberResource);
        $this->priceResultFactory = $priceResultFactory;
        $this->dateTime           = $dateTime;
    }

    /**
     * @param ProductInterface $product
     * @param int              $qtyInQuote
     * @param null             $machCustomerNumber
     *
     * @return PriceResult
     */
    public function get(ProductInterface $product, $qtyInQuote = 1, $machCustomerNumber = null): PriceResult
    {
        $action = self::CALL_INV_PRICE;
        $this->debugLog('Beginning ' . $action . ' Call');
        $status = $cacheUsed = false;

        $qtyInQuote = (int)$qtyInQuote;
        $qtyInQuote = max($qtyInQuote, 1);

        /** @var PriceResult $result */
        $result = $this->priceResultFactory->create();
        $result->setProduct($product);
        $result->setRequestedQty($qtyInQuote);

        if (is_null($machCustomerNumber)) {
            $machCustomerNumber = $this->getMachCustomerNumber();
        }

        try {
            $arguments = array(
                $this->_soapVar($this->getMachSku($product), 'InvNumberIn'),
                /*
                 * 1 - - Inventory Number ,Qty Orderded, Customer Number, Keycode and Page returns Availablity and Price
                 */
                $this->_soapVar($this->getSecurityCode(), 'SecurityCode'),
                $this->_soapVar(1, 'ActionCode'),
            );

            $invPriceIn = array(
                $this->_soapVar($machCustomerNumber, 'CustNumber'),
                $this->_soapVar($qtyInQuote, 'InvQty'),

            );

            $arguments[] = $this->_soapVar($invPriceIn, 'INV_PRICE_IN');

            $cachedResult = $this->_getCachedResult($arguments, $action);

            //if session data is younger than 30 minutes, no need to refresh.
            if ($cachedResult !== false) {
                $result->addData($cachedResult);
                $cacheUsed = $status = true;

                return $result;
            }

            $response = $this->makeRequest($action, $arguments, SOAP_ENC_OBJECT);

            if (!isset($response->ERROR_OUT->ErrorNumber)) {
                throw new LocalizedException(__('Invalid response received, unable to determine product price.'));
            } else {
                $errorNumber  = $response->ERROR_OUT->ErrorNumber;
                $errorMessage = $response->ERROR_OUT->ErrorMsg;
                if (!$this->_isSuccess($action, $errorNumber)) {
                    $message = 'Error occurred attempting to retrieve product price from MACH ERP.<br/>';
                    $message .= 'Error Number: %1$s. Returned Message: %2$s <br/>';
                    $message = sprintf($message, $errorNumber, $errorMessage);

                    $this->infoLog($message);

                    return $result;
                } else {
                    $priceData = $response->INV_PRICE_OUT;

                    $data = array(
                        'customer_number' => $priceData->CustNumber,
                        //Qty available to ship
                        'qty_available'   => (float)$priceData->InvQtyShip,
                        //Qty that must be backordered
                        'qty_backordered' => (float)$priceData->InvQtyBkr,
                        //Price of this item for this customer and keycode
                        'final_price'     => (float)$priceData->InvPrice,
                        'price'           => (float)$priceData->InvPrice,
                        //Standard Price of Item
                        'standard_price'  => (float)$priceData->InvStdPrice,
                        //Date when Backordered Qty would be Available
                        'available_date'  => $priceData->InvAvailDate,
                        //Lead Time in Days
                        'lead_time'       => (int)$priceData->InvLeadTime,
                    );

                    //save to session so we don't have to repeat the same api calls.
                    $this->_setCachedResult($arguments, $data, $action);

                    $result->addData($data);

                    $this->debugLog($result);
                    $status = true;
                }
            }
        } catch (LocalizedException $e) {
            $this->infoLog($e);

            return $result;
        } catch (\Exception $e) {
            $this->criticalLog($e);

            return $result;
        } finally {
            $message = array(
                'message'    => sprintf('Finished %s.  Result: %s', $action, ($status ? 'PASS' : 'FAIL')),
                'cache_used' => $this->_castBoolToString($cacheUsed, 'No', 'Yes'),
                'product'    => $product->getSku(),
                'result'     => (isset($cachedResult) ? $cachedResult : (isset($data) ? $data : array())),
            );
            $this->debugLog($message);
            $this->debugLog('-------------------------------------------');
        }

        return $result;
    }
}
