<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Api;

/**
 * Pull Data Sync Interface
 */
interface PushResponseInterface
{
    /**
     * Sync Response
     *
     * @return I95DevConnect\CloudConnect\Api\Data\ResponseInterface
     */
    public function syncResponse();
}
