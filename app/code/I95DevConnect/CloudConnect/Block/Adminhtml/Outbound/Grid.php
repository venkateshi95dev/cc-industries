<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Block\Adminhtml\Outbound;

use Exception;
use I95DevConnect\MessageQueue\Helper\Data as MessageQueueHelper;
use I95DevConnect\CloudConnect\Helper\Data as CloudHelper;

/**
 * Class to add Cloud Message ID column to Outbound MQ grid
 */
class Grid extends \I95DevConnect\MessageQueue\Block\Adminhtml\Outbound\Grid
{
    /**
     * @var array
     */
    public $status =  [
        "" => "All",
        MessageQueueHelper::PENDING => "Pending",
        MessageQueueHelper::PROCESSING => "Processing",/** @updatedBy Sravani Polu change label **/
        MessageQueueHelper::ERROR => "Error",
        MessageQueueHelper::SUCCESS => "Request Transferred",/** @updatedBy Sravani Polu change label **/
        CloudHelper::SUCCESS_C => "Success",
        MessageQueueHelper::COMPLETE => "Complete",
        MessageQueueHelper::CLOSED => "Closed",
    ];

    /**
     * Adding Cloud Message Id to outbound MQ grid column
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
                'header' => __('Cloud Message Id'),
                'type' => 'text',
                'index' => 'destination_msg_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'frame_callback' => [$this, 'appendColumnIndex']
            ]
        );
        return $this;
    }
    // @codingStandardsIgnoreEnd

    /**
     * Get status message
     *
     * @param string $value
     * @param Object $row
     * @return string
     */
    public function appendColumnIndex($value, $row)
    {
        return '<span class="destination_msg_id_' . $row->getID() . '" >' . $value . "</span>";
    }
}
