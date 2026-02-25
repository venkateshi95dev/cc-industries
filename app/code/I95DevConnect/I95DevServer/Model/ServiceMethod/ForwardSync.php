<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Model\ServiceMethod;

use Exception;
use I95DevConnect\I95DevServer\Model\ServiceMethod\ForwardSync\MQToErp\SendEntityData;
use I95DevConnect\I95DevServer\Model\ServiceMethod\ForwardSync\MQToErp\SendEntityResponse;
use I95DevConnect\I95DevServer\Model\ServiceMethod\ForwardSync\MQToErp\SendIds;
use I95DevConnect\MessageQueue\Model\I95DevResponse;
use I95DevConnect\MessageQueue\Model\Logger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for Magento to ERP sync
 */
class ForwardSync extends AbstractServiceMethod
{
    public const STATUS = "status";
    public const ERROR = "Error";
    public const RES_DATA = "responseData";
    public const REQ_DATA = "requestData";
    public const IN_DATA = "inputData";

    /**
     * @var ForwardSync\MQToErp\SendIds
     */
    public $sendIds;
    /**
     * @var ForwardSync\MQToErp\SendEntityData
     */
    public $sendEntityData;
    /**
     * @var ForwardSync\MQToErp\SendEntityResponse
     */
    public $sendEntityResponse;

    /**
     * Constructor for DI
     *
     * @param Logger                $logger
     * @param I95DevResponse        $i95DevResponse
     * @param ScopeConfigInterface  $scopeConfigInterface
     * @param StoreManagerInterface $storeManager
     * @param SendIds               $sendIds
     * @param SendEntityData        $sendEntityData
     * @param SendEntityResponse    $sendEntityResponse
     */
    public function __construct(
        Logger $logger,
        I95DevResponse $i95DevResponse,
        ScopeConfigInterface $scopeConfigInterface,
        StoreManagerInterface $storeManager,
        SendIds $sendIds,
        SendEntityData $sendEntityData,
        SendEntityResponse $sendEntityResponse
    ) {

        $this->sendIds = $sendIds;
        $this->sendEntityData = $sendEntityData;
        $this->sendEntityResponse = $sendEntityResponse;

        parent::__construct($logger, $i95DevResponse, $scopeConfigInterface, $storeManager);
    }

    /**
     * Method to get collection from Magento and send to ERP
     *
     * @param  string      $entityCode
     * @param  string      $dataString
     * @param  string|null $erpName
     * @return Object
     * @throws Exception
     */
    public function sendEntityData($entityCode, $dataString, $erpName = null)
    {
        try {
            $finalRequest = $this->convertInputStringToArray($dataString);
            $data = $this->sendIds($entityCode, $finalRequest);

            if (!empty($data)) {
                $responseData = $this->sendEntityData->getEntityData($entityCode, $data, $erpName);

                if (isset($responseData[self::STATUS])) {
                    $this->setResponse(
                        $responseData[self::STATUS],
                        $responseData['message'],
                        $responseData[self::RES_DATA]
                    );
                }
            } else {
                $this->setResponse("false", "No data Exist for sync");
            }
        } catch (LocalizedException $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }

        return $this->i95DevResponse;
    }

    /**
     * Send response to ERP
     *
     * @param  string $entityCode
     * @param  string $dataString
     * @param  string $erpName
     * @return I95DevResponse $i95Devresponse
     * @throws LocalizedException
     * @throws Exception
     */
    public function sendEntityResponse($entityCode, $dataString, $erpName = null)
    {
        try {
            $finalRequest = $this->convertInputStringToArray($dataString);

            if (isset($finalRequest[self::REQ_DATA])) {
                foreach ($finalRequest[self::REQ_DATA] as $key => $requestData) {
                    if (isset($requestData[self::IN_DATA])) {
                        $finalRequest[self::REQ_DATA][$key][self::IN_DATA] = $this->convertInputStringToArray(
                            $this->decryptDES($requestData[self::IN_DATA])
                        );
                    }
                }
            }

            if (isset($finalRequest[self::REQ_DATA])) {
                $responseData = $this->sendEntityResponse->getEntityResponse(
                    $entityCode,
                    $finalRequest[self::REQ_DATA],
                    $erpName
                );

                $this->processResponseData($responseData);
            } else {
                $this->setResponse("false", "No data exists in response data");
            }
        } catch (LocalizedException $ex) {
            throw new LocalizedException(__($ex->getMessage()));
        }

        return $this->i95DevResponse;
    }

    /**
     * Process response data
     *
     * @param array $responseData
     */
    public function processResponseData($responseData)
    {
        if (isset($responseData[self::STATUS])) {
            foreach ($responseData[self::RES_DATA] as $key => $requestData) {
                if (isset($requestData[self::IN_DATA])) {
                    $preparedata['addressess'] = $requestData[self::IN_DATA];
                    $inputData = $this->encryptAES(json_encode($preparedata));
                    $responseData[self::RES_DATA][$key][self::IN_DATA] = $inputData;
                }
            }

            $this->setResponse(
                $responseData[self::STATUS],
                $responseData['message'],
                $responseData[self::RES_DATA]
            );
        }
    }

    /**
     * Send id list available in outbound MQ
     *
     * @param  string $entityCode
     * @param  array  $requestData
     * @return array
     * @throws LocalizedException
     * @throws Exception
     */
    public function sendIds($entityCode, $requestData = null)
    {
        try {
            $updatedIdList = $this->sendIds->defaultUpdatedEntityIds($entityCode, $requestData);
        } catch (LocalizedException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return $updatedIdList;
    }
}
