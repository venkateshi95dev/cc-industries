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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Uploads;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Config as ConfigModel;
use Ebizcharge\Ebizcharge\Model\Customer as CustomerModel;
use Ebizcharge\Ebizcharge\Model\ShellCommand;
use Ebizcharge\Ebizcharge\Model\SyncAssetsFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\LayoutFactory;

/**
 * Upload Latest Resources action class
 *
 * Class UploadLatestResources
 */
class UploadLatestResources extends Action
{
    /**
     * @var CustomerModel
     */
    protected CustomerModel $_customerModel;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var ShellCommand
     */
    protected ShellCommand $_shellCommand;

    /**
     * @var ConfigModel
     */
    protected ConfigModel $_configModel;

    /**
     * @var SyncAssetsFactory
     */
    protected SyncAssetsFactory $_syncAssetsFactory;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $_jsonFactory;

    /**
     * @param Context $context
     * @param CustomerModel $customerModel
     * @param EbizchargeLogger $ebizchargeLogger
     * @param ConfigModel $configModel
     * @param ShellCommand $shellCommand
     * @param SyncAssetsFactory $syncAssetsFactory
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        CustomerModel $customerModel,
        EbizchargeLogger $ebizchargeLogger,
        ConfigModel $configModel,
        ShellCommand $shellCommand,
        SyncAssetsFactory $syncAssetsFactory,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);

        /** @var configModel */
        $this->_configModel = $configModel;
        /** @var customerModel */
        $this->_customerModel = $customerModel;
        /** @var shellCommand */
        $this->_shellCommand = $shellCommand;
        /** @var ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var syncAssetsFactory */
        $this->_syncAssetsFactory = $syncAssetsFactory;

        /** @var jsonFactory */
        $this->_jsonFactory = $jsonFactory;
    }

    /**
     * Execute Methods
     *
     * @return false|ResponseInterface|Json|ResultInterface|void
     */
    public function execute()
    {
        /** @var  $params */
        $assetAction = $this->getRequest()->getParam('upload_request');

        /** assets Action Request */
        if (!isset($assetAction)) {
            $this->_ebizchargeLogger->addError(__("Error occured assets request is not set"));
            return false;
        }

        /** @var $jsonFactory */
        $response = [];
        $jsonFactory = $this->_jsonFactory->create();

        try {
            /** Sync Assets Switch */
            switch ($assetAction) {

                /** Asset Request download customers */
                case 'upload_customers':
                    $this->_ebizchargeLogger->addInfo(__("Uploading customers to EBizCharge Hub"));
                    $downloadCustomersProcess = $this->_customerModel->processUploadCustomersToEbizcharge();

                    /** process ran successfully */
                    if ($downloadCustomersProcess) {
                        $response = [
                            'message' => __("Success process started successfully"),
                            'error' => false,
                            'success' => true,
                        ];
                    }
                    $this->_ebizchargeLogger->addInfo(__($response['message']));

                    /** creating JSON Factory */
                    return $jsonFactory->setData(json_encode($response));
                    //break;

                /** Assets Request is uploading Products */
                case 'upload_products':
                    $this->_ebizchargeLogger->addInfo(__("Uploading products started to EBizCharge Hub"));
                    try {
                        $uploadItemsShellCommand = ShellCommand::SHELL_COMMAND_PROCESS_UPLOAD_PRODUCTS;

                        /** run shell command  */
                        $result = $this->_shellCommand->run($uploadItemsShellCommand);
                        /** process ran successfully */
                        $response = [
                            'message' => __("Success process started successfully"),
                            'error' => false,
                            'success' => true,
                        ];
                    } catch (\Exception $e) {
                        $response = [
                            'message' => $e->getMessage(),
                            'error' => true,
                            'success' => false,
                        ];
                    }

                    $this->_ebizchargeLogger->addInfo(__($response['message']));

                    /** creating JSON Factory */
                    return $jsonFactory->setData(json_encode($response));
                    //break;

                /** Assets Download Products */
                case 'upload_orders':
                    $this->_ebizchargeLogger->addInfo(__("Uploading orders from EBizCharge Hub"));
                    try {
                        $uploadOrdersShellCommand = ShellCommand::SHELL_COMMAND_PROCESS_UPLOAD_ORDERS;

                        /** run shell command  */
                        $result = $this->_shellCommand->run($uploadOrdersShellCommand);
                        /** process ran successfully */
                        $response = [
                            'message' => __("Success process started successfully"),
                            'error' => false,
                            'success' => true,
                        ];

                    } catch (\Exception $e) {
                        $response = [
                            'message' => $e->getMessage(),
                            'error' => true,
                            'success' => false,
                        ];

                    }

                    $this->_ebizchargeLogger->addInfo(__($response['message']));

                    /** creating JSON Factory */
                    return $jsonFactory->setData(json_encode($response));
                    //break;
            }
        } catch (LocalizedException $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occurred during running process Error:" . $exception->getMessage()
            ));
            $response = [
                'message' => __("Error occurred during running process Error: " . $exception->getMessage()),
                'error' => true,
                'success' => false
            ];
            return false;
        }
    }
}
