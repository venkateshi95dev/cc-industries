<?php
/**
 * @namespace   Crimson
 * @module      MachBase
 * @author      Matheus Gontijo
 * @email       mgontijo@crimsonagility.com
 * @date        12/20/2018 4:38 PM
 * @brief
 */

namespace Crimson\MachBase\Model\Api;

use Crimson\MachBase\Model\MachConfig;
use Magento\Newsletter\Model\ResourceModel\Subscriber;

class HealthCheck extends AbstractApi
{
    const ACTION = self::CALL_HEALTH_CHECK;

    const API_SUCCESS_CODE = 0;

    const MACH_STATUS_UNKNOWN = 0;
    const MACH_STATUS_UP = 1;
    const MACH_STATUS_DOWN = 2;

    public function __construct(
        ApiContext $apiContext,
        MachConfig $machConfig,
        Subscriber $subscriberResource
    ) {
        parent::__construct($apiContext, $machConfig, $subscriberResource);
    }

    /**
     * @param bool $useCache
     *
     * @return bool
     */
    public function isUp(bool $useCache = true): bool
    {
        $cacheKey = 'mach_erp_health_check';
        if  ($useCache) {
            $isUp = (int) ($this->cacheManager->load($cacheKey) ?: self::MACH_STATUS_UNKNOWN);
            if ($isUp !== self::MACH_STATUS_UNKNOWN) {
                return $isUp === self::MACH_STATUS_UP;
            }
        }

        $this->debugLog(sprintf('Beginning %s Call', self::ACTION));

        try {
            $isUp = $this->_isUp();
            $status = ($isUp === true ? self::MACH_STATUS_UP : self::MACH_STATUS_DOWN);

            $this->cacheManager->save($status, $cacheKey, [], 300);
        } catch (\Exception $e) {
            $isUp = false;
            $this->criticalLog($e);
        }

        $this->debugLog(sprintf('Finished %s Result: %s', self::ACTION, ($isUp ? 'PASS' : 'FAIL')));

        return $isUp;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function _isUp(): bool
    {
        $arguments = array(
            $this->_soapVar('WEB', 'Source'),
            $this->_soapVar($this->getSecurityCode(), 'SecurityCode'),
            $this->_soapVar(self::HEALTH_CHECK_ACTION_CODE, 'ActionCode'),
        );

        $response = $this->makeRequest(self::ACTION, $arguments);

        $errorNumber = $response->ERROR_OUT->ErrorNumber ?? null;
        if ($errorNumber !== null && ((int) $errorNumber) === self::API_SUCCESS_CODE) {
            return true;
        }

        return false;
    }
}
