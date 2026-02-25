<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Api;

/**
 * Interface for I95Dev REST Methods
 */
interface I95DevServerRepositoryInterface
{
    /**
     * Service method
     *
     * @param  string $methodName
     * @param  string $inputString
     * @param  string $erpName
     * @return I95DevConnect\MessageQueue\Api\I95DevResponseInterface
     */
    public function serviceMethod($methodName, $inputString = null, $erpName = null);

    /**
     * Sync data MQ to Magento
     *
     * @return string
     */
    public function syncMQtoMagento();
}
