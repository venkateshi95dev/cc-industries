<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Block\Adminhtml;

use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\I95DevErpMQRepositoryFactory;
use I95DevConnect\MessageQueue\Model\ReadCustomXml;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for Inbound summary report
 */
class Summary extends SummaryReport
{
    /**
     * @var string
     */
    public $_template = 'I95DevConnect_MessageQueue::summary.phtml';// phpcs:ignore

    /**
     * @var array
     */
    public $totalStatusRcords = [];

    /**
     * @var Data
     */
    public $messageQueueHelper;

    /**
     * @var I95DevErpMQRepositoryFactory
     */
    public $erpMessageQueue;

    /**
     * @var ReadCustomXml
     */
    public $entityListReader;

    /**
     * @var UrlInterface
     */
    public $backendUrl;

    /**
     * Summary constructor
     *
     * @param Context $context
     * @param Data $messageQueueHelper
     * @param I95DevErpMQRepositoryFactory $erpMessageQueue
     * @param ReadCustomXml $entityListReader
     * @param UrlInterface $backendUrl
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $messageQueueHelper,
        I95DevErpMQRepositoryFactory $erpMessageQueue,
        ReadCustomXml $entityListReader,
        UrlInterface $backendUrl,
        array $data = []
    ) {
        $this->messageQueueHelper = $messageQueueHelper;
        $this->erpMessageQueue = $erpMessageQueue;
        $this->entityListReader = $entityListReader;
        $this->backendUrl = $backendUrl;
        parent::__construct($context, $data);
    }

    /**
     * Get MQ Url
     *
     * @param type $filter
     * @return string
     */
    public function getMessageQUrl($filter)
    {
        $filter = base64_encode($filter);
        return $this->backendUrl->getUrl('messagequeue/messagequeue/index', ['filter' => $filter]);
    }

    /**
     * Get inbound MQ url
     *
     * @return string
     */
    public function getInboundMessageQueue()
    {
        return $this->backendUrl->getUrl("messagequeue/messagequeue/index");
    }

    /**
     * Get entity wise sync report
     *
     * @return array
     * @throws LocalizedException
     */
    public function getEntityWiseSyncReport()
    {
        $totalReport = [];
        $entityList = $this->entityListReader->getXmlDataOrderBySortOrder();
        if (!empty($entityList)) {
            $this->totalStatusRcord[self::PENDING] = 0;
            $this->totalStatusRcord[self::PROCESSING] = 0;
            $this->totalStatusRcord[self::ERROR] = 0;
            $this->totalStatusRcord[self::SUCCESS] = 0;
            $this->totalStatusRcord[self::COMPLETE] = 0;
            $this->totalStatusRcord[self::TOTAL] = 0;
            foreach ($entityList as $entity) {
                $modelCollection = $this->erpMessageQueue->create()->getCollection();
                $modelCollection->addFieldToFilter("entity_code", $entity['id']);
                $modelCollection->removeAllFieldsFromSelect();
                $modelCollection->removeFieldFromSelect("msg_id");

                $modelCollection->addExpressionFieldToSelect(
                    self::PENDING,
                    "(count(if(status = '1', 1, null)) )",
                    self::PENDING
                );
                $modelCollection->addExpressionFieldToSelect(
                    self::PROCESSING,
                    "(count(if(status = '2', 1, null)) )",
                    self::PROCESSING
                );

                $modelCollection->addExpressionFieldToSelect(
                    self::ERROR,
                    "(count(if(status = '3', 1, null)) )",
                    self::ERROR
                );

                $modelCollection->addExpressionFieldToSelect(
                    self::COMPLETE,
                    "(count(if(status = '5', 1, null)) )",
                    self::COMPLETE
                );

                $modelCollection->addExpressionFieldToSelect(
                    self::SUCCESS,
                    "(count(if(status = '4', 1, null)) )",
                    self::SUCCESS
                );

                $report = $this->getTotalReports($modelCollection, $entity);

                if (!empty($report)) {
                    $totalReport[] = $report;
                }
            }

            $this->totalStatusRcords = $this->totalStatusRcord;
        }

        return $totalReport;
    }
}
