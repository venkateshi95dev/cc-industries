<?php

namespace I95DevConnect\MessageQueue\Block\Adminhtml;

use I95DevConnect\MessageQueue\Block\Adminhtml\MessageQueue\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;

/**
 * messagequeue grid block class for outbound messagequeue
 */
class MessageQueueGrid extends Extended
{
    public const OPTIONS = "options";
    public const ENTITYCODE = "entity_code";
    public const MSG_ID = "msg_id";
    public const INDEX = "index";
    public const COL_ID = "col-id";
    public const COLUMN_CSS_CLASS = "column_css_class";
    public const HEADER_CSS_CLASS = "header_css_class";
    public const STATUS = "status";
    public const HEADER = "header";

    /**
     * @var array
     */
    public $status = [];

    /**
     * @var array
     */
    public $entityList;

    /**
     * Prepare default grid column
     *
     * @return void
     */
    protected function _prepareColumns()// phpcs:ignore
    {
        $this->addColumn(
            self::MSG_ID,
            [
                self::HEADER => __('Message ID'),
                'type' => 'number',
                self::INDEX => self::MSG_ID,
                self::HEADER_CSS_CLASS => self::COL_ID,
                self::COLUMN_CSS_CLASS => self::COL_ID
            ]
        );
        $this->addColumn(
            self::ENTITYCODE,
            [
                self::HEADER => "Entity",
                'type' => self::OPTIONS,
                self::INDEX => self::ENTITYCODE,
                self::OPTIONS => $this->entityList
            ]
        );

        $this->addColumn(
            'created_dt',
            [
                self::HEADER => __('Created Date'),
                'type' => 'datetime',
                self::INDEX => 'created_dt',
                self::HEADER_CSS_CLASS => self::COL_ID,
                self::COLUMN_CSS_CLASS => self::COL_ID
            ]
        );

        $this->addColumn(
            'updated_dt',
            [
                self::HEADER => __('Updated Date'),
                'type' => 'datetime',
                self::INDEX => 'updated_dt',
                self::HEADER_CSS_CLASS => self::COL_ID,
                self::COLUMN_CSS_CLASS => self::COL_ID
            ]
        );

        if (!$this->getIsExport()) {
            $this->addColumn(
                self::STATUS,
                [
                    self::HEADER => __('Status'),
                    'type' => self::OPTIONS,
                    self::INDEX => self::STATUS,
                    'frame_callback' => [$this, 'getMessageStatus'],
                    self::HEADER_CSS_CLASS => self::COL_ID,
                    self::COLUMN_CSS_CLASS => self::COL_ID,
                    self::OPTIONS => $this->status
                ]
            );
        } else {
            $this->addColumn(
                self::STATUS,
                [
                    self::HEADER => __('Status'),
                    'type' => self::OPTIONS,
                    self::INDEX => self::STATUS,
                    self::HEADER_CSS_CLASS => self::COL_ID,
                    self::COLUMN_CSS_CLASS => self::COL_ID,
                    self::OPTIONS => $this->status
                ]
            );
        }
    }
}
