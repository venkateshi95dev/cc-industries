<?php

/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\System\Config;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Ebizcharge\Ebizcharge\Model\TranApiFactory as SoapApiFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use SoapFault;

/**
 * System Config Validate Merchant Api
 *
 * Class ValidateMerchantApi
 */
class ValidateMerchantApi extends Action
{
    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;
    /**
     * @var ConfigFactory
     */
    protected ConfigFactory $configFactory;
    /**
     * @var JsonFactory
     */
    protected JsonFactory $jsonFactory;
    /**
     * @var SoapApiFactory
     */
    protected SoapApiFactory $soapApiFactory;
    /**
     * @var EncryptorInterface
     */
    protected EncryptorInterface $encryptor;

    /**
     * Main Constructor of the Class
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ConfigFactory $configFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param SoapApiFactory $soapApiFactory
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ConfigFactory $configFactory,
        EbizchargeLogger $ebizchargeLogger,
        SoapApiFactory $soapApiFactory,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);

        /** @var  $jsonFactory */
        $this->jsonFactory = $resultJsonFactory;
        /** @var  $ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var  $configFactory */
        $this->configFactory = $configFactory;
        /** @var  $soapApiFactory */
        $this->soapApiFactory = $soapApiFactory;
        /** @var $encryptor */
        $this->encryptor = $encryptor;
    }

    /**
     * Execute Method
     *
     * @return array|false|ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        /** @var $isMerchantValidated */
        $jsonResponse = [
            'error' => true,
            'valid' => false,
            'msg' => __("Could not found valid merchant.")
        ];
        /** the JSON Factory $result */
        $resultJsonFactory = $this->jsonFactory->create();
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        /** @var $requestParams */
        $requestParams = $this->getRequest()->getParams();

        try {
            $isRawInput = true;
            /** if request has merchant key */
            if (!isset($requestParams['merchant_key'])) {
                $this->ebizchargeLogger->addInfo($jsonResponse["msg"]);
                return $jsonResponse;
            }
            if ($configFactory->hasPostFix($this->encryptor->decrypt($requestParams["merchant_key"]))) {
                 $requestParams["merchant_key"] = $configFactory->decryptWithPostFix($requestParams["merchant_key"]);
                 $requestParams["merchant_id"] = $configFactory->decryptWithPostFix($requestParams["merchant_id"]);
                 $requestParams["merchant_pin"] = $configFactory->decryptWithPostFix($requestParams["merchant_pin"]);
            }
            /** @var  $merchantValidation */
            $merchantValidation = $this->soapApiFactory->create()->validateMerchantAPICredentials($requestParams);

            if ($merchantValidation) {
                $this->ebizchargeLogger->addInfo(__("Success the merchant is validated"));
                /** @var Json $result */
                $jsonResponse = [
                    'error' => false,
                    'valid' => true,
                    'msg' => __("Success the merchant is validated.")
                ];
            } else {
                $jsonResponse = [
                    'error' => true,
                    'valid' => false,
                    'msg' => __("Merchant is not validated with provided keys")
                ];
                $this->ebizchargeLogger->addInfo(__("Merchant is not validated"));
            }
        } catch (SoapFault $soapFault) {
            /** logging the critical errors */
            $this->ebizchargeLogger->critical($soapFault->getMessage());
            $jsonResponse = [
                'error' => true,
                'valid' => false,
                'msg' => __("SOAP Error occurred during validation. : " . $soapFault->getMessage())
            ];
        }
        return $resultJsonFactory->setData($jsonResponse);
    }

    /**
     * Is Allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ebizcharge_Ebizcharge::rec');
    }
}
