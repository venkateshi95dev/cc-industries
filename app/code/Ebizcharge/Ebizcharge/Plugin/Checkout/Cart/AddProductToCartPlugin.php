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

namespace Ebizcharge\Ebizcharge\Plugin\Checkout\Cart;


use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Magento\Framework\Exception\NoSuchEntityException;


/**
 * Add Product to Cart Plugin
 */
class AddProductToCartPlugin extends AbstractModel
{

    /**
     * Around Add Product
     *
     * @param MageCartModel $subject
     * @param callable $proceed
     * @param $productInfo
     * @param $buyRequest
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function aroundAddProduct($subject, callable $proceed, $productInfo, $buyRequest = null): mixed
    {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if($isEbizChargeActive) {

            if ($this->isRecurringExist($buyRequest, $this->getStoreId())) {

                if ($buyRequest && is_array($buyRequest)) {
                    $buyRequestOptions = isset($buyRequest['options']) ? $buyRequest['options'] : [];

                    if (is_array($buyRequest) && count($buyRequest) > 0) {
                        $customAddtionalOptions = [];
                        foreach ($buyRequest as $key => $option) {
                            if (is_array($option)) {
                                if ($key === "recurring") {
                                    $option["label"] = "recurring";
                                    $option["value"] = true;
                                    $customAddtionalOptions[$key] = $option;
                                }
                            }
                        }
                    }
                    $origCustomOptions = [];
                    if (count($buyRequestOptions) > 0) {
                        foreach ($buyRequestOptions as $oKey => $option) {
                            if (is_array($option)) {
                                if (isset($option["label"])) {
                                    $origCustomOptions[$oKey] = $option;
                                }
                            }
                        }
                    }
                    $additionalOptions = array_merge($origCustomOptions, $customAddtionalOptions);
                    $productInfo->addCustomOption(
                        'additional_options',
                        $this->jsonSerializer->serialize($additionalOptions)
                    );

                }
            }
        }

        return $proceed($productInfo, $buyRequest);
    }
}
