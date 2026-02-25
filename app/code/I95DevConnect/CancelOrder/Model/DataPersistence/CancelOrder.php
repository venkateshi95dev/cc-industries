<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_CancelOrder
 */

namespace I95DevConnect\CancelOrder\Model\DataPersistence;

use Exception;
use I95DevConnect\CancelOrder\Model\DataPersistence\CancelOrder\Cancel;

/**
 * Class for cancel order
 */
class CancelOrder
{
    /**
     * @var Cancel
     */
    public $cancelOrder;

    /**
     * CancelOrder constructor.
     *
     * @param Cancel $cancelOrder
     */
    public function __construct(
        Cancel $cancelOrder
    ) {
        $this->cancelOrder = $cancelOrder;
    }

    /**
     * Creates cancel order
     *
     * @param  string $stringData
     * @return string
     * @throws Exception
     */
    public function create($stringData)
    {
        return $this->cancelOrder->cancelOrder($stringData);
    }
}
