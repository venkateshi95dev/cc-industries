<?php
/**
 * @namespace   Crimson
 * @module      MachBase
 * @author      Matheus Gontijo
 * @email       mgontijo@crimsonagility.com
 * @date        20/12/2018 4:38 PM
 * @brief
 */

namespace Crimson\MachBase\Model\Api;

use Crimson\MachBase\Model\Log;
use Crimson\MachBase\Model\MachConfig;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Newsletter\Model\Subscriber;

/**
 * Class AbstractApi
 * @package Crimson\MachBase\Model\Api
 */
abstract class AbstractApi
{
    const CALL_HEALTH_CHECK = 'HEALTH_CHECK';
    const CALL_ADD_ORDER = 'ADD_ORDER';
    const CALL_ADDRESS_VERIFY = 'ADDRESS_VERIFY';
    const CALL_CUST_INFO = 'CUST_INFO';
    const CALL_CUST_LIST = 'CUST_LIST';
    const CALL_CUST_UPDATE = 'CUST_UPDATE';
    const CALL_GC_INFO = 'GC_INFO';
    const CALL_GET_FREIGHT = 'GET_FREIGHT';
    const CALL_INV_INFO = 'INV_INFO';
    const CALL_INV_PRICE = 'INV_PRICE';
    const CALL_ORDER_DETAIL = 'ORDER_DETAIL';
    const CALL_ORDER_LIST = 'ORDER_LIST';
    const CALL_PAYWARE_AUTH = 'PAYWARE_AUTH';

    const MACH_ORDER_STATUS_CANCELLED = 'Cancelled Order';
    const MACH_ORDER_STATUS_OPEN = 'Open Order';
    const MACH_ORDER_SHIPPED = 'Shipped Order';

    const ERROR_NUMBER_OK = 0;
    const ERROR_NUMBER_SECURITY_CODE = 1;
    const ERROR_NUMBER_FILE_OPEN = 2;
    const ERROR_NUMBER_RECORD_EXIST = 3;
    const ERROR_NUMBER_BAD_DATA = 4;

    const GET_FREIGHT_ACTION_CODE_SHIPRATE = 1;
    const GET_FREIGHT_MULTIPLE_ACTION_CODE_SHIPRATE = 5;
    const GET_FREIGHT_ACTION_CODE_TAX = 3;

    const ADD_ORDER_ACTION_CODE_ADD_ORDER = 1;
    const ADD_ORDER_ACTION_CODE_ADD_CATALOG_REQUEST = 2;
    const ADD_ORDER_ACTION_CODE_ADD_CUSTOMER = 3;

    const HEALTH_CHECK_ACTION_CODE = 2;
    /**
     * @var ApiContext
     */
    protected $apiContext;

    /** @var CacheInterface */
    protected $cacheManager;

    /** @var ScopeConfigInterface|null */
    protected $config = null;
    /** @var EventManager  */
    protected $eventManager;

    /** @var Json */
    protected $json;

    /** @var Log|null */
    protected $log = null;

    protected $helper = null;
    /**
     * @var MachConfig
     */
    protected $machConfig;
    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber
     */
    protected $subscriberResource;

    public function __construct(
        ApiContext $apiContext,
        MachConfig $machConfig,
        \Magento\Newsletter\Model\ResourceModel\Subscriber $subscriberResource
    ) {
        $this->config       = $apiContext->getConfig();
        $this->log          = $apiContext->getLog();
        $this->helper       = $apiContext->getHelper();
        $this->cacheManager = $apiContext->getCacheManager();
        $this->json         = $apiContext->getJson();
        $this->machConfig   = $machConfig;
        $this->apiContext   = $apiContext;
        $this->subscriberResource = $subscriberResource;
        $this->eventManager = $apiContext->getEventManager();
    }

    protected function getSecurityCode()
    {
        return $this->machConfig->getSecurityCode();
    }

    /**
     * @param      $action
     * @param      $arguments
     * @param null $encType
     *
     * @return mixed
     * @throws \Exception
     */
    protected function makeRequest($action, $arguments, $encType = SOAP_ENC_OBJECT)
    {
        $this->debugLog(sprintf('%s - SOAP Request', $action));

        try {
            if (is_null($encType)) {
                $response = $this->apiContext->getClient()->$action($arguments);
            } else {
                $response = $this->apiContext->getClient()->$action(new \SoapVar($arguments, $encType));
            }
        } catch (\Exception $e) {
            $this->debugMakeRequest('ERROR');
            throw $e;
        }

        $this->debugMakeRequest('SUCCESS');

        return $response;
    }

    protected function debugMakeRequest($result)
    {
        try {
            $this->debugLog($result);
            $this->debugLog($this->apiContext->getClient()->__getLastRequest());
            $this->debugLog($this->apiContext->getClient()->__getLastResponse());
        } catch (\Exception $e) {
            $this->debugLog($e->getMessage());
        }
    }

    /**
     * @param $message
     */
    protected function debugLog($message)
    {
        $this->log->getDebugLog()->debug(json_encode($message));
    }

    /**
     * @param $message
     */
    protected function infoLog($message)
    {
        $this->log->getInfoLog()->info(json_encode($message));
    }

    /**
     * @param $message
     */
    protected function criticalLog($message)
    {
        $this->log->getInfoLog()->critical(json_encode($message));
    }

    protected function soapRequestLog($message)
    {
        $this->log->getSoapRequestLog()->debug(json_encode($message));
    }

    protected function soapResponseLog($message)
    {
        $this->log->getResponseLog()->debug(json_encode($message));
    }

    /**
     * @param $value
     * @param null $key
     * @return \SoapVar
     */
    protected function _soapVar($value, $key = null): \SoapVar
    {
        if (is_scalar($value)) {
            $encType = XSD_STRING;
        } else {
            $encType = SOAP_ENC_OBJECT;
        }

        if (is_null($key)) {
            return new \SoapVar($value, $encType, null, null, null, 'machsoftware');
        } else {
            return new \SoapVar($value, $encType, null, null, $key, 'machsoftware');
        }
    }

    /**
     * @param $value
     * @param string $defaultFalse
     * @param string $defaultTrue
     * @return string
     */
    protected function _castBoolToString($value, $defaultFalse = 'N', $defaultTrue = 'Y'): string
    {
        return ($value ? $defaultTrue : $defaultFalse);
    }

    /**
     * @param $value
     * @param string $defaultFalse
     * @param string $defaultTrue
     * @return string
     */
    protected function _castBoolToStringNewsletter($value, $defaultFalse = 'C', $defaultTrue = 'B'): string
    {
        return ($value ? $defaultTrue : $defaultFalse);
    }

    /**
     * @param $value
     * @param string $true
     * @return bool
     */
    protected function _castStringToBool($value, $true = 'Y'): bool
    {
        return ((string)$value) === $true;
    }

    /**
     * @param $value
     * @return string
     */
    protected function _castIntToString($value): string
    {
        return ($value == 0 ? 'N' : 'Y');
    }

    /**
     * @param $action
     * @param null $errorNumber
     * @param null $response
     * @return array|bool
     */
    protected function _isSuccess($action, $errorNumber = null, $response = null, $checkForTax = false)
    {
        /*
         * if we are in a freight call, on success error_out is not returned sometimes.
         * We are under the impression that if it is not returned and 'freight_info_out' is returned it is a successful
         *  call.  If error_out IS returned, we will check as normal and return what we can.
         */
        if ($action == self::CALL_GET_FREIGHT && !is_null($response)) {
            if (isset($response->ERROR_OUT->ErrorNumber)) {
                $errorNumber = $response->ERROR_OUT->ErrorNumber;

                //if we have an error number and it is successful, return true.
                if ($this->_isSuccess($action, $response->ERROR_OUT->ErrorNumber) === true) {
                    return true;
                } else {
                    //we may not have error message, if we do return it, otherwise return false.
                    if (isset($response->ERROR_OUT->ErrorMsg)) {
                        return array(
                            'error_number'  => $errorNumber,
                            'error_message' => $response->ERROR_OUT->ErrorMsg,
                        );
                    } else {
                        return array(
                            'error_number'  => $errorNumber,
                            'error_message' => false,
                        );
                    }
                }
            }

            return $checkForTax ? isset($response->FREIGHT_INFO_OUT) : isset($response->FREIGHT_INFO_OUT->RATESHOPOUT);
        } else {
            if (!is_numeric($errorNumber)) {
                return false;
            }

            if (!is_null($errorNumber) && (((int)$errorNumber) === 0)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @param string|ProductInterface $product
     *
     * @return string|null
     */
    protected function getMachSku($product): ?string
    {
        return $this->apiContext->getMachSku()->get($product);
    }

    /**
     * @param $arguments
     * @param $action
     *
     * @return null|mixed
     */
    protected function _getCachedResult($arguments, $action)
    {
        $cacheKey = $this->_getCacheKeyFromArgumentArray($arguments, $action);

        $result = $this->cacheManager->load($cacheKey) ?: false;
        if ($result && !is_array($result)) {
            $result = $this->json->unserialize($result);
        }

        return $result;
    }

    /**
     * @param array  $arguments
     * @param array  $result
     * @param string $action
     *
     * @return $this
     */
    protected function _setCachedResult($arguments, $result, $action): AbstractApi
    {
        $cacheKey        = $this->_getCacheKeyFromArgumentArray($arguments, $action);
        $result          = $this->_removeUnserializableElements($result);
        $maxAgeInSeconds = $this->_getMaxRequestAge($action) * 60;

        $this->cacheManager->save($this->json->serialize($result), $cacheKey, [], $maxAgeInSeconds);

        return $this;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function _removeUnserializableElements(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->_removeUnserializableElements($value);
            } elseif (!$this->_isSerializable($value)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private function _isSerializable($value): bool
    {
        try {
            $value = $this->json->serialize($value);
            return $value === "0" ? true : ($value ? true : false);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string|null $action
     *
     * @return int - minutes
     */
    protected function _getMaxRequestAge(string $action = null): int
    {
        switch ($action) {
            case self::CALL_INV_INFO:
                return 20;
            case self::CALL_GET_FREIGHT:
                return 60;
            default:
                return 45;
        }
    }

    /**
     * @param $arguments
     * @param $action
     *
     * @return string
     */
    protected function _getCacheKeyFromArgumentArray($arguments, $action): string
    {
        return sprintf('%s%s_%s', $this->_getCacheKeyPrefix(), $action, $this->_generateRequestHash($arguments));
    }

    /**
     * the default cache prefix for all cached results, same so all can be cleared.
     *
     * @return string
     */
    private function _getCacheKeyPrefix(): string
    {
        return 'mach_request_';
    }

    /**
     * @param $datetime - yyyy-MM-dd HH:mm:ss
     *
     * @return \DateTime
     * @throws \Exception
     */
    protected function getIntegrationDate($datetime)
    {
        $date = new \DateTime($datetime);

        $timeZone = new \DateTimeZone($this->machConfig->getIntegrationTimezone());
        $date->setTimezone($timeZone);

        return $date;
    }

    /**
     * @param $request
     *
     * @return string
     */
    protected function _generateRequestHash($request): string
    {
        return sha1($this->json->serialize($request));
    }

    /**
     * @param string|DataObject $input - input is string or object with getEmail() or getCustomerEmail()
     *
     * @return bool
     * @throws LocalizedException
     */
    protected function _isNewsletterSubscribed($input): bool
    {
        if ($input instanceof DataObject) {
            $email = $input->getEmail();
            if ($email) {
                return $this->_isEmailNewsletterSubcribed($email);
            }

            $email = $input->getCustomerEmail();
            if ($email) {
                return $this->_isEmailNewsletterSubcribed($email);
            }
        } elseif (is_string($input)) {
            return $this->_isEmailNewsletterSubcribed($input);
        }

        return false;
    }

    /**
     * @param $email
     *
     * @return bool
     * @throws LocalizedException
     */
    protected function _isEmailNewsletterSubcribed($email): bool
    {
        $result   = $this->subscriberResource->loadByEmail($email);

        if (empty($result)
            || !isset($result[$this->subscriberResource->getIdFieldName()])
            || !isset($result['subscriber_status'])
        ) {
            return false;
        }

        $subscribed = ($result['subscriber_status'] === Subscriber::STATUS_SUBSCRIBED);

        return ($result[$this->subscriberResource->getIdFieldName()] && $subscribed);
    }
}
