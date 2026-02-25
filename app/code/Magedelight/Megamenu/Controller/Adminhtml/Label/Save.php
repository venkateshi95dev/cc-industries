<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Controller\Adminhtml\Label;

use Magedelight\Megamenu\Api\LabelRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Indexer\Model\IndexerFactory;
use Magedelight\Megamenu\Model\LabelFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

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
     * @var LabelFactory
     */
    private $labelFactory;

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
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param ProductAction $productAction
     * @param Json $serializer
     * @param IndexerFactory $indexerFactory
     * @param LabelFactory $labelFactory
     * @param LabelRepositoryInterface $labelRepository
     * @param CategoryFactory $categoryFactory
     * @param CategoryResource $categoryResource
     * @param ProductFactory $productFactory
     * @param ProductResource $productResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        ProductAction $productAction,
        Json $serializer,
        IndexerFactory $indexerFactory,
        LabelFactory $labelFactory,
        LabelRepositoryInterface $labelRepository,
        CategoryFactory $categoryFactory,
        CategoryResource $categoryResource,
        ProductFactory $productFactory,
        ProductResource $productResource
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->productAction = $productAction;
        $this->serialize = $serializer;
        $this->indexerFactory = $indexerFactory;
        $this->labelFactory = $labelFactory;
        $this->labelRepository = $labelRepository;
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->productFactory = $productFactory;
        $this->productResource = $productResource;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Throwable
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            try {
                $id = $this->getRequest()->getParam('label_id');
                $model = $this->labelFactory->create();
                $oldStoreId = null;
                if ($id) {
                    $model = $this->labelRepository->get($id);
                    $oldStoreId = $model->getStoreId();
                }
                if (!$model->getId() && $id) {
                    $this->messageManager->addErrorMessage(__('This Label no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }

                $oldAssignedProducts = $model->getProductAssign();
                $oldAssignedCategories = array_filter(explode(',', $model->getCategoryAssign()??''));
                $categoryIds = $data['category_assign'] ?? [];
                $data['category_assign'] = !empty($categoryIds) ? implode(',', $categoryIds) : null;

                $model->setData($data);
                $this->labelRepository->save($model);

                $oldAssignedProducts = $oldAssignedProducts ? $this->serialize->unserialize($oldAssignedProducts): [];
                if (isset($data['product_assign']) && $data['product_assign'] != '') {
                    $removeProductIds = '{}';
                    if (!empty($oldAssignedProducts)) {
                        $newAssignedProducts  = $this->serialize->unserialize($data['product_assign']);
                        $productdiff = $data['store_id'] === $oldStoreId
                            ? array_filter(array_diff($oldAssignedProducts, $newAssignedProducts))
                            : $oldAssignedProducts;
                        if (!empty($productdiff)) {
                            $removeProductIds = $this->serialize->serialize($productdiff);
                        }
                    }
                    $data['store_id'] !== $oldStoreId
                        ? $this->assignedProductData($data['product_assign'], $removeProductIds, $data, $oldStoreId)
                        : $this->assignedProductData($data['product_assign'], $removeProductIds, $data);
                } elseif (!empty($oldAssignedProducts)) {
                    $this->removeProductData(
                        $oldAssignedProducts,
                        $data['store_id'] !== $oldStoreId ? $oldStoreId : $data['store_id']
                    );
                }

                if (!empty($categoryIds)) {
                    $removeCategoryIds = [];
                    if (!empty($oldAssignedCategories)) {
                        $removeCategoryIds = $data['store_id'] === $oldStoreId
                            ? array_filter(array_diff($oldAssignedCategories, $categoryIds)) : $oldAssignedCategories;
                    }
                    $data['store_id'] !== $oldStoreId
                        ? $this->assignedCategoryData($categoryIds, $removeCategoryIds, $data, $oldStoreId)
                        : $this->assignedCategoryData($categoryIds, $removeCategoryIds, $data);
                } elseif (empty($categoryIds) && !empty($oldAssignedCategories)) {
                    $this->removeCategoryData(
                        $oldAssignedCategories,
                        $data['store_id'] !== $oldStoreId ? $oldStoreId : $data['store_id']
                    );
                }

                $this->messageManager->addSuccessMessage(__('You saved the Label.'));
                $this->dataPersistor->clear('magedelight_megamenu_label');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['label_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Label.'));
            }

            $this->dataPersistor->set('magedelight_megamenu_label', $data);
            return $resultRedirect->setPath('*/*/edit', ['label_id' => $this->getRequest()->getParam('label_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Assign product attribute value.
     *
     * @param mixed $productIds
     * @param mixed $removeProductIds
     * @param array $data
     * @param int|null $oldStoreId
     * @throws \Throwable
     */
    private function assignedProductData($productIds, $removeProductIds, $data, $oldStoreId = null)
    {
        $productIds = array_values($this->serialize->unserialize($productIds));
        $removeProductIds = array_values($this->serialize->unserialize($removeProductIds));
        try {
            if (!empty($productIds)) {
                $lastElement = end($productIds);
                if (!is_numeric($lastElement)) {
                    array_pop($productIds);
                }
                $this->productAction->updateAttributes(
                    $productIds,
                    $this->prepareProductAttributes($data),
                    $data['store_id'] // Store Id
                );
            }

            if (!empty($removeProductIds)) {
                $lastElement = end($removeProductIds);
                if (!is_numeric($lastElement)) {
                    array_pop($removeProductIds);
                }
                $storeId = $oldStoreId !== null ? $oldStoreId : $data['store_id'];
                $this->removeProductData($removeProductIds, $storeId);
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
     * Prepare Product attribute.
     *
     * @param array $data
     * @return array
     */
    private function prepareProductAttributes($data)
    {
        return [
            "md_menu_label_shape" => $data['shape'],
            "md_menu_label" => $data['text'],
            "md_label_text_color" => $data['text_color'],
            "md_label_background_color" => $data['background_color']
        ];
    }

    /**
     * Prepare productattribute value.
     *
     * @return string[]
     */
    private function prepareProductAttributesForRemove()
    {
        return [
            "md_menu_label_shape",
            "md_menu_label",
            "md_label_text_color",
            "md_label_background_color"
        ];
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
                    $removeAttributes = $this->prepareProductAttributesForRemove();
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
     * @param mixed $categoryIds
     * @param mixed $removeCategoryIds
     * @param array $data
     * @param string|null $oldStoreId
     */
    private function assignedCategoryData($categoryIds, $removeCategoryIds, $data, $oldStoreId = null)
    {
        foreach ($categoryIds as $categoryId) {
            try {
                $category = $this->categoryFactory->create()->setStoreId($data['store_id']);
                $this->categoryResource->load($category, $categoryId);
                $category->setData('md_label', $data['text']);
                $category->setData('md_label_text_color', $data['text_color']);
                $category->setData('md_label_background_color', $data['background_color']);
                $category->setData('md_label_shape', $data['shape']);
                $this->categoryResource->save($category);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error occurred while updating the category attribute.'));
            }
        }

        if (!empty($removeCategoryIds)) {
            $storeId = $oldStoreId !== null ? $oldStoreId : $data['store_id'];
            $this->removeCategoryData($removeCategoryIds, $storeId);
        }
    }

    /**
     * Function to remove Category Data
     *
     * @param array $removeCategoryIds
     * @param string $storeId
     */
    private function removeCategoryData($removeCategoryIds, $storeId)
    {
        if (!empty($removeCategoryIds)) {
            foreach ($removeCategoryIds as $categoryId) {
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
