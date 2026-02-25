<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Block\Adminhtml\MessageQueue;

use Exception;
use I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Block\Adminhtml\MessageQueueGrid;
use I95DevConnect\MessageQueue\Helper\Data as MessageQueueHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\UrlInterface;

/**
 * Inbound Message Queue Grid
 */
class Grid extends MessageQueueGrid
{
    /**
     * @var string[]
     */
    public $status = [
        "" => "All",
        MessageQueueHelper::PENDING => "Pending",
        MessageQueueHelper::PROCESSING => "Processing",
        MessageQueueHelper::ERROR => "Error",
        MessageQueueHelper::SUCCESS => "Success",
        MessageQueueHelper::COMPLETE => "Complete",
    ];

    /**
     * @var MessageQueueHelper
     */
    public $messageQueueHelper;

    /**
     * @var I95DevErpMQRepositoryInterfaceFactory
     */
    public $i95DevErpMQRepository;

    /**
     * @param MessageQueueHelper $messageQueueHelper
     * @param Context $context
     * @param Data $backendHelper
     * @param I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository
     * @param UrlInterface $backendUrl
     * @param array $data
     */
    public function __construct(
        MessageQueueHelper $messageQueueHelper,
        Context $context,
        Data $backendHelper,
        I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository,
        UrlInterface $backendUrl,
        array $data = []
    ) {
        $this->messageQueueHelper = $messageQueueHelper;
        $this->i95DevErpMQRepository = $i95DevErpMQRepository;
        $this->backendUrl = $backendUrl;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Get main buttons
     *
     * @return string
     */
    public function getMainButtonsHtml()
    {
        $html = $this->getChildHtml('entititylist');
        if ($this->getFilterVisibility()) {
            $html .= $this->getSearchButtonHtml();
            $html .= $this->getResetFilterButtonHtml();
        }

        return $html;
    }

    /**
     * Get status message
     *
     * @param string $value
     * @param Object $row
     * @return string
     */
    public function getMessageStatus($value, $row)
    {
        return '<span class="status_' . $row->getID() . '" >' . $value . "</span>";
    }

    /**
     * Get data html
     *
     * @param string $value
     * @param Object $row
     *
     * @return string
     */
    public function getDataHtml($value, $row)
    {
        $dataUrl = $this->backendUrl->getUrl("messagequeue/messagequeue/data");

        $msgId = $row->getMsgId();
        $messageQueue = $this->i95DevErpMQRepository->create()->get($msgId);
        if ($messageQueue->getDataId()) {
            return '<a herf=javascript:void(0) onclick="(function () {require(' . "'massagequeuegrid'" .
                ').showDataMessage(' . "'" . $messageQueue->getDataId() . "'" . ', `' . $dataUrl . '`);})();" >
                View</a>';
        } else {
            return 'No Data';
        }
    }

    /**
     * Grid construct
     *
     * @return void
     */
    protected function _construct()// phpcs:ignore
    {
        parent::_construct();
        $this->setID('messageQueueGrid');
        $this->setDefaultSort(self::MSG_ID);
        $this->setDefaultDir('DESC');
        $this->entityList = $this->showInInbound();
    }

    /**
     * Fetch the entity list to show in inbound mesagequeue
     *
     * @return array
     * @author Arushi Bansal
     */
    protected function showInInbound()
    {
        $supportedEntity = $this->messageQueueHelper->getEntityTypeInboundList();
        $existingEntity = $this->i95DevErpMQRepository->create()->getCollection();
        $existingEntity->addFieldToSelect(self::ENTITYCODE)->getSelect()->group(self::ENTITYCODE);
        $existingEntity = $existingEntity->getData();
        $allEntityList = $this->messageQueueHelper->getEntityTypeList();

        foreach ($existingEntity as $entity_code) {
            //@author Divya Koona. Added to exclude Customer Group entity from IBMQ Entity Drop Down for NAV
            if (!empty($entity_code[self::ENTITYCODE]) && in_array($entity_code[self::ENTITYCODE], $supportedEntity)) {
                $supportedEntity[$entity_code[self::ENTITYCODE]] = __($allEntityList[$entity_code[self::ENTITYCODE]]);
            }
        }

        return array_merge(["" => "All"], $supportedEntity);
    }

    /**
     * Prepare grid collection object
     *
     * @return $this
     */
    protected function _prepareCollection()// phpcs:ignore
    {
        $collection = $this->i95DevErpMQRepository->create()->getCollection();
        //@author Divya Koona. Added to exclude Customer Group entity data from IBMQ collection for NAV

        $supportedEntityCodes = array_keys($this->entityList);
        $collection->addFieldToFilter(self::ENTITYCODE, ['in' => $supportedEntityCodes]);
        if ($this->getIsExport()) {
            $select = $collection->getSelect();
            $joinTable = $collection->getTable("i95dev_error_report");
            $select->joinLeft(
                ["secondTable" => $joinTable],
                'main_table.error_id = secondTable.id',
                ['msg']
            );
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Initialize grid before rendering
     *
     * @return $this
     * @throws Exception
     */
    protected function _prepareGrid()// phpcs:ignore
    {
        $this->_prepareColumns();
        $this->_prepareEntityListLayout();
        parent::_prepareGrid();
        $this->addExportType($this->getUrl('messagequeue/*/exportInboundCsv', ['_current' => true]), __('CSV'));
        $this->addExportType($this->getUrl('messagequeue/*/exportInboundXml', ['_current' => true]), __('Excel XML'));

        return $this;
    }

    /**
     * Prepare default grid column
     *
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()// phpcs:ignore
    {
        parent::_prepareColumns();

        $this->addColumn(
            'target_id',
            [
                self::HEADER => "ERP Id",
                'type' => 'text',
                self::INDEX => 'target_id',
                self::HEADER_CSS_CLASS => self::COL_ID,
                self::COLUMN_CSS_CLASS => self::COL_ID
            ]
        );

        $this->addColumn(
            'ref_name',
            [
                self::HEADER => "Reference Name",
                'type' => 'text',
                self::INDEX => 'ref_name',
                self::HEADER_CSS_CLASS => self::COL_ID,
                self::COLUMN_CSS_CLASS => self::COL_ID
            ]
        );
        if ($this->getIsExport()) {
            $this->addColumn(
                'msg',
                [
                    self::HEADER => "Error Message",
                    'type' => 'text',
                    self::INDEX => 'msg',
                    self::HEADER_CSS_CLASS => self::COL_ID,
                    self::COLUMN_CSS_CLASS => self::COL_ID
                ]
            );
        }

        $this->addColumn(
            'counter',
            [
                self::HEADER => __('Count'),
                'type' => 'number',
                self::INDEX => 'counter',
                self::HEADER_CSS_CLASS => self::COL_ID,
                self::COLUMN_CSS_CLASS => self::COL_ID
            ]
        );
        $this->addColumn(
            'magento_id',
            [
                self::HEADER => __('Response'),
                'type' => 'text',
                self::INDEX => 'magento_id',
                'renderer' => 'I95DevConnect\MessageQueue\Block\Adminhtml\ErrorReport',
                self::HEADER_CSS_CLASS => self::COL_ID,
                self::COLUMN_CSS_CLASS => self::COL_ID,
                'is_system' => true
            ]
        );

        $this->addColumn(
            'action',
            [
                self::HEADER => __('Data'),
                'width' => '50px',
                'frame_callback' => [$this, 'getDataHtml'],
                'filter' => false,
                'sortable' => false,
                self::INDEX => 'data_id',
                self::HEADER_CSS_CLASS => self::COL_ID,
                self::COLUMN_CSS_CLASS => self::COL_ID,
                'is_system' => true
            ]
        );
        return $this;
    }

    /**
     * Prepare Entititylist widget.
     */
    public function _prepareEntityListLayout()// phpcs:ignore
    {
        $block = $this->getLayout()->createBlock('I95DevConnect\MessageQueue\Block\Adminhtml\Widget\Entitylist');
        $this->setChild('entititylist', $block);
    }

    /**
     * Prepare mass action
     *
     * @return $this
     */
    protected function _prepareMassaction()// phpcs:ignore
    {
        $this->setMassactionIDField(self::MSG_ID);
        $this->getMassactionBlock()->setFormFieldName(self::MSG_ID);

        $this->getMassactionBlock()->addItem(
            'sync',
            [
                'label' => __('Sync'),
                'url' => $this->getUrl('messagequeue/*/massSync')
            ]
        );
        return $this;
    }
}
