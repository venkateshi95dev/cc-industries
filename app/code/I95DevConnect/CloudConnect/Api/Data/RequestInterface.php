<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Request interface for setting and getting request data
 */
interface RequestInterface extends ExtensibleDataInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    public const PACKET_SIZE = 'PacketSize';
    public const REQUEST_DATA = 'RequestData';
    public const CONTEXT = 'Context';
    public const TYPE = 'type';

    /**
     * Get packetSize
     *
     * @return int|null
     * @api
     */
    public function getPacketSize();

    /**
     * Set packetSize
     *
     * @param int $packetSize
     * @return int
     * @api
     */
    public function setPacketSize($packetSize);

    /**
     * Get ResultData
     *
     * @return \I95DevConnect\CloudConnect\Api\Data\DataInterface[] | null
     */
    public function getRequestData();

    /**
     * Set ResultData
     *
     * @param \I95DevConnect\CloudConnect\Api\Data\DataInterface[] $resultData
     * @return $this
     */
    public function setRequestData($resultData);

    /**
     * Get Context
     *
     * @return ContextInterface[]
     * @api
     */
    public function getContext();

    /**
     * Set context
     *
     * @param ContextInterface[] $context
     * @return $this
     * @api
     */
    public function setContext($context);

    /**
     * Get type
     *
     * @return string|null
     * @api
     * @author Janani Allam
     */
    public function getType();

    /**
     * Set type
     *
     * @param string $type
     * @return string
     * @author Janani Allam
     * @api
     */
    public function setType($type);
}
