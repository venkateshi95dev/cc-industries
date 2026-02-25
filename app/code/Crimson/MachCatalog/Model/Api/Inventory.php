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
use Crimson\MachCatalog\Model\Api\Result\InventoryResult;
use Crimson\MachCatalog\Model\Api\Result\InventoryResultFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Crimson\MachBase\Model\Api\Client;

/**
 * Class Inventory
 * @package Crimson\MachCatalog\Model\Api
 */
class Inventory extends AbstractApi
{

    public function __construct(
        ApiContext $apiContext,
        MachConfig $machConfig,
        protected Client $client,
        Subscriber $subscriberResource,
        protected InventoryResultFactory $inventoryResultFactory,
        protected DateTime $dateTime
    ) {
        parent::__construct($apiContext, $machConfig, $subscriberResource);
    }

    /**
     * @param ProductInterface|string $product
     * @param bool                    $forceRefresh
     * @param bool                    $fullResult
     *
     * @return InventoryResult
     */
    public function get($product, bool $forceRefresh = false, bool $fullResult = false): InventoryResult
    {
        $action = self::CALL_INV_INFO;
        $this->debugLog('Beginning ' . $action . ' Call');
        $status = $cacheUsed = false;

        /** @var InventoryResult $result */
        $result = $this->inventoryResultFactory->create();
        if ($product instanceof ProductInterface) {
            $result->setProduct($product);
        }

        //if we have a sku we know we have a simple and should check it.
        if ($product instanceof ProductInterface
            && $product->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        ) {
            return $result;
        }

        try {
            $arguments = array(
                $this->_soapVar($this->getMachSku($product), 'InvNumberIn'),
                /*
                 * 1 - Inventory Number Only Lookup
                 * 2 - Lookups
                 */
                $this->_soapVar($this->getSecurityCode(), 'SecurityCode'),
                $this->_soapVar(1, 'ActionCode'),
            );

            $arguments[] = $this->_soapVar([], 'INV_INFO_IN');
            $cachedResult = $this->_getCachedResult($arguments, $action);

            //if cache data is younger than 30 minutes, no need to refresh.
            //if force refresh is true, don't both checking for data, force it to continue through the function
            if (!$forceRefresh && $cachedResult !== false) {
                $result->addData($cachedResult);
                $status = $cacheUsed = true;

                return $result;
            }

            $this->apiContext->setClient($this->client);
            $response = $this->makeRequest($action, $arguments, SOAP_ENC_OBJECT);

            if (!isset($response->ERROR_OUT->ErrorNumber)) {
                throw new LocalizedException(__('Invalid response received, unable to determine product price.'));
            } else {
                $errorNumber  = $response->ERROR_OUT->ErrorNumber;
                $errorMessage = $response->ERROR_OUT->ErrorMsg;
                if (!$this->_isSuccess($action, $errorNumber)) {
                    $message = 'Error occurred attempting to retrieve inventory info from MACH ERP.<br/>';
                    $message .= 'Error Number: %1$s. Returned Message: %2$s <br/>';
                    $message = sprintf($message, $errorNumber, $errorMessage);

                    $this->infoLog($message);

                    $result->setErrorMessage($errorMessage)
                        ->setErrorNumber($errorNumber);

                    return $result;
                } else {
                    $status        = true;
                    $inventoryData = $response->INV_INFO_OUT;

                    $data = array(
                        'updated_at'        => $this->dateTime->gmtDate(),
                        'qty_available'     => (float)$inventoryData->InvQtyAvail,
                        'list_price'        => (float)$inventoryData->InvListPrice,
                        'status_code'       => $inventoryData->InvStatusCode,
                        'drop_ship'         => $this->_castStringToBool($inventoryData->InvDSFlag),
                        'gift_certificate'  => $this->_castStringToBool($inventoryData->InvGCFlag),
                        'taxable'           => $this->_castStringToBool($inventoryData->InvTaxFlag),
                        'club_flag'         => $this->_castStringToBool($inventoryData->InvClubFlag),
                        'additional_amount' => (float)$inventoryData->InvAddAmt,
                    );

                    if (!empty($inventoryData->InvOpen1)) {
                        $data['has_core_charge'] = true;
                        $data['core_charge_sku'] = $inventoryData->InvOpen1;

                        //value is now returned correctly
                        $data['core_charge_amount'] = (float) $inventoryData->InvOpen2;
                    } else {
                        $data['has_core_charge'] = false;
                    }
                    if ($fullResult) {
                        $data['full_inventory_response'] = $this->json->unserialize($this->json->serialize($inventoryData));
                    }
                    //save to session so we don't have to repeat the same api calls.
                    $this->_setCachedResult($arguments, $data, $action);

                    $result->addData($data);

                    $this->debugLog($result);
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
                'product'    => ($product instanceof ProductInterface ? $product->getSku() : $product),
                'result'     => (isset($cachedResult) ? $cachedResult : (isset($data) ? $data : array())),
            );
            $this->debugLog($message);
            $this->debugLog('-------------------------------------------');
        }

        return $result;
    }
}
