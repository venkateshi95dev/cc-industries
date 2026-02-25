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

namespace Ebizcharge\Ebizcharge\Plugin;

use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Exception;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Updater;

/**
 * Order item subscribe options
 *
 * Class SubscribeOptions
 */
class SubscribeOptions extends AbstractModel
{


    /**
     * Update subscription options in buy request
     *
     * @param Updater $subject
     * @param Item $item
     * @param array $info
     * @return array
     * @throws Exception
     */
    public function beforeUpdate(Updater $subject, Item $item, array $info): array
    {
        $itemId = $item->getItemId();
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if ($isEbizChargeActive) {
            if (isset($info['recurring']) && count($info['recurring']) > 0 && (float)$info['recurring']['itemid'] === (float)$itemId) {
                if (!empty($info['recurring']) && count($info['recurring']) > 0) {

                    if (array_key_exists('rec_indefinitely', $info['recurring'])) {
                        $info['recurring']['rec_indefinitely'] = self::REC_INDEFINITELY;
                    }
                    if (isset($info['recurring']["rec_activate"]) && strtolower($info['recurring']["rec_activate"]) === strtolower("On")) {
                        $info['recurring']['rec_activate'] = 1;
                    } else {
                        $info['recurring']['rec_activate'] = 0;
                    }


                    if (count($item->getOptions()) > 0) {

                        foreach ($item->getOptions() as $option) {
                            try {
                                $optionId = $option->getId();
                                $option = $option->load($optionId);
                                $optionCode = $option->getCode();

                                if ($optionCode === "info_buyRequest") {
                                    $infoBuyRequest = $this->serializer->unserialize($option->getValue());
                                    $infoBuyRequest["recurring"] = isset($info["recurring"]) ? $info["recurring"] : [];
                                    if (count($infoBuyRequest["recurring"]) > 0) {
                                        $option->setValue($this->serializer->serialize($infoBuyRequest));
                                        $option->save();
                                    }
                                }

                            } catch (Exception $e) {
                                $this->ebizchargeLogger->addCritical(__("Subscription against this item cannot be made error:" . $e->getMessage()));

                            }
                        }
                    }
                    $info["recurring"] = isset($info["recurring"]) ? $info["recurring"] : [];

                }

            }
        }


        return [$item, $info];
    }
}
