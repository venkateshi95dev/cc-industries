<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/4/2019 3:08 PM
 * @brief
 */

namespace Crimson\MachOrder\Plugin\Model\Order;

use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Magento\Shipping\Model\Order\Track;

/**
 * Class TrackPlugin
 * @package Crimson\MachOrder\Plugin\Model\Order
 */
class TrackPlugin
{
    /**
     * @param Track               $track
     * @param Phrase|string|array|DataObject $trackingInfo
     *
     * @return Phrase|string|array|DataObject
     */
    public function afterGetNumberDetail(Track $track, $trackingInfo)
    {
        if (is_string($trackingInfo) || $trackingInfo instanceof Phrase) {
            return $trackingInfo;
        }

        $customUrl = $track->getExtensionAttributes()->getMachCustomTrackUrl();
        if ($customUrl && \Zend_Uri::check($customUrl)) {
            if (is_array($trackingInfo)) {
                $trackingInfo['url'] = $customUrl;
            } else {
                $trackingInfo->setUrl($customUrl);
            }
        }

        return $trackingInfo;
    }
}
