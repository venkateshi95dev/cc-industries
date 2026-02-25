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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Downloads;

use Ebizcharge\Ebizcharge\Api\Data\SoapApiModelInterface;
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
use Magento\Framework\View\Result\LayoutFactory;

/**
 * Check Latest Resources for Download action
 *
 * Class CheckLatestResources
 */
class CheckLatestResources extends Action
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
     * @var LayoutFactory
     */
    protected LayoutFactory $resultLayoutFactory;

    /**
     * CheckLatestResources constructor.
     *
     * @param Context $context
     * @param LayoutFactory $resultLayoutFactory
     * @param CustomerModel $customerModel
     * @param EbizchargeLogger $ebizchargeLogger
     * @param ConfigModel $configModel
     * @param ShellCommand $shellCommand
     * @param SyncAssetsFactory $syncAssetsFactory
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        LayoutFactory $resultLayoutFactory,
        CustomerModel $customerModel,
        EbizchargeLogger $ebizchargeLogger,
        ConfigModel $configModel,
        ShellCommand $shellCommand,
        SyncAssetsFactory $syncAssetsFactory,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);

        /** @var resultLayoutFactory */
        $this->resultLayoutFactory = $resultLayoutFactory;
        /** @var _configModel */
        $this->_configModel = $configModel;
        /** @var _customerModel */
        $this->_customerModel = $customerModel;
        /** @var _shellCommand */
        $this->_shellCommand = $shellCommand;
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _syncAssetsFactory */
        $this->_syncAssetsFactory = $syncAssetsFactory;

        /** @var _jsonFactory */
        $this->_jsonFactory = $jsonFactory;
    }

    /**
     * Execute Methods
     *
     * @return false|ResponseInterface|Json|ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        /** @var  $params */
        $assetAction = $this->getRequest()->getParam('assets');

        /** assets Action Request */
        if (!isset($assetAction)) {
            $this->_ebizchargeLogger->addError(__("Error: Sync assets request has not been sent."));
            return false;
        }
        /** @var $jsonFactory */
        $jsonFactory = $this->_jsonFactory->create();

        /** Sync Assets Switch */
        switch ($assetAction) {

            /** Asset Request is customers */
            case SoapApiModelInterface::EBIZCHARGE_SYNC_ASSETS_DOWNLAOD_CUSTOMERS:
                $this->_ebizchargeLogger->addInfo(__("Checking new customers to import from EBizCharge Gateway"));
                $syncCustomersData = $this->_syncAssetsFactory->create()
                    ->checkLatestCustomersAtEbizcharge();
                $this->_ebizchargeLogger->addInfo(__($syncCustomersData['msg']));
                /** creating JSON Factory */
                return $jsonFactory->setData(json_encode($syncCustomersData));
                //break;
            /** Assets request is Items */
            case SoapApiModelInterface::EBIZCHARGE_SYNC_ASSETS_DOWNLAOD_PRODUCTS:
                $this->_ebizchargeLogger->addInfo(__("Checking new products|items to import from EBizCharge Gateway"));
                $syncProductsData = $this->_syncAssetsFactory->create()
                    ->checkLatestProductsAtEbizcharge();
                /** creating JSON Factory */
                return $jsonFactory->setData(json_encode($syncProductsData));
                //break;
            /** Assets request is orders */
            case SoapApiModelInterface::EBIZCHARGE_SYNC_ASSETS_DOWNLAOD_ORDERS:
                $this->_ebizchargeLogger->addInfo(__("Checking new orders to import from EBizCharge Gateway"));

                $syncOrdersData = $this->_syncAssetsFactory->create()
                    ->checkLatestOrdersAtEbizcharge();
                /** creating JSON Factory */
                return $jsonFactory->setData(json_encode($syncOrdersData));
                //break;
        }
    }
}
