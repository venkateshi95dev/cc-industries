<?php
/**
 * @namespace   Crimson
 * @module      ScheduledImport
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        6/24/2019 1:26 PM
 * @brief
 */

namespace Crimson\ScheduledImport\Model;

/**
 * Class PreProcessPool
 * @package Crimson\ScheduledImport\Model
 */
class PreProcessPool implements PreProcessPoolInterface
{
    /**
     * @var PreProcessPoolInterface[]
     */
    protected $preProcessors;

    /**
     * PreProcessPool constructor.
     *
     * @param PreProcessPoolInterface[] $preProcessors
     */
    public function __construct(
        array $preProcessors = []
    ) {
        $this->preProcessors = $preProcessors;
    }

    /**
     * @param string $absolutePath
     */
    public function processByPath(string $absolutePath): void
    {
        foreach ($this->preProcessors as $preProcessor) {
            $preProcessor->processByPath($absolutePath);
        }
    }
}
