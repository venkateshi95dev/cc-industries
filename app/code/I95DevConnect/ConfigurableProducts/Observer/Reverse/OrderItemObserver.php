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
use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * class for order item observer
 */
class OrderItemObserver implements ObserverInterface
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
     * @var ConfigurableFactory
     */
    public $typeConfigurableFactory;

    /**
     * @var ProductFactory
     */
    public $magentoProductModel;

    /**
     * @var AttributeFactory
     */
    public $entityAttributeFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    public $productFactory;

    /**
     *
     * @param LoggerInterfaceFactory $logger
     * @param ConfigurableFactory $typeConfigurableFactory
     * @param AttributeFactory $entityAttributeFactory
     * @param \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory
     * @param ProductFactory $magentoProductModel
     */
    public function __construct(
        LoggerInterfaceFactory $logger,
        ConfigurableFactory $typeConfigurableFactory,
        AttributeFactory $entityAttributeFactory,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory,
        ProductFactory $magentoProductModel
    ) {
        $this->logger = $logger;
        $this->typeConfigurableFactory = $typeConfigurableFactory;
        $this->entityAttributeFactory = $entityAttributeFactory;
        $this->productFactory = $productFactory;
        $this->magentoProductModel = $magentoProductModel;
    }

    /**
     * Set variant attributes options
     *
     * @param  Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {

        $item = $observer->getData('quoteObject');
        $parentProduct = $this->typeConfigurableFactory->create()->getParentIdsByChild($item->simpleproduct->getId());
        $parentId = isset($parentProduct[0]) ? $parentProduct[0] : null;

        // In future if more type of product supported we need to verify product type as well
        if ($parentId !== null) {
            $item->parentProduct = $item->buyOptions = null;

            $item->parentProduct = $this->magentoProductModel->create()->load($parentId);
            $itemVariants = current($item->itemData['itemVariants']);
            $attributeWithKey = $itemVariants['attributeWithKey'];

            $options = null;
            $options = $this->getOptions($attributeWithKey);

            $buyOptions = [
                'qty' => $item->itemData['qty'],
                'super_attribute' => $options,
                '_processing_params' => []
            ];

            $markdownPrice = ($item->itemData['transactionMarkdownPrice'] ?? '');
            $baseMarkdownPrice = ($item->itemData['markdownPrice'] ?? '');

            $basePrice = ($baseMarkdownPrice != '') ?
            ((float)$item->itemData['price'] - $baseMarkdownPrice) : $item->itemData['price'];
            if (isset($item->itemData['transactionPrice'])) {
                $price = ($markdownPrice != '') ?
                    ((float) $item->itemData['transactionPrice'] - $markdownPrice) :
                    $item->itemData['transactionPrice'];
            }

            if ($item->simpleproduct->getPrice() != $basePrice) {
                if (isset($item->itemData['transactionPrice'])) {
                    $buyOptions['custom_price'] = $price;
                } else {
                    $buyOptions['custom_price'] = $basePrice;
                }
            }

            $item->buyOptions = new DataObject($buyOptions);
        }
    }

    /**
     * Get options
     *
     * @param object $attributeWithKey
     * @return mixed
     */
    private function getOptions($attributeWithKey)
    {
        $attributeOption = [];
        foreach ($attributeWithKey as $attr) {
            $attributeId = $this->entityAttributeFactory->create()->getIdByCode(
                'catalog_product',
                $attr['attributeCode']
            );
            $poductReource = $this->productFactory->create();

            //@Hrusieksh Changed the code  for issue #24894993
            $attribute = $poductReource->getAttribute($attr['attributeCode']);
            if ($attribute->usesSource()) {
                foreach ($attribute->getSource()->getAllOptions() as $option) {
                    $attributeOption[strtolower($option['label'])] = $option['value'];
                }
            }

            $optionId = (array_key_exists(strtolower($attr['attributeValue']), $attributeOption)) ?
                $attributeOption[strtolower($attr['attributeValue'])] : null;

            $options[$attributeId] = $optionId;
        }

        return $options;
    }
}
