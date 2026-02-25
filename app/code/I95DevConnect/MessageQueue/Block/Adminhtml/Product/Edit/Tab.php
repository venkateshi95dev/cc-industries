<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Block\Adminhtml\Product\Edit;

use Magento\Backend\Block\Template\Context;

/**
 * Class To Add Tab in Product
 * @api
 */
class Tab extends \Magento\Backend\Block\Widget\Tab
{
    /**
     * Class Constructor
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        if (!$this->_request->getParam('id')) {
            $this->setCanShow(false);
        }
    }
}
