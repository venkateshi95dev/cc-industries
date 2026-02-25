<?php

namespace Crimson\MachAddressVerification\Model\Api;

use Crimson\MachAddressVerification\Model\Api\AddressVerification\Result;
use Crimson\MachAddressVerification\Model\Api\AddressVerification\ResultFactory;
use Crimson\MachAddressVerification\Model\Config as MachAddressConfig;
use Crimson\MachBase\Model\Api\AbstractApi;
use Crimson\MachBase\Model\Api\Addressclient;
use Crimson\MachBase\Model\Api\ApiContext;
use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachBase\Model\MachConfig;
use Magento\Framework\DataObject;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Framework\Stdlib\DateTime\DateTime as GetDateTime;

/**
 * Class AddressVerification
 * @package Crimson\MachAddressVerification\Model\Api
 */
class AddressVerification extends AbstractApi
{

    CONST CALL_ADDRESS_VERIFY_COUNTY = 'ADDRESS_COUNTY_VERIFY';

    //Open1 gets the response for when a Ste/Apt number is missed and other values defined here
    CONST CALL_ADDRESS_VERIFY_OPEN1_STE_APT_VALID_VALUE = "Address Verified";
    CONST CALL_ADDRESS_VERIFY_OPEN1_STE_APT_MESSAGE_VALUES = [
        "Address Verified"           => "Address Verified",
        "Ste/Apt Missing or Invalid" => "Suite/Apt Missing or Invalid",
        "Address Out of Range"       => "Address Out of Range",
    ];
    CONST COUNTY_MACH_ARRAY_KEY = 'county';
    CONST RESBUSFLAG_MACH_ARRAY_KEY = 'res_com_flag';
    CONST VERIFICATION_CORRECT_RESPONSE_STATUS = ['V','A','Y'];

    /**
     * @var MachConfig
     */
    protected $machConfig;

    /**
     * @var ApiContext
     */
    protected $apiContext;

    public function __construct(
        ApiContext $apiContext,
        protected HealthCheck $healthCheck,
        Subscriber $subscriberResource,
        MachConfig $machConfig,
        protected MachAddressConfig $machAddressConfig,
        protected Addressclient $addressClient,
        protected GetDateTime $dateTime,
        protected ResultFactory $resultFactory
    ) {
        $this->machConfig = $machConfig;
        parent::__construct($apiContext,$machConfig,$subscriberResource);
    }

    /**
     * @param DataObject $address
     * @param bool $onlyCounty
     *
     * @return array|bool
     * @throws \Exception
     *
     * ADDRESS_VERIFY - this will return an array of suggested addresses
     *
     * MACH Address Verification only works for US addresses
     *
     */
    public function addressVerify(DataObject $address, bool $onlyCounty = false)
    {
        try {
            $action = self::CALL_ADDRESS_VERIFY;
            $actionToCache = self::CALL_ADDRESS_VERIFY_COUNTY;
            $this->debugLog('Beginning ' . $action . ' Call');
            $status = $cacheUsed = false;

            if (!$this->healthCheck->isUp()) {
                return false;
            }

            $arguments = array(
                $this->_soapVar('MD', 'AddressVerifyService'),
                $this->_soapVar($this->getSecurityCode(), 'SecurityCode'),
                $this->_soapVar(1, 'ActionCode'),
            );

            $regionCode = $address->getRegionCode();
            if (!$regionCode && $address->getRegionId()) {
                $regionCode = $this->machAddressConfig->getRegionCodeById((int)$address->getRegionId());
            }

            $addressIn = array(
                $this->_soapVar($address->getStreet1(), 'AddressLine1'),
                $this->_soapVar($address->getStreet2(), 'AddressLine2'),
                $this->_soapVar($address->getCity(), 'City'),
                $this->_soapVar($regionCode, 'State'),
                $this->_soapVar($address->getZipcode(), 'ZipCode'),
            );

            $arguments[] = $this->_soapVar($addressIn, 'ADDRESS_IN');

            /** @var Result $response */
            $addrResult = $this->resultFactory->create();

            /** Implement Address Validation Result Caching -  Start Get Cache
             * There is no parameter to differentiate(for caching) County call and
             * the address validation call.
             * Only for caching purposes we change the action name, ONLY the name being saved into the cache.
             */
            /** @var array $sessionData */
            if ($onlyCounty) {
                $sessionData = $this->_getCachedResult($arguments, $actionToCache);
            } else {
                $sessionData = $this->_getCachedResult($arguments, $action);
            }

            if ($sessionData !== false) {
                $addrResult->setData($sessionData);
                $status = $cacheUsed = true;

                if ($onlyCounty) {
                    return $addrResult->getData();
                } else {
                    return $this->_addOpen1Data($sessionData);
                }
            }
            /** End Get Cache */
            $this->apiContext->setClient($this->addressClient);
            $response = $this->makeRequest($action, $arguments, SOAP_ENC_OBJECT);

            if (in_array($response->Status, self::VERIFICATION_CORRECT_RESPONSE_STATUS)) {
                $status   = true;
                $regionId = $this->machAddressConfig->getRegionIdByCode($response->ADDRESS_OUT->State);

                if (!$onlyCounty) {
                    //we now include the County value
                    $result = [
                        [
                            'street1'     => ucwords(strtolower($response->ADDRESS_OUT->Address1 ?? '')),
                            'street2'     => ucwords(strtolower($response->ADDRESS_OUT->Address2 ?? '')),
                            'city'        => ucwords(strtolower($response->ADDRESS_OUT->City ?? '')),
                            'region_id'   => $regionId,
                            'region_code' => $response->ADDRESS_OUT->State,
                            'zipcode'     => $response->ADDRESS_OUT->ZipBase,
                            'zipplusfour' => $response->ADDRESS_OUT->ZipPlusFour,
                            'county'      => $response->ADDRESS_OUT->County,
                            'country_id'  => $response->ADDRESS_OUT->Country,
                            'open1'        => $response->ADDRESS_OUT->Open1 ?? "",
                            'res_com_flag' => $response->ADDRESS_OUT->ResBusIndicator,

                            'ship_adv'      => true,
                            'ship_adv_date' => $this->dateTime->gmtDate(),
                            'ship_adv_dpi'  => $response->ADDRESS_OUT->DeliveryPointValidation ?? "",
                            'ship_adv_di'   => $response->ADDRESS_OUT->ResBusIndicator ?? "",
                        ],
                    ];
                    $addrResult->setData($result);

                    /** Set Address Validation Result Cache */
                    $this->_setCachedResult($arguments, $addrResult->getData(), $action);

                    //adding new elements to result
                    $result = $this->_addOpen1Data($result);

                } else {
                    //we now only include the County value
                    $result = ['county' => $response->ADDRESS_OUT->County];
                    $addrResult->setData($result);

                    /** Set County Validation Result Cache */
                    $this->_setCachedResult($arguments, $addrResult->getData(), $actionToCache);
                }

                return $result;

            } elseif ($response->ERROR_OUT->ErrorNumber == 0 && $response->Status == 'X' && $onlyCounty) {
                $status = true;

                //we now only include the County value
                $countyValidated = ['county' => $response->ADDRESS_OUT->County];
                $addrResult->setData($countyValidated);

                /** Set County Validation Result Cache */
                $this->_setCachedResult($arguments, $addrResult->getData(), $actionToCache);

                return $countyValidated;
            }

            return false;

        } catch (\Exception $e) {
            $this->debugLog($e->getMessage());
            throw $e;
        } finally {
            $message = array(
                'message'    => sprintf('Finished %s.  Result: %s', $action, ($status ? 'PASS' : 'FAIL')),
                'cache_used' => $this->_castBoolToString($cacheUsed, 'No', 'Yes'),
            );
            $this->debugLog($message);
            $this->debugLog('-------------------------------------------');
        }
    }

    /**
     * @param array $result
     * @return array
     */
    protected function _addOpen1Data(array $result): array
    {
        foreach ($result as $key => $response) {
            $open1Result = !empty($response["open1"]) ? $response["open1"] : "";
            $result[$key]["open1_valid"]   = $this->_isOpen1Valid($open1Result);
            $result[$key]["open1_message"] = $this->_getOpen1Message($open1Result);
        }

        return $result;
    }

    /**
     * @param string $open1Value
     * @return bool
     */
    protected function _isOpen1Valid(string $open1Value): bool
    {
        return $open1Value && $open1Value == self::CALL_ADDRESS_VERIFY_OPEN1_STE_APT_VALID_VALUE;
    }

    /**
     * @param string $open1Value
     * @return string
     */
    protected function _getOpen1Message(string $open1Value): string
    {
        return $open1Value && isset(self::CALL_ADDRESS_VERIFY_OPEN1_STE_APT_MESSAGE_VALUES[$open1Value])
            ?
            (string) self::CALL_ADDRESS_VERIFY_OPEN1_STE_APT_MESSAGE_VALUES[$open1Value]
            :
            "";
    }
}
