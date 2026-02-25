<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product;

use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\DataPersistence\Product\AbstractProduct;
use Magento\Framework\Exception\LocalizedException;

/**
 * Sync product from i95dev messagequeue to magento
 */
class Create extends AbstractProduct
{
    public const SKIPOBS = "i95_observer_skip";

    /**
     * @var string
     */
    public $sku;

    /**
     * @var object
     */
    public $productExtension = null;

    /**
     * @var object
     */
    public $result;

    /**
     * Create a product in Magento
     *
     * @param string $stringData
     * @param string $entityCode
     * @param string $erpCode
     * @return Object
     * @updatedBy Arushi Bansal
     */
    public function createProduct($stringData, $entityCode, $erpCode)
    {
        $this->setStringData($stringData);

        try {
            $this->sku = $this->dataHelper->getValueFromArray("sku", $this->stringData);

            /* Set basic details for a product - general product details */
            $this->setBasicDetails();
            $this->productInterface->setExtensionAttributes($this->productExtension);

            /* Observer that can be used before saving product in magento */
            $beforeeventname = 'erpconnect_messagequeuetomagento_beforesave_' . $entityCode;
            $this->eventManager->dispatch($beforeeventname, ['currentObject' => $this]);

            /* If product is new then only sync the stock/inventory in magento */
            if ($this->isNewItem) {
                $this->setStockInformation();
            }

            /* used by i95devteam in observer - observer code will execute specifically for i95dev only,
            if i95_observer_skip is true*/
            $this->dataHelper->unsetGlobalValue(self::SKIPOBS);
            $this->dataHelper->setGlobalValue(self::SKIPOBS, true);

            $this->result = $this->productRepo->create()->save($this->productInterface);

            $this->dataHelper->unsetGlobalValue(self::SKIPOBS);

            /* Observer that can be used after saving product in magento */
            $aftereventname = 'erpconnect_messagequeuetomagento_aftersave_' . $entityCode;
            $this->eventManager->dispatch($aftereventname, ['currentObject' => $this]);

            if (!is_object($this->result) && empty($this->result->getId())) {
                throw new LocalizedException(
                    __($this->result),
                    null,
                    105
                );
            }

            return $this->setResponse(
                Data::SUCCESS,
                "Record Successfully Synced",
                $this->result->getId()
            );
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                '__METHOD__',
                $erpCode . " :: " . $ex->getMessage(),
                LoggerInterface::I95EXC,
                'error'
            );

            return $this->setResponse(
                Data::ERROR,
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }
}
