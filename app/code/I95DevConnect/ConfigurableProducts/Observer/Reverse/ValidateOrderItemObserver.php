<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_ConfigurableProducts
 */

namespace I95DevConnect\ConfigurableProducts\Observer\Reverse;

use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Variant order item observer class
 */
class ValidateOrderItemObserver implements ObserverInterface
{
    /**
     * @var object
     */
    public $magentoStoreManager;

    /**
     * @var object
     */
    public $cartRepository;

    /**
     * @var object
     */
    public $productRepository;

    /**
     * @var object
     */
    public $requestHelper;

    /**
     * @var object
     */
    public $dataObject;

    /**
     * @var object
     */
    protected $_eavAttribute;// phpcs:ignore

    /**
     * @var LoggerInterfaceFactory
     */
    public $logger;

    /**
     * @var ProductFactory
     */
    public $magentoProductModel;

    /**
     * @var ConfigurableFactory
     */
    public $typeConfigurableFactory;

    /**
     *
     * @param LoggerInterfaceFactory $logger
     * @param ConfigurableFactory $typeConfigurableFactory
     * @param ProductFactory $magentoProductModel
     */
    public function __construct(
        LoggerInterfaceFactory $logger,
        ConfigurableFactory $typeConfigurableFactory,
        ProductFactory $magentoProductModel
    ) {
        $this->logger = $logger;
        $this->typeConfigurableFactory = $typeConfigurableFactory;
        $this->magentoProductModel = $magentoProductModel;
    }

    /**
     * Get parent product load by id
     *
     * @param  Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {

        $item = $observer->getData('item');
        //@author Divya Koona. $item['id'] changed to $item['entity_id'] as
        //I am getting undefined index issue while order sync from Inbound MQ to Magento
        $parentproduct = $this->typeConfigurableFactory->create()->getParentIdsByChild($item['entity_id']);
        $parentId = isset($parentproduct[0]) ? $parentproduct[0] : null;
        if ($parentId !== null) {
            $parentProduct = $this->magentoProductModel->create()->load($parentId);
            // @Hrusikesh converted integer to string
            $status = $parentProduct->getStatus();
            if ((string)$status !== '1') {
                throw new LocalizedException(__("i95dev_order_027"));
            }
        }
    }
}
