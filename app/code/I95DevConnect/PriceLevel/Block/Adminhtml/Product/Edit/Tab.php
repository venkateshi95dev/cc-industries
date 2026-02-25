<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Block\Adminhtml\Product\Edit;

use Magento\Backend\Block\Template\Context;

/**
 * Price Level Tab for Product in Admin Login
 * @api
 */
class Tab extends \Magento\Backend\Block\Widget\Tab
{
    /**
     * Class constructor to include all the dependencies,
     * Make the Price Level Tab available based on the request
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {

        parent::__construct($context, $data);

        if (!$this->_request->getParam('id')
                || !$this->_authorization->isAllowed('I95DevConnect_PriceLevel::pricelevel')
        ) {
            $this->setCanShow(false);
        }
    }
}
