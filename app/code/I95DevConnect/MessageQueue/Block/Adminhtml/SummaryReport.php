<?php

namespace I95DevConnect\MessageQueue\Block\Adminhtml;

use Magento\Backend\Block\Template;

/**
 * summary report for inbound messagequeue
 */
class SummaryReport extends Template
{
    public const PENDING = "pending";
    public const PROCESSING = "processing";
    public const ERROR = "error";
    public const SUCCESS = "success";
    public const COMPLETE = "complete";
    public const TOTAL = "total";

    /**
     * @var array
     */
    public $totalStatusRcords = [];

    /**
     * @var array
     */
    public $totalStatusRcord = [];

    /**
     * Get report total
     *
     * @param array $modelCollection
     * @param string $entity
     * @return array|mixed
     */
    protected function getTotalReports($modelCollection, $entity)
    {
        $modelCollection->addExpressionFieldToSelect(self::TOTAL, "(count(*))", self::TOTAL);
        if ($modelCollection->getSize() > 0) {
            $report = $modelCollection->getData()[0];
            $this->totalStatusRcord[self::PENDING] += $report[self::PENDING];
            $this->totalStatusRcord[self::PROCESSING] += $report[self::PROCESSING];
            $this->totalStatusRcord[self::ERROR] += $report[self::ERROR];
            $this->totalStatusRcord[self::SUCCESS] += $report[self::SUCCESS];
            $this->totalStatusRcord[self::COMPLETE] += $report[self::COMPLETE];
            $this->totalStatusRcord[self::TOTAL] += $report[self::TOTAL];
            $report['entity'] = $entity['title'];
            $report['entity_code'] = $entity['id'];
            return $report;
        } else {
            return [];
        }
    }

    /**
     * Get total status records
     *
     * @return array
     */
    public function getTotalStatusRcords()
    {
        return $this->totalStatusRcords;
    }
}
