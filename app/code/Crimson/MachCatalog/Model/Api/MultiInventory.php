<?php

namespace Crimson\MachCatalog\Model\Api;

use Crimson\MachBase\Model\Api\AbstractApi;
use Crimson\MachBase\Model\Api\ApiContext;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCatalog\Model\Api\Result\MultiInventoryResult;
use Crimson\MachCatalog\Model\Api\Result\MultiInventoryResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Crimson\MachBase\Model\Api\Client;

/**
 * Class MultiInventory
 * @package Crimson\MachCatalog\Model\Api
 */
class MultiInventory extends AbstractApi
{
    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var MultiInventoryResultFactory
     */
    protected $inventoryResultFactory;

    public function __construct(
        ApiContext $apiContext,
        MachConfig $machConfig,
        protected Client $defaultClient,
        Subscriber $subscriberResource,
        MultiInventoryResultFactory $inventoryResultFactory,
        DateTime $dateTime
    ) {
        parent::__construct($apiContext, $machConfig, $subscriberResource);
        $this->inventoryResultFactory = $inventoryResultFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * @param array $skuList
     * @param bool  $forceRefresh
     *
     * @return MultiInventoryResult
     */
    public function get(array $skuList, bool $forceRefresh = false): MultiInventoryResult
    {
        $action = self::CALL_INV_INFO;
        $this->debugLog('Beginning ' . $action . ' Call');
        $status = $cacheUsed = false;

        /** @var MultiInventoryResult $result */
        $result = $this->inventoryResultFactory->create();
        if (is_array($skuList)) {
            $result->setSkuList($skuList);
        }

        if (empty($skuList)) {
            return $result;
        }

        try {
            $arguments = array(
                $this->_soapVar(null, 'InvNumberIn'),
                $this->_soapVar($this->getSecurityCode(), 'SecurityCode'),
                $this->_soapVar(3, 'ActionCode'),
            );

            $multiItemSoap = $this->prepareMultiItemSoap($skuList);
            $arguments[] = $this->_soapVar($multiItemSoap, 'INV_INFO_IN');

            $cachedResult = $this->_getCachedResult($arguments, $action);

            //if cache data is younger than 30 minutes, no need to refresh.
            //if force refresh is true, don't both checking for data, force it to continue through the function
            if (!$forceRefresh && $cachedResult !== false) {
                $result->addData($cachedResult);
                $status = $cacheUsed = true;
                $result->setData('response_status', $status);

                return $result;
            }

            $this->apiContext->setClient($this->defaultClient);
            $response = $this->makeRequest($action, $arguments, SOAP_ENC_OBJECT);

            if (!isset($response->ERROR_OUT->ErrorNumber)) {
                throw new LocalizedException(__('Invalid response received, unable to determine product price.'));
            } else {
                $errorNumber  = $response->ERROR_OUT->ErrorNumber;
                $errorMessage = $response->ERROR_OUT->ErrorMsg;
                if (!$this->_isSuccess($action, $errorNumber)) {
                    $message = 'Error occurred attempting to retrieve MultiInventory info from MACH ERP.<br/>';
                    $message .= 'Error Number: %1$s. Returned Message: %2$s <br/>';
                    $message = sprintf($message, $errorNumber, $errorMessage);

                    $this->infoLog($message);

                    $result->setErrorMessage($errorMessage)
                        ->setErrorNumber($errorNumber);

                    return $result;
                } else {
                    if (empty($response->INV_INFO_OUT)) {
                        $message = 'Error occurred attempting to retrieve MultiInventory info from MACH ERP.<br/>';
                        $message .= 'SKUs sent do not exist on Mach  <br/>';
                        $errorMessage = sprintf($message);

                        $this->infoLog($message);

                        $result->setErrorMessage($errorMessage)
                            ->setErrorNumber($errorNumber);

                        return $result;
                    } else {
                        $status        = true;
                        $result->setData('response_status', $status);
                        $inventoryData = $response->INV_INFO_OUT;

                        $multiItemData = $inventoryData->ItemList;
                        if (!is_array($multiItemData)) {
                            $multiItemData = array($multiItemData);
                        }

                        $updateDate = $this->dateTime->gmtDate();
                        $data = $this->prepareInventoryResult($skuList, $multiItemData, $updateDate);

                        //save to session so we don't have to repeat the same api calls.
                        $this->_setCachedResult($arguments, $data, $action);

                        $result->addData($data);

                        $this->debugLog($result);
                    }
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
                'sku_list'    => print_r($skuList,true),
                'result'     => (isset($cachedResult) ? $cachedResult : (isset($data) ? $data : array())),
            );
            $this->debugLog($message);
            $this->debugLog('-------------------------------------------');
        }

        return $result;
    }

    /**
     * @param array  $skuList
     * @param        $multiItemData
     * @param string $updateDate
     *
     * @return array
     */
    public function prepareInventoryResult(array $skuList, $multiItemData, string $updateDate) :array
    {
        if (empty($skuList)) {
            return [];
        }

        $result = [];
        foreach ($multiItemData as $itemData) {
            if (!isset($itemData->Number) || !in_array($itemData->Number,$skuList)) {
                continue;
            }

            //They do support Dropship on this call
            $data = [
                'updated_at'        => $updateDate,
                'qty_available'     => (float)$itemData->QtyAvail,
                'list_price'        => (float)$itemData->ListPrice,
                'status_code'       => $itemData->InvStatus,
                'drop_ship'         => $this->_castStringToBool($itemData->Option3),
            ];

            $result[$itemData->Number] = $data;
        }

        return $result;
    }

    /**
     * @param array $skuList
     *
     * @return array
     */
    public function prepareMultiItemSoap(array $skuList) :array
    {
        $soapFinal = [];
        foreach ($skuList as $sku) {
            if (!empty($sku)) {
                $ele = $this->_soapVar($sku, 'MultiItem');
                $soapElement = $this->_soapVar([$ele], 'MultiItemList');
                $soapFinal[] = $soapElement;
            }
        }

        return $soapFinal;
    }
}
