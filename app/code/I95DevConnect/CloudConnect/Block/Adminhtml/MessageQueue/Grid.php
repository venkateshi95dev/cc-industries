<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Block\Adminhtml\MessageQueue;

use Exception;

/**
 * Class to add Cloud Message ID column to Inbound MQ grid
 */
class Grid extends \I95DevConnect\MessageQueue\Block\Adminhtml\MessageQueue\Grid
{
    /**
     * Adding Cloud Message Id to Inbound MQ grid column
     *
     * @return $this
     * @throws Exception
     */
    // @codingStandardsIgnoreStart
    public function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn(
            'destination_msg_id',
            [
                'header' => "Cloud Message Id",
                'type' => 'text',
                'index' => 'destination_msg_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        return $this;
    }
    // @codingStandardsIgnoreEnd
}
