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

namespace Ebizcharge\Ebizcharge\Plugin\Checkout\Model;

use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class LayoutProcessorPlugin extends AbstractModel
{
    /**
     * Run JS Layout Processor Interface
     *
     * @param LayoutProcessorInterface $jsLayout
     * @param array $jsLayoutProcessor
     * @return array[]
     * @throws NoSuchEntityException
     */
    public function afterProcess(
        LayoutProcessorInterface $jsLayout,
        array $jsLayoutProcessor
    ): array {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if (!$isEbizChargeActive) {
            if(isset($jsLayoutProcessor["components"]["checkout"])){
                unset($jsLayoutProcessor["components"]["checkout"]["children"]["sidebar"]
                    ["children"]["summary"]["children"]["totals"] ["children"]["ebiz-surcharge"])
                    ;
            }
            if(isset($jsLayoutProcessor["components"]["checkout"])){
                $checkoutChildren =
                    $jsLayoutProcessor["components"]["checkout"]["children"]["sidebar"]["children"]["summary"];
            }

        }
        return $jsLayoutProcessor;
    }
}
