<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Block\Adminhtml;

use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\ErrorUpdateData;
use I95DevConnect\MessageQueue\Model\ErrorUpdateDataFactory;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Text;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;

/**
 * Block responsible for rendering error message to in Message Queue
 */
class ErrorReport extends Text
{
    /**
     * @var ErrorUpdateData
     */
    public $modelErrorUpdateDataFactory;

    /**
     * @param ErrorUpdateDataFactory $modelErrorUpdateDataFactory
     * @param UrlInterface $backendUrl
     */
    public function __construct(
        ErrorUpdateDataFactory $modelErrorUpdateDataFactory,
        UrlInterface $backendUrl
    ) {

        $this->modelErrorUpdateDataFactory = $modelErrorUpdateDataFactory;
        $this->backendUrl = $backendUrl;
    }

    /**
     * Render block function
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $baseAdminUrl = $this->backendUrl->getUrl("messagequeue/messagequeue/errorData");
        $loadData = $row->getData();
        $errorId = (int) $loadData['error_id'];
        if ($loadData['status'] == Data::ERROR && $errorId !== 0) {
                return '<a herf onclick="(function () {require(' .
                        "'massagequeuegrid').showErrorMessage('$errorId', '$baseAdminUrl');})();" .
                        '" return false">' . 'Error ' . '</a>';
        } elseif ($loadData['status'] != Data::PENDING) {
            return $loadData[$this->getColumn()->getIndex()];
        } else {
            return '0';
        }
    }
}
