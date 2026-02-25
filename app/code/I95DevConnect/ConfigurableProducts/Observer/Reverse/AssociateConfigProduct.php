<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_MessageQueue
 * @createdBy vinayakrao.shetkar
 */

namespace I95DevConnect\ConfigurableProducts\Observer\Reverse;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\ConfigurableProduct\Api\OptionRepositoryInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Observer class for sales order invoice before save
 */
class AssociateConfigProduct implements ObserverInterface
{
    /**
     * @var LinkManagementInterface
     */
    public $linkManager;

    /**
     * @var ProductRepositoryInterface
     */
    public $productRepo;

    /**
     * @var Configurable
     */
    public $configurableType;

    /**
     * @var OptionRepositoryInterface
     */
    public $optionRepo;

    public const PARENTSKU = "parentSku";

    /**
     * @param  LinkManagementInterface $linkManager
     * @param  ProductRepositoryInterface $productRepo
     * @param  OptionRepositoryInterface $optionRepo
     * @param  Configurable $configurableType
     * @author Arushi Bansal
     */
    public function __construct(
        LinkManagementInterface $linkManager,
        ProductRepositoryInterface $productRepo,
        OptionRepositoryInterface $optionRepo,
        Configurable $configurableType
    ) {
        $this->linkManager = $linkManager;
        $this->productRepo = $productRepo;
        $this->optionRepo = $optionRepo;
        $this->configurableType = $configurableType;
    }

    /**
     * Save custom invoice
     *
     * @param  Observer $observer
     * @return bool|void
     * @throws LocalizedException
     * @author Arushi Bansal
     */
    public function execute(Observer $observer)
    {
        try {
            $currentObject = $observer->getEvent()->getData("currentObject");
            $stringData = $currentObject->stringData;
            $result = $currentObject->result;
            if (isset($stringData[self::PARENTSKU]) && !empty($stringData[self::PARENTSKU])) {
                $product = $this->productRepo->get($stringData[self::PARENTSKU]);
                //@Hrusikesh Compaire two string by PHP strcmp()
                $isEqual = strcmp($product->getTypeId(), "configurable");
                if ($isEqual === 0) {
                    $childrenIds = array_values($this->configurableType->getChildrenIds($product->getId())[0]);

                    if (!in_array($result->getId(), $childrenIds)) {
                        $this->linkManager->addChild($stringData[self::PARENTSKU], $stringData["sku"]);
                    }
                }
            }
        } catch (NoSuchEntityException $e) {
            return true;
        } catch (LocalizedException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
