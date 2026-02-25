<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/19/2019 9:50 AM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Model\Api;

use Crimson\MachBase\Exception\MachConnectionException;
use Crimson\MachBase\Model\Api\AbstractApi;
use Crimson\MachBase\Model\Api\ApiContext;
use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCatalogRequest\Api\Data\CatalogRequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Newsletter\Model\ResourceModel\Subscriber;

/**
 * Class CatalogRequest
 * @package Crimson\MachCatalogRequest\Model\Api
 */
class CatalogRequest extends AbstractApi
{

    protected HealthCheck $healthCheck;

    public function __construct(
        ApiContext $apiContext,
        MachConfig $machConfig,
        Subscriber $subscriberResource,
        HealthCheck $healthCheck
    ) {
        parent::__construct($apiContext, $machConfig, $subscriberResource);
        $this->healthCheck = $healthCheck;
    }

    /**
     * @param CatalogRequestInterface $catalogRequest
     *
     * @return $this
     * @throws LocalizedException
     * @throws \Exception
     */
    public function export(CatalogRequestInterface $catalogRequest): CatalogRequest
    {
        if (!$this->healthCheck->isUp()) {
            //will be attempted again on next cron run.
            throw new MachConnectionException(__('MACH is down.'));
        }

        $action = self::CALL_ADD_ORDER;
        $this->debugLog(__('Beginning %1 Call for catalog request', $action));
        $status = false;

        $startTimerLive = microtime(true);
        try {
            $arguments = array(
                $this->_soapVar($this->getSecurityCode(), 'SecurityCode'),
                /*
                 * 1 – Add Order to Mach System
                 * 2 – Add Catalog Request to Mach System
                 * 3 – Add a new customer (no order or catalog request)
                 */
                $this->_soapVar(self::ADD_ORDER_ACTION_CODE_ADD_CATALOG_REQUEST, 'ActionCode'),
            );

            $addOrderInData = array(
                $this->_soapVar(null, 'CustNumber'),
                $this->_soapVar($catalogRequest->getName(), 'BillName'),
                $this->_soapVar($catalogRequest->getStreet1(), 'BillAdd1'),
                $this->_soapVar($catalogRequest->getStreet2(), 'BillAdd2'),
                $this->_soapVar($catalogRequest->getCity(), 'BillCity'),
                $this->_soapVar($catalogRequest->getState(), 'BillState'),
                $this->_soapVar($catalogRequest->getZip(), 'BillZip'),
                $this->_soapVar(null, 'BillCounty'),
                $this->_soapVar($catalogRequest->getCountry(), 'BillCountry'),
                $this->_soapVar(null, 'BillPhone'),
                $this->_soapVar(null, 'BillFax'),
                $this->_soapVar($catalogRequest->getEmail(), 'BillEmail'),
                $this->_soapVar(null, 'BillPIN'),
                $this->_soapVar(null, 'BillContact'),
                $this->_soapVar(null, 'BillTitle'),
                $this->_soapVar($this->_castIntToString($catalogRequest->getRequestMore()), 'BillMailList'),
                $this->_soapVar($this->_castIntToString($catalogRequest->getRequestMore()), 'BillRentList'),
                $this->_soapVar($this->_castIntToString($catalogRequest->getRequestMore()), 'BillEmailList'),
                $this->_soapVar(null, 'BillOpen1'),
                $this->_soapVar(null, 'BillOpen2'),
                $this->_soapVar(null, 'BillOpen3'),
                $this->_soapVar(null, 'BillOpen4'),
                $this->_soapVar(null, 'BillOpen5'),
                $this->_soapVar($catalogRequest->getRequestedItems(), 'KeyCode'),
            );

            $arguments[] = $this->_soapVar($addOrderInData, 'ADD_ORDER_IN');
            $response    = $this->makeRequest($action, $arguments, SOAP_ENC_OBJECT);
            if (!isset($response->ERROR_OUT->ErrorNumber)) {
                throw new LocalizedException(__('Invalid response received, unable to determine if order was added.'));
            } else {
                $errorNumber  = $response->ERROR_OUT->ErrorNumber;
                $errorMessage = $response->ERROR_OUT->ErrorMsg;

                if (!$this->_isSuccess($action, $errorNumber)) {
                    $this->infoLog(__('Error. Code: %1, Message: %2', $errorNumber, $errorMessage));
                } else {
                    $this->infoLog('Catalog Request Success');
                }
            }
            $status = true;
        } catch (LocalizedException $e) {
            $this->infoLog($e);
            throw $e;
        } catch (\Exception $e) {
            $this->criticalLog($e);
            throw $e;
        } finally {
            $runTime = round(microtime(true) - $startTimerLive, 5);
            $message = __('Finished %1 in %2.  Result: %3', $action, $runTime, $status ? 'PASS' : 'FAIL');
            $this->debugLog($message);
        }

        return $this;
    }
}
