<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/28/2019 8:43 AM
 * @brief
 */

namespace Crimson\Sales\Model\Order\Attributes;

use Crimson\Sales\Model\ResourceModel\Order\AbstractMachData;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class AbstractSaveHandler
 * @package Crimson\Sales\Model\Order\Attributes
 */
abstract class AbstractSaveHandler
{
    /**
     * @var AbstractMachData
     */
    protected $machDataResource;

    /**
     * AttachHandler constructor.
     *
     * @param AbstractMachData $machDataResource
     */
    public function __construct(
        AbstractMachData $machDataResource
    ) {
        $this->machDataResource = $machDataResource;
    }

    /**
     * @param OrderInterface $order
     *
     * @return AbstractSaveHandler
     */
    abstract public function saveAttributes(OrderInterface $order): AbstractSaveHandler;
}
