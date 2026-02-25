<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 * @author Divya Koona. Removed isTargetCustomerAvailable function.
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order;

use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Generic;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;

/**
 * Class AbstractOrder contains method to initialize currentObject
 */
class AbstractOrder
{
    /**
     * @var object
     */
    public $currentObject;

    /**
     * @var LoggerInterfaceFactory
     */
    public $logger;

    /**
     * @var Generic
     */
    public $genericHelper;

    /**
     * @var string
     */
    public $targetFieldErp = 'targetId';

    /**
     * @var string
     */
    public $stringData;

    /**
     * @var string
     */
    public $entityCode;

    /**
     *
     * @var postData[]
     */
    public $postData = [];

    /**
     * @var Validate
     */
    public $validate;

    /**
     *
     * @param LoggerInterfaceFactory $logger
     * @param Generic $genericHelper
     * @param Validate $validate
     */
    public function __construct(
        LoggerInterfaceFactory $logger,
        Generic $genericHelper,
        Validate $validate
    ) {
        $this->logger = $logger;
        $this->genericHelper = $genericHelper;
        $this->validate = $validate;
    }

    /**
     * Initialize $this->currentObject
     *
     * @param array $orderObject
     * @return $this
     * @author Debashis S. Gopal
     */
    public function currentObject($orderObject)
    {
        $this->currentObject = $orderObject;
        return $this;
    }
}
