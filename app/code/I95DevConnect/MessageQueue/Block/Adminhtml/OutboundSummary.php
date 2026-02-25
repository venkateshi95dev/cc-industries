<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Block\Adminhtml;

use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\I95DevMagMQRepositoryFactory;
use I95DevConnect\MessageQueue\Model\ReadCustomXml;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for outbound summary report
 */
class OutboundSummary extends SummaryReport
{
    /**
     * @var string
     */
    public $_template = 'I95DevConnect_MessageQueue::outboundsummary.phtml';// phpcs:ignore

    /**
     * @var array
     */
    public $totalStatusRcords = [];

    /**
     * @var Data
     */
    public $messageQueueHelper;

    /**
     * @var I95DevMagMQRepositoryFactory
     */
    public $I95DevMagMQ;

    /**
     * @var ReadCustomXml
     */
    public $entityListReader;

    /**
     * @var UrlInterface
     */
    public $backendUrl;

    /**
     * OutboundSummary constructor
     *
     * @param Context $context
     * @param Data $messageQueueHelper
     * @param I95DevMagMQRepositoryFactory $I95DevMagMQ
     * @param ReadCustomXml $entityListReader
     * @param UrlInterface $backendUrl
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $messageQueueHelper,
        I95DevMagMQRepositoryFactory $I95DevMagMQ,
        ReadCustomXml $entityListReader,
        UrlInterface $backendUrl,
        array $data = []
    ) {
        $this->messageQueueHelper = $messageQueueHelper;
        $this->I95DevMagMQ = $I95DevMagMQ;
        $this->entityListReader = $entityListReader;
        $this->backendUrl = $backendUrl;
        parent::__construct($context, $data);
    }

    /**
     * Get url of outbound MQ
     *
     * @return string
     */
    public function getOutboundMessageQueue()
    {
        return $this->backendUrl->getUrl("messagequeue/messagequeue/magento");
    }

    /**
     * Get MQ Url
     *
     * @param string $filter
     * @return string
     */
    public function getMessageQUrl($filter)
    {
        $filter = base64_encode($filter);
        return $this->backendUrl->getUrl('messagequeue/messagequeue/magento', ['filter' => $filter]);
    }

    /**
     * Get reports
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
            foreach ($entityList as $entityCode => $entity) {
                if ($entityCode == "address") {
                    continue;
                }
                $modelCollection = $this->I95DevMagMQ->create()->getCollection();
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
                    self::SUCCESS,
                    "(count(if(status = '4', 1, null)) )",
                    self::SUCCESS
                );
                $modelCollection->addExpressionFieldToSelect(
                    self::COMPLETE,
                    "(count(if(status = '5', 1, null)) )",
                    self::COMPLETE
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
