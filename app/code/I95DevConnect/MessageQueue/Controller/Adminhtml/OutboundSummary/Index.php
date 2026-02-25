<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Controller\Adminhtml\OutboundSummary;

use I95DevConnect\MessageQueue\Controller\Adminhtml\MessagequeueAction;

/**
 * Controller to render outbound summary
 */
class Index extends MessagequeueAction
{
    /**
     * Execute controller
     *
     * @return mixed
     */
    public function execute()
    {
        if ($this->helperData->isEnabledInAnyWebsite()) {
            $outboundSummary = __('Outbound Summary Report');
            $breadcrumb = ['label' => $outboundSummary, 'title' => $outboundSummary];
            return $this->helperData->loadPage(
                $outboundSummary,
                'I95DevConnect\MessageQueue\Block\Adminhtml\OutboundSummary',
                'I95DevConnect_MessageQueue::OutboundSummaryReport',
                $breadcrumb
            );
        } else {
            return $this->helperData->returnToIndex();
        }
    }
}
