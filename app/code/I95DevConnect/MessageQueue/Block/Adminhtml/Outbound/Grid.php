<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Block\Adminhtml\Outbound;

use Exception;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterface;
use I95DevConnect\MessageQueue\Block\Adminhtml\MessageQueueGrid;
use I95DevConnect\MessageQueue\Helper\Data as MessageQueueHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;

/**
 * Outbound Message Queue Grid
 */
class Grid extends MessageQueueGrid
{
    /**
     * @var I95DevMagMQRepositoryInterface
     */
    public $I95DevMagMQ;

    /**
     * @var MessageQueueHelper
     */
    public $messageQueueHelper;

    /**
     * @var array
     */
    public $status = [
        "" => "All",
        MessageQueueHelper::PENDING => "Pending",
        MessageQueueHelper::PROCESSING => "Processing",
        /** @updatedBy Sravani Polu change label * */
        MessageQueueHelper::ERROR => "Error",
        MessageQueueHelper::SUCCESS => "Request Transferred",
        /** @updatedBy Sravani Polu change label * */
        MessageQueueHelper::COMPLETE => "Complete",
        MessageQueueHelper::CLOSED => "Closed",
    ];

    /**
     *
     * @param I95DevMagMQRepositoryInterface $I95DevMagMQ
     * @param MessageQueueHelper $messageQueueHelper
     * @param Context $context
     * @param Data $backendHelper
     * @param array $data
     */
    public function __construct(
        I95DevMagMQRepositoryInterface $I95DevMagMQ,
        MessageQueueHelper $messageQueueHelper,
        Context $context,
        Data $backendHelper,
        array $data = []
    ) {
        $this->I95DevMagMQ = $I95DevMagMQ;
        $this->messageQueueHelper = $messageQueueHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Get main buttons
     *
     * @return string
     */
    public function getMainButtonsHtml()
    {
        $html = $this->getChildHtml('oentititylist');
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
     * Contruct function
     *
     * @return void
     */
    protected function _construct()// phpcs:ignore
    {
        parent::_construct();
        $this->setID('magentoMessageQueueGrid');
        $this->setDefaultSort(self::MSG_ID);
        $this->setDefaultDir('DESC');
        $this->entityList = array_merge(["" => "All"], $this->showInOutbound());
        unset($this->entityList["address"]);
        /* @updatedBy Debashis. If component is NAV/BC no need to show customer group in out bound mq */
        $component = $this->messageQueueHelper->getComponent();
        if ($component == 'NAV' || $component == 'BC') {
            unset($this->entityList["CustomerGroup"]);
        }
    }

    /**
     * Fetch the entity list to show in outbound mesagequeue
     *
     * @return array
     * @author Arushi Bansal
     */
    protected function showInOutbound()
    {
        $supportedEntity = $this->messageQueueHelper->getEntityTypeOutboundList();
        $existingEntity = $this->I95DevMagMQ->getCollection();
        $existingEntity->addFieldToSelect(self::ENTITYCODE)->getSelect()->group(self::ENTITYCODE);
        $existingEntity = $existingEntity->getData();
        $allEntityList = $this->messageQueueHelper->getEntityTypeList();

        foreach ($existingEntity as $entity_code) {
            //@author Divya Koona. Added to exclude Customer Group entity from OBMQ Entity Drop Down for NAV
            if (!empty($entity_code[self::ENTITYCODE]) && in_array($entity_code[self::ENTITYCODE], $supportedEntity)) {
                $supportedEntity[$entity_code[self::ENTITYCODE]] = $allEntityList[$entity_code[self::ENTITYCODE]];
            }
        }

        return array_unique($supportedEntity);
    }

    /**
     * Prepare grid collection object
     *
     * @return $this
     */
    protected function _prepareCollection()// phpcs:ignore
    {
        $collection = $this->I95DevMagMQ->getCollection();
        //@author Divya Koona. Added to exclude Customer Group entity data from OBMQ collection for NAV
        $supportedEntity = $this->showInOutbound();
        $supportedEntityCodes = array_keys($supportedEntity);
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
        $this->addExportType($this->getUrl('messagequeue/*/exportOutboundCsv', ['_current' => true]), __('CSV'));
        $this->addExportType($this->getUrl('messagequeue/*/exportOutboundXml', ['_current' => true]), __('Excel XML'));

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
            'erp_code',
            [
                self::HEADER => "ERP",
                self::INDEX => 'erp_code'
            ]
        );

        $this->addColumn(
            'magento_id',
            [
                self::HEADER => __('Magento Id'),
                'type' => 'text',
                self::INDEX => 'magento_id',
                self::HEADER_CSS_CLASS => self::COL_ID,
                self::COLUMN_CSS_CLASS => self::COL_ID
            ]
        );

        $this->addColumn(
            'target_id',
            [
                self::HEADER => "ERP Id",
                'type' => 'text',
                self::INDEX => 'target_id',
                'is_system' => true,
                'renderer' => 'I95DevConnect\MessageQueue\Block\Adminhtml\ErrorReport',
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
            'updated_by',
            [
                self::HEADER => __('Updated By'),
                'type' => 'text',
                self::INDEX => 'updated_by',
                self::HEADER_CSS_CLASS => self::COL_ID,
                self::COLUMN_CSS_CLASS => self::COL_ID
            ]
        );
        return $this;
    }

    /**
     * Prepare Entititylist widget.
     */
    public function _prepareEntityListLayout()// phpcs:ignore
    {
        $this->addChild(
            'oentititylist',
            'I95DevConnect\MessageQueue\Block\Adminhtml\Widget\Outbound\EntityList'
        );
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
                'label' => __('Re-Sync'),
                'url' => $this->getUrl('messagequeue/*/outboundresync')
            ]
        );

        //  New Update By Closed action
        $this->getMassactionBlock()->addItem(
            'update_by_closed',
            [
                'label' => __('Update To Closed'),
                'url'   => $this->getUrl('messagequeue/*/updateByClosed'),
                'confirm' => __('Are you sure you want to update the selected records as Closed?'),
            ]
        );

        return $this;
    }
}

