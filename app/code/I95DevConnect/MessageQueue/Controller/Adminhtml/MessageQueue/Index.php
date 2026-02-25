<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Controller\Adminhtml\MessageQueue;

use I95DevConnect\MessageQueue\Controller\Adminhtml\MessagequeueAction;

/**
 * Controller Class for inbound message queue.
 */
class Index extends MessagequeueAction
{
    /**
     * Execute function
     *
     * @return mixed
     */
    public function execute()
    {
        if ($this->helperData->isEnabledInAnyWebsite()) {
            $breadcrumb = ['label' => 'MessageQueue Report', 'title' => 'Message Queue' ];
            return $this->helperData->loadPage(
                'Inbound Message Queue Report',
                'I95DevConnect\MessageQueue\Block\Adminhtml\MessageQueue',
                'I95DevConnect_MessageQueue::MessageQueue',
                $breadcrumb
            );
        } else {
            return $this->helperData->returnToIndex();
        }
    }
}
