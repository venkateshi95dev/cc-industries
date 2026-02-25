<?php
/**
 * @namespace   Crimson
 * @module      MachGiftCard
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 5:14 PM
 * @brief
 */

namespace Crimson\MachGiftCard\Model\Api;

use Crimson\MachBase\Model\Api\AbstractApi;
use Crimson\MachBase\Model\Api\ApiContext;
use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachGiftCard\Api\Data\MachGiftCardInterface;
use Crimson\MachGiftCard\Api\Data\MachGiftCardInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime;
use Magento\GiftCardAccount\Model\Giftcardaccount;
use Magento\Newsletter\Model\ResourceModel\Subscriber;

/**
 * Class GiftCardInfo
 * @package Crimson\MachGiftCard\Model\Api
 */
class GiftCardInfo extends AbstractApi
{
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var HealthCheck
     */
    protected $healthCheck;
    /**
     * @var MachGiftCardInterfaceFactory
     */
    protected $machGiftCardFactory;

    public function __construct(
        ApiContext $apiContext,
        MachConfig $machConfig,
        MachGiftCardInterfaceFactory $machGiftCardFactory,
        Subscriber $subscriberResource,
        HealthCheck $healthCheck,
        DataObjectHelper $dataObjectHelper
    ) {
        parent::__construct($apiContext, $machConfig, $subscriberResource);
        $this->healthCheck         = $healthCheck;
        $this->dataObjectHelper    = $dataObjectHelper;
        $this->machGiftCardFactory = $machGiftCardFactory;
    }

    /**
     * @param string $giftcardCode
     * @return MachGiftCardInterface|null
     */
    public function get(string $giftcardCode): ?MachGiftCardInterface
    {
        $action = self::CALL_GC_INFO;
        $this->debugLog(__('Beginning %1 Call for %2', $action, $giftcardCode));
        $data = [];
        //load customer if not loaded.
        try {
            $actionCode = 1;
            $arguments  = [
                $this->_soapVar($this->getSecurityCode(), 'SecurityCode'),
                $this->_soapVar($actionCode, 'ActionCode'),
                $this->_soapVar($giftcardCode, 'GCNumberIn'),
            ];
            $response   = $this->makeRequest($action, $arguments);
            if (!isset($response->ERROR_OUT->ErrorNumber)) {
                throw new LocalizedException(__('Invalid response received, unable to determine product price.'));
            } else {
                $errorNumber  = $response->ERROR_OUT->ErrorNumber;
                $errorMessage = $response->ERROR_OUT->ErrorMsg;
                if (!$this->_isSuccess($action, $errorNumber)) {
                    $message = 'Error occurred attempting to retrieve giftcard information from MACH ERP.<br/>';
                    $message .= 'Error Number: %1. Returned Message: %2 <br/>';
                    $message = __($message, $errorNumber, $errorMessage);
                    $this->infoLog($message);

                    return null;
                } else {
                    $giftcardData = $response->GC_INFO_OUT;

                    $data = [
                        'code'        => $giftcardCode,
                        'issue_date'  => $this->_convertDateToInternalFormat($giftcardData->GCIssueDate),
                        //just changed to null, for ZIP GCs don't expire
                        'issued_to'   => null,
                        //'issued_to'   => (string) $giftcardData->GCIssuedTo, This was returning the name of the peron assigned
                        //'date_expires' => date_timestamp_get(date_create($giftcardData->GCIssuedTo)),
                        'status'      => $giftcardData->GCStatus == 'Y'
                            ? Giftcardaccount::STATUS_ENABLED
                            : Giftcardaccount::STATUS_DISABLED,
                        'state' => $giftcardData->GCStatus == 'Y'
                            ? Giftcardaccount::STATE_AVAILABLE
                            : Giftcardaccount::STATE_EXPIRED,
                        'amount'      => (float) $giftcardData->GCIssueAmt,
                        'open_amount' => (float) $giftcardData->GCOpenAmt,
                        'balance'     => (float) $giftcardData->GCOpenAmt,
                    ];

                    $machGiftCard = $this->machGiftCardFactory->create();
                    $this->dataObjectHelper->populateWithArray($machGiftCard, $data, MachGiftCardInterface::class);

                    //save to session so we don't have to repeat the same api calls.
                    $this->_setCachedResult($arguments, $data, $action);

                    $this->debugLog($data);

                    return $machGiftCard;
                }
            }
        } catch (LocalizedException $e) {
            $this->infoLog($e);
        } catch (\Exception $e) {
            $this->criticalLog($e);
        }

        return null;
    }

    /**
     * @param string|null $machDateString
     * @return string|null
     */
    protected function _convertDateToInternalFormat(?string $machDateString): ?string
    {
        try {
            if (!$machDateString) {
                return null;
            }

            return date(DateTime::DATETIME_PHP_FORMAT, strtotime($machDateString));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param string|null $action
     *
     * @return int - minutes
     */
    protected function _getMaxRequestAge(string $action = null): int
    {
        if ($action === null || $action === self::CALL_GC_INFO) {
            return 1;
        }

        return parent::_getMaxRequestAge($action);
    }
}
