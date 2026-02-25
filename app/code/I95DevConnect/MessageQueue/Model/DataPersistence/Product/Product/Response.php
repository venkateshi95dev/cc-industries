<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product;

use I95DevConnect\MessageQueue\Api\Data\I95DevMagMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use Magento\Catalog\Model\ProductRepositoryFactory;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class responsible for saving erp responses in product
 */
class Response
{
    /**
     *
     * @var LoggerInterface
     */
    public $logger;

    /**
     *
     * @var Data
     */
    public $dataHelper;

    /**
     *
     * @var Manager
     */
    public $eventManager;

    /**
     *
     * @var I95DevMagMQRepositoryInterfaceFactory
     */
    public $I95DevMagMQRepository;

    /**
     *
     * @var I95DevMagMQInterfaceFactory
     */
    public $I95DevMagMQData;

    /**
     *
     * @var Object
     */
    public $product = null;

    /**
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;

    /**
     * @var ProductRepositoryFactory
     */
    public $productRepo;

    /**
     * @var int
     */
    public $productId;

    /**
     * @var string
     */
    public $erpCode;

    public const SKIPOBRVR = "i95_observer_skip";

    /**
     *
     * @param LoggerInterface $logger
     * @param Data $dataHelper
     * @param I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQRepository
     * @param I95DevMagMQInterfaceFactory $I95DevMagMQData
     * @param Manager $eventManager
     * @param ProductRepositoryFactory $productRepo
     * @param AbstractDataPersistence $abstractDataPersistence
     */
    public function __construct(
        LoggerInterface $logger,
        Data $dataHelper,
        I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQRepository,
        I95DevMagMQInterfaceFactory $I95DevMagMQData,
        Manager $eventManager,
        ProductRepositoryFactory $productRepo,
        AbstractDataPersistence $abstractDataPersistence
    ) {
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->I95DevMagMQRepository = $I95DevMagMQRepository;
        $this->I95DevMagMQData = $I95DevMagMQData;
        $this->eventManager = $eventManager;
        $this->productRepo = $productRepo;
        $this->abstractDataPersistence = $abstractDataPersistence;
    }

    /**
     * Sets target product details in product
     *
     * @param array $requestData
     * @return AbstractDataPersistence
     * @author Hrusieksh Manna
     */
    public function setProductResponse($requestData)
    {
        try {
            /** @updatedBy vinayakrao shetkar. Changed targetId to sourceId
             * validate Data with productId instead of sku **/
            $this->productId = $this->dataHelper->getValueFromArray("sourceId", $requestData);
            if ($this->validateData()) {
                $this->erpCode = isset($requestData['erp_name']) ? $requestData['erp_name'] : __("ERP");
                $productResponseBeforeEvent = "erpconnect_forward_product_beforeresponse";
                $this->eventManager->dispatch($productResponseBeforeEvent, ['currentObject' => $this]);
                $this->dataHelper->unsetGlobalValue(self::SKIPOBRVR);
                $this->dataHelper->setGlobalValue(self::SKIPOBRVR, true);
                $result = $this->erpUpdatesForProduct();
                $this->dataHelper->unsetGlobalValue(self::SKIPOBRVR);
                $productResponseAfterEvent = "erpconnect_forward_product_afterresponse";
                $this->eventManager->dispatch($productResponseAfterEvent, ['currentObject' => $this]);
                return $result;
            } else {
                return $this->abstractDataPersistence->setResponse(
                    Data::ERROR,
                    __("Product response is invalid to sync"),
                    null,
                    105
                );
            }
        } catch (LocalizedException $ex) {
            /** @updatedBy Debashis S. Gopal. Returning false instead of throwing exception,
             * as expected by the calling function. **/
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );

            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Validate response data from ERP
     *
     * @return boolean
     * @throws LocalizedException
     * @updatedBy Debashis S. Gopal. Replaced Undefined variable with $this->productRepo. And optimized code.
     */
    public function validateData()
    {
        try {
            $this->product = $this->productRepo->create()->getById($this->productId);

            if ($this->product->getId()) {
                return true;
            } else {
                return $this->abstractDataPersistence->setResponse(
                    Data::ERROR,
                    __("Product Not Exist With Id " . $this->productId),
                    null,
                    108
                );
            }
        } catch (NoSuchEntityException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                108
            );
        }
    }

    /**
     * Update product with with ERP Data
     *
     * @updatedBy Debashis S. Gopal. Removed unnecessary new product creation
     *
     * Instead initialized in validate method. And optimized the code
     *
     * @throws LocalizedException
     */
    public function erpUpdatesForProduct()
    {
        try {
            $this->product->setCustomAttribute("targetproductstatus", Data::SYNCED)
                    ->setCustomAttribute("update_by", $this->erpCode);
            $result = $this->productRepo->create()->save($this->product);
            if ($result->getId()) {
                return $this->abstractDataPersistence->setResponse(
                    Data::SUCCESS,
                    __("Response send successfully")
                );
            } else {
                return $this->abstractDataPersistence->setResponse(
                    Data::ERROR,
                    __("Some error occured in response sync"),
                    null,
                    105
                );
            }
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }
}
