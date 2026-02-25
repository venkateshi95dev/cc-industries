<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Controller\Adminhtml\Label;

use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Indexer\Model\IndexerFactory;
use Magedelight\Megamenu\Api\LabelRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;

class Delete extends \Magedelight\Megamenu\Controller\Adminhtml\Label
{
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
     * @var LabelRepositoryInterface
     */
    private $labelRepository;

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
     * Delete constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param ProductAction $productAction
     * @param Json $serializer
     * @param IndexerFactory $indexerFactory
     * @param LabelRepositoryInterface $labelRepository
     * @param CategoryFactory $categoryFactory
     * @param CategoryResource $categoryResource
     * @param ProductFactory $productFactory
     * @param ProductResource $productResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        ProductAction $productAction,
        Json $serializer,
        IndexerFactory $indexerFactory,
        LabelRepositoryInterface $labelRepository,
        CategoryFactory $categoryFactory,
        CategoryResource $categoryResource,
        ProductFactory $productFactory,
        ProductResource $productResource
    ) {
        $this->productAction = $productAction;
        $this->serialize = $serializer;
        $this->indexerFactory = $indexerFactory;
        $this->labelRepository = $labelRepository;
        parent::__construct($context, $coreRegistry);
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->productFactory = $productFactory;
        $this->productResource = $productResource;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Throwable
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('label_id');
        if ($id) {
            try {
                $model = $this->labelRepository->get($id);
                if ($model->getProductAssign()) {
                    $this->unAssignedProductData($model->getProductAssign(), $model->getStoreId());
                }
                if ($model->getCategoryAssign()) {
                    $this->unAssignedCategoryData($model->getCategoryAssign(), $model->getStoreId());
                }
                $this->labelRepository->delete($model);

                $this->messageManager->addSuccessMessage(__('You deleted the Label.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['label_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a Label to delete.'));
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Assign product attribute value.
     *
     * @param mixed $data
     * @param string $storeId
     * @return void
     * @throws \Throwable
     */
    private function unAssignedProductData($data, $storeId)
    {
        $productIds = array_values($this->serialize->unserialize($data));
        try {
            if (!empty($productIds)) {
                $lastElement = end($productIds);
                if (!is_numeric($lastElement)) {
                    array_pop($productIds);
                }
                $this->removeProductData($productIds, $storeId);
            }
            // Reindex product attributes
            $this->reindexProductEav();
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
     * Assign category attribute value.
     *
     * @param mixed $data
     * @param string $storeId
     */
    private function unAssignedCategoryData($data, $storeId)
    {
        $categoryIds = explode(',', $data);
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

