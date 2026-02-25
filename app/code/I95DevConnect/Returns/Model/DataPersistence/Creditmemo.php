<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2021 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_Returns
 */

namespace I95DevConnect\Returns\Model\DataPersistence;

use I95DevConnect\Returns\Model\DataPersistence\ReturnCreditmemo\ReturnCreditmemo;

/**
 * Class for Creditmemo
 */
class Creditmemo
{
    /**
     * @var ReturnCreditmemo
     */
    public $rceditMemo;

    /**
     * Creditmemo constructor.
     *
     * @param ReturnCreditmemo $rceditMemo
     */
    public function __construct(
        ReturnCreditmemo $rceditMemo
    ) {
        $this->rceditMemo = $rceditMemo;
    }

    /**
     * Creates Credit Memo
     *
     * @param string $stringData
     * @return string
     * @throws \Exception
     */
    public function create($stringData)
    {
        return $this->rceditMemo->returnCreditmemo($stringData);
    }
}
