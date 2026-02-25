<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Block\Adminhtml\Widget;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

/**
 * Class for i95Dev Entity list
 */
class Entitylist extends Template
{
    /**
     * @var string
     */
    protected $_template = 'I95DevConnect_MessageQueue::widget/entitylist.phtml';// phpcs:ignore

    /**
     * @var array
     */
    public $items = [];

    /**
     * @var Data
     */
    public $msgData;

    /**
     * @param Context $context
     * @param Data $msgData
     */
    public function __construct(
        Context $context,
        Data $msgData
    ) {
        parent::__construct($context);
        $this->msgData = $msgData;
    }

    /**
     * Get entity details
     *
     * @return array
     */
    public function getEntities()
    {
        return $this->msgData->getSyncEntities();
    }
}
