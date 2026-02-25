<?php
namespace Crimson\ZipCokerWvConsolidation\Plugin\Sales\Model\Order;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;

class BeforeSetStatus
{
    public function beforeSetStatus(\Magento\Sales\Model\Order $subject, $status)
    {
        // This change only apply for orders placed in Cokertire & WV, to override the default status set for Zipcorvette
        if (!in_array($subject->getStore()->getWebsite()->getCode(), [CokerStoreInterface::COKER_WEBSITE_CODE, WVStoreInterface::WV_WEBSITE_CODE])) {
            return [$status];
        }
        if ($status === 'infulfillment') {
            if($subject->getState() === 'processing')
                $status = 'processing';
            if($subject->getState() === 'pending_payment')
                $status = 'pending_payment';
        }
        return [$status];
    }
}
