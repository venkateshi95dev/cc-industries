<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Data interface for getting and setting request data field
 */
interface DataInterface extends ExtensibleDataInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    public const TARGET_ID = 'TargetId';
    public const SOURCE_ID = 'SourceId';
    public const MAGENTO_ID = 'MagentoId';
    public const REFERNCE = 'Reference';
    public const INPUT_DATA = 'InputData';
    public const MESSAGE_ID = 'MessageId';
    public const MESSAGE = 'Message';
    public const STATUS = 'Status';

    /**
     * Get target targetId
     *
     * @return string|null
     * @api
     */
    public function getTargetId();

    /**
     * Set target id
     *
     * @param string $targetId
     * @return $this
     * @api
     */
    public function setTargetId($targetId);

    /**
     * Get source sourceId
     *
     * @return string|null
     * @api
     */
    public function getSourceId();

    /**
     * Set source id
     *
     * @param string $sourceId
     * @return $this
     * @api
     */
    public function setSourceId($sourceId);

    /**
     * Get magento id magentoId
     *
     * @return string|null
     * @api
     */
    public function getMagentoId();

    /**
     * Set magento id
     *
     * @param string $magentoId
     * @return $this
     * @api
     */
    public function setMagentoId($magentoId);

    /**
     * Get reference
     *
     * @return string|null
     * @api
     */
    public function getReference();

    /**
     * Set reference
     *
     * @param string $reference
     * @return $this
     * @api
     */
    public function setReference($reference);

    /**
     * Set inputData
     *
     * @param string $inputData
     * @return $this
     * @api
     */
    public function setInputData($inputData);

    /**
     * Get inputData
     *
     * @return string|null
     * @api
     */
    public function getInputData();

    /**
     * Set cloud messageId
     *
     * @param string $messageId
     * @return $this
     * @api
     */
    public function setMessageId($messageId);

    /**
     * Get cloud messageId
     *
     * @return string|null
     * @api
     */
    public function getMessageId();

    /**
     * Set cloud message
     *
     * @param string $message
     * @return $this
     * @api
     */
    public function setMessage($message);

    /**
     * Get cloud message
     *
     * @return string|null
     * @api
     */
    public function getMessage();

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     * @api
     */
    public function setStatus($status);

    /**
     * Get status
     *
     * @return boolean|null
     * @api
     */
    public function getStatus();
}
