<?php

namespace Crimson\MachAddressVerification\Model\Mach;

use Crimson\MachAddressVerification\Model\Api\AddressVerification;
use Crimson\MachAddressVerification\Model\Config as MachAddressConfig;
use Crimson\MachBase\Model\Api\HealthCheck;
use IWD\AddressValidation\Helper\Data;
use IWD\AddressValidation\Model\AbstractValidation;
use IWD\AddressValidation\Model\Google\Validation as GoogleValidation;
use IWD\AddressValidation\Model\Ups\Validation as UpsValidation;
use IWD\AddressValidation\Model\Usps\Validation as UspsValidation;
use IWD\AddressValidation\Model\Validation\Address;
use IWD\AddressValidation\Model\Validation\Response;
use IWD\AddressValidation\Model\Validation\Validator;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime as GetDateTime;

/**
 * Class Validation
 * @package Crimson\MachAddressVerification\Model\Mach
 */
class Validation extends AbstractValidation
{

    protected $notSkippedCountries = ['us'];

    protected $upsSkipRegions = ['virgin islands', 'puerto rico', 'guam'];

    CONST SERVICE_SKIP_US_ATYPICAL_REGIONS = 'ups';
    CONST LOCAL_COUNTRY_CODES = ['US', 'CA'];

    /** @var MachAddressConfig $machAddrConfig */
    protected $machAddrConfig;

    /**
     * @var AddressVerification
     */
    protected $addressVerification;

    /**
     * @var HealthCheck
     */
    protected $healthCheck;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var UpsValidation
     */
    private $upsValidator;

    /**
     * @var UspsValidation
     */
    private $uspsValidator;

    /**
     * @var GoogleValidation
     */
    private $googleValidator;

    /**
     * @var GetDateTime
     */
    protected $dateTime;

    public function __construct(
        MachAddressConfig $machAddrConfig,
        AddressVerification $addressVerification,
        HealthCheck $healthCheck,
        Validator $validator,
        UpsValidation $upsValidator,
        UspsValidation $uspsValidator,
        GoogleValidation $googleValidator,
        Context $context,
        Registry $registry,
        Data $helper,
        Address $address,
        Response $response,
        GetDateTime $dateTime,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $helper, $address, $response, $resource, $resourceCollection, $data);

        $this->healthCheck = $healthCheck;
        $this->machAddrConfig  = $machAddrConfig;
        $this->addressVerification = $addressVerification;
        $this->validator = $validator;
        $this->googleValidator = $googleValidator;
        $this->upsValidator = $upsValidator;
        $this->uspsValidator = $uspsValidator;
        $this->dateTime = $dateTime;
        $this->helper = $helper;
    }

    /**
     * @return mixed|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function validateAddress()
    {
        $isUsOrCaCountry = $this->isCountryUsOrCa();
        if (is_null($isUsOrCaCountry)) {
            /**
             * No country on the address to validate
             */
            $this->response->setIsValid(false);
            return;
        }

        if ($isUsOrCaCountry && $this->getEnable() && $this->healthCheck->isUp()) {
            $addressForCheck      = $this->getAddressForValidation()->getData();
            $addressReadyToVerify = $this->prepareAddressToVerify($addressForCheck);
            $response             = $this->addressVerification->addressVerify($addressReadyToVerify);

            $this->getMachCandidates($response);
            $this->response->setData('mode', MachAddressConfig::MACH_ADDRESS_VALIDATION_CODE);
        } else {
            /**
             * Mach DOWN || International Address: We call the method selected on IWD drop-down, we cannot
             * call getValidator() because we are plugging it and if Mach is enabled
             * it is gonna return 'mach' but we know it is down so, the option on the IWD drop-down
             * is the one.
             * The Google service is the one being used.
             */
            $mode = (string)$this->helper->getValidationMode();
            $this->validator->setValidationMode($mode);
            $validatorService = $this->getValidatorByMode($mode);
            if ($validatorService) {
                $validatorService->validateAddress();
                $this->response->setData('mode', $mode);
                $this->response->setData('international_address', !$isUsOrCaCountry);
            }
        }
    }

    /**
     * @param string $mode
     *
     * @return bool
     */
    private function skipUpsUsAtypicalRegions(string $mode) :bool
    {
        $addressData = $this->getAddressForValidation();
        if (isset($addressData['region'])
            && in_array(strtolower($addressData['region']), $this->upsSkipRegions)
            && $mode == self::SERVICE_SKIP_US_ATYPICAL_REGIONS
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool|null
     */
    private function isCountryUsOrCa(): ?bool
    {
        $addressData = $this->getAddressForValidation();
        if (empty($addressData['country_id'])) {
            return null;
        }

        if (in_array($addressData['country_id'], self::LOCAL_COUNTRY_CODES)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function skipCountry() :bool
    {
        $addressData = $this->getAddressForValidation();
        if (isset($addressData['country_id'])
            && !in_array(strtolower($addressData['country_id']), $this->notSkippedCountries)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $mode
     *
     * @return GoogleValidation|UpsValidation|UspsValidation
     * @throws LocalizedException
     */
    public function getValidatorByMode(string $mode)
    {
        switch ($mode) {
            case 'ups':
                return $this->upsValidator;
            case 'usps':
                return $this->uspsValidator;
            case 'google':
                return $this->googleValidator;
            default:
                throw new LocalizedException(__('Validation mode <' . $mode . '> is not supported'));
        }
    }

    /**
     * @param $response
     *
     * @throws NoSuchEntityException
     */
    public function getMachCandidates($response)
    {
        if (!$response) {
            $this->response->setIsValid(false);
            $this->response->getOriginalAddress()->addData($this->_getFailureResponseFields());

            return;
        }

        if (is_array($response)) {
            foreach ($response as $result) {
                $street = $result['street1'];

                if ($result['street2']) {
                    $street .= ' ' . $result['street2'];
                }

                $regionId = $result['region_id'];

                if (!empty($result['region_code'])) {
                    $regionCode = $result['region_code'];
                } else {
                    $regionCode = $this->machAddrConfig->getRegionCodeById((int)$regionId);
                }

                $zipCode = $result['zipcode'];
                if (!empty($result['zipplusfour'])) {
                    $zipCode .= '-' . $result['zipplusfour'];
                }

                $open1Valid   = !empty($result['open1_valid']) ? $result['open1_valid'] : false;
                $open1Message = !empty($result['open1_message']) ? $result['open1_message'] : "";

                //if we have at least 1 Open1 invalid address add this flag to be processed on the template/modal
                if (!$open1Valid) {
                    $this->response->setData('missing_part', true);
                }

                $address = clone $this->getAddressForValidation();
                $address->setData([]);
                $address->setStreet($street);
                $address->setCity($result['city']);
                $address->setRegionId($regionId);
                $address->setRegionCode($regionCode);
                $address->setCountryId($result['country_id']);
                $address->setPostcode($zipCode);
                $address->setOpen1Valid($open1Valid);
                $address->setOpen1Message($open1Message);

                // Address Validation Extra Fields
                $advStatus  = !empty($result['ship_adv']) ? (bool) $result['ship_adv'] : false;
                $advDate    = !empty($result['ship_adv_date']) ? $result['ship_adv_date'] : "";
                $advDpi     = !empty($result['ship_adv_dpi']) ? $result['ship_adv_dpi'] : "";
                $advDi      = !empty($result['ship_adv_di']) ? $result['ship_adv_di'] : "";
                $address->setShipAdv($advStatus);
                $address->setShipAdvDate($advDate);
                $address->setShipAdvDpi($advDpi);
                $address->setShipAdvDi($advDi);

                //Adding suggested address
                $this->addSuggestedAddress($address);
            }
        }
    }

    /**
     * @return array
     */
    protected function _getFailureResponseFields(): array
    {
        return [
            "ship_adv"      => false,
            "ship_adv_date" => $this->dateTime->gmtDate(),
            "ship_adv_dpi"  => "X",
            "ship_adv_di"   => "U",
        ];
    }

    /**
     * @param array $address
     *
     * * The array values for Mach should be like this:
     *
     * array(
     *     'street_1'    => $address->getStreet1(),
     *     'street_2'    => $address->getStreet2(),
     *     'city'       => $address->getCity(),
     *     'zipcode'    => $address->getPostcode(),
     *     'region_id'  => $address->getRegionId(),
     *     'country_id' => $address->getCountryId(),
     * )
     *
     * - Street value and Zip Code value are required to this call.
     *
     * @return DataObject
     */
    public function prepareAddressToVerify(array $address): DataObject
    {
        $addressData = new DataObject ($address);
        $streets = [];
        if (!empty($addressData['street'])) {

            if (is_array($addressData['street'])) {
                $streets = $addressData['street'];
            } else {
                $streets = explode(PHP_EOL, $addressData->getStreet());
            }
        }

        return new DataObject(
            [
                'street_1'  => ucwords(strtolower(trim($streets[0] ?? ''))),
                'street_2'  => ucwords(strtolower(trim($streets[1] ?? ''))),
                'city'      => ucwords(strtolower(trim($addressData->getCity() ?? ''))),
                'region_id' => $addressData->getRegionId(),
                'zipcode'   => substr(trim($addressData->getPostcode() ?? ''), 0, 5),
            ]
        );
    }

    public function getEnable(): bool
    {
        return $this->machAddrConfig->isMachAddrValidationEnabled();
    }

    /**
     * @param Address $address
     */
    public function addSuggestedAddress($address)
    {
        $address->updateRegionData();
        $isEqual = $address->isEqualWithAddress($this->getAddressForValidation());
        if (!$isEqual) {
            if (!$this->isSuggestedAddressAdded($address)) {
                $this->response->addSuggestedAddress($address);
            }
        } else {
            $this->response->setIsValid(true);
            $originalAddress = $this->response->getOriginalAddress();
            $originalAddress->setShipAdv($address->getShipAdv());
            $originalAddress->setShipAdvDate($address->getShipAdvDate());
            $originalAddress->setShipAdvDpi($address->getShipAdvDpi());
            $originalAddress->setShipAdvDi($address->getShipAdvDi());
        }
    }
    /**
     * @param $address
     * @return bool
     */
    private function isSuggestedAddressAdded($address): bool
    {
        $suggestedAddresses = $this->response->getSuggestedAddresses();
        foreach ($suggestedAddresses as $suggestedAddress) {
            if ($suggestedAddress->isEqualWithAddress($address)) {
                return true;
            }
        }
        return false;
    }

}
