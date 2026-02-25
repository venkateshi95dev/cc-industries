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

namespace Ebizcharge\Ebizcharge\Observer\Product;

use Ebizcharge\Ebizcharge\Model\AbstractModel;


use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;


/**
 * Product save after observer
 *
 * Class ProductSaveAfter
 */
class ProductSaveAfter extends AbstractModel implements ObserverInterface
{

    /**
     * Upload product to Ebiz Gateway on saving product
     *
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer): void
    {
        $storeId = $this->configFactory->create()->getStore()->getId();
        $isEbizActive = $this->isModuleActive($storeId);
        $isItemUpload = $this->isUploadItemsEnabled($storeId);

        $isDownload = $this->sessionManagerInterface->getIsDownload();

        // To check EbizCharge module & Item Upload feature is enabled
        if (!$isEbizActive || !$isItemUpload) {
            return;
        }

        $model = $observer->getEvent()->getData('product');
        if ($model instanceof CatalogProduct) {
            /** @var EbizProduct $model */
             $this->ebizResourceProduct->saveEbizchargeFields($model, $isDownload);
        }
    }
}
