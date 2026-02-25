<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Controller\Adminhtml\Label;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Magedelight\Megamenu\Model\ResourceModel\Label\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Catalog\Model\CategoryFactory;

class MassDelete extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ProductAction
     */
    private $productAction;

    /**
     * @var Json
     */
    private $serialize;

    /**
     * @var IndexerFactory
     */
    private $indexerFactory;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CategoryResource
     */
    private $categoryResource;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ProductAction $productAction
     * @param Json $serializer
     * @param IndexerFactory $indexerFactory
     * @param CategoryFactory $categoryFactory
     * @param CategoryResource $categoryResource
     * @param ProductFactory $productFactory
     * @param ProductResource $productResource
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ProductAction $productAction,
        Json $serializer,
        IndexerFactory $indexerFactory,
        CategoryFactory $categoryFactory,
        CategoryResource $categoryResource,
        ProductFactory $productFactory,
        ProductResource $productResource
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->productAction = $productAction;
        $this->serialize = $serializer;
        $this->indexerFactory = $indexerFactory;
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->productFactory = $productFactory;
        $this->productResource = $productResource;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return Redirect
     * @throws LocalizedException|\Exception|\Throwable
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $label) {
            $this->unAssignedProductData($label->getData(), $label->getStoreId());
            $this->unAssignedCategoryData($label->getData(), $label->getStoreId());
            $label->delete();
        }

        // Reindex product attributes
        $this->reindexProductEav();

        $this->messageManager->addSuccessMessage(
            __(
                'A total of %1 record(s) have been deleted.',
                $collectionSize
            )
        );

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Remove Assigned product attribute value.
     *
     * @param array $data
     * @param string $storeId
     */
    private function unAssignedProductData($data, $storeId)
    {
        if (!isset($data['product_assign']) || !$data['product_assign']) {
            return;
        }
        $productIds = array_values($this->serialize->unserialize($data['product_assign']));
        try {
            if (!empty($productIds)) {
                $lastElement = end($productIds);
                if (!is_numeric($lastElement)) {
                    array_pop($productIds);
                }
                $this->removeProductData($productIds, $storeId);
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Magento\Framework\Exception\InputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * Function to remove Product Data
     *
     * @param array $removeProductIds
     * @param string $storeId
     */
    private function removeProductData($removeProductIds, $storeId)
    {
        if (!empty($removeProductIds)) {
            foreach ($removeProductIds as $productId) {
                try {
                    $removeAttributes = $this->prepareProductAttributes();
                    $product = $this->productFactory->create()->setStoreId($storeId);
                    $this->productResource->load($product, $productId);
                    foreach ($removeAttributes as $attribute) {
                        $product->setData($attribute, null);
                    }
                    $this->productResource->save($product);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __('An error occurred while updating the Product attribute.')
                    );
                }
            }
        }
    }

    /**
     * Prepare productattribute value.
     *
     * @return string[]
     */
    private function prepareProductAttributes()
    {
        return [
            "md_menu_label_shape",
            "md_menu_label",
            "md_label_text_color",
            "md_label_background_color"
        ];
    }

    /**
     * Reindex product EAV attributes.
     *
     * @return void
     * @throws \Throwable
     */
    private function reindexProductEav()
    {
        $indexer = $this->indexerFactory->create()->load('catalog_product_attribute');
        $indexer->reindexAll();
    }

    /**
     * Remove Assigned category attribute value.
     *
     * @param array $data
     * @param string $storeId
     */
    private function unAssignedCategoryData($data, $storeId)
    {
        if (!isset($data['category_assign']) || !$data['category_assign']) {
            return;
        }
        $categoryIds = explode(',', $data['category_assign']);

        if (!empty($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                try {
                    $category = $this->categoryFactory->create()->setStoreId($storeId);
                    $this->categoryResource->load($category, $categoryId);
                    $category->setData('md_label', null);
                    $category->setData('md_label_text_color', null);
                    $category->setData('md_label_background_color', null);
                    $category->setData('md_label_shape', null);
                    $this->categoryResource->save($category);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __('An error occurred while updating the category attribute.')
                    );
                }
            }
        }
    }
}
