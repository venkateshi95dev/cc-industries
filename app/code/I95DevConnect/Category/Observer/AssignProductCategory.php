<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_Category
 */

namespace I95DevConnect\Category\Observer;

use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\Category\Helper\Data as CategoryHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Assign categories to product
 */
class AssignProductCategory implements ObserverInterface
{
    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var CategoryHelper
     */
    public $categoryHelper;

    /**
     * constructor to include dependency classes
     * @param Data $dataHelper
     * @param CategoryHelper $categoryHelper
     */
    public function __construct(
        Data $dataHelper,
        CategoryHelper $categoryHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->categoryHelper = $categoryHelper;
    }

    /**
     * Assign categories to product
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $productData = $observer->getData('currentObject');
        $categoryIds = $this->dataHelper->getValueFromArray("categoryIds", $productData->stringData);
        if (isset($categoryIds) && $categoryIds != '') {
            $categoryIdsArray = explode(',', $categoryIds);
            $magCatIds = [];
            foreach ($categoryIdsArray as $categoryId) {
                $magCatId = $this->categoryHelper->getCatIdByTargetCatCode($categoryId);
                if (isset($magCatId)) {
                    $magCatIds[] = $magCatId;
                } else {
                    $productData->logger->create()->createLog(
                        '__METHOD__',
                        __('i95dev_category_003') . $categoryId,
                        \I95DevConnect\MessageQueue\Api\LoggerInterface::I95EXC,
                        'error'
                    );
                }
            }
            $productData->productInterface->setCategoryIds($magCatIds);
        }
    }
}
