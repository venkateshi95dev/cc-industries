<?php
 /**
 * @namespace   Crimson
 * @module      ScheduledImport
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        6/24/2019 12:57 PM
 * @brief
 */

namespace Crimson\ScheduledImport\Plugin\Model\Scheduled;

use Crimson\ScheduledImport\Model\PreProcessPool;
use Magento\ScheduledImportExport\Model\Scheduled\Operation;

class OperationPlugin
{
    /**
     * @var PreProcessPool
     */
    protected $preProcessPool;

    /**
     * OperationPlugin constructor.
     *
     * @param PreProcessPool $preProcessPool
     */
    public function __construct(
        PreProcessPool $preProcessPool
    ) {
        $this->preProcessPool = $preProcessPool;
    }

    /**
     * @param Operation $subject
     * @param $tmpFilePath
     * @return mixed
     */
    public function afterGetFileSource(Operation $subject, $tmpFilePath)
    {
        $this->preProcessPool->processByPath($tmpFilePath);

        return $tmpFilePath;
    }
}
