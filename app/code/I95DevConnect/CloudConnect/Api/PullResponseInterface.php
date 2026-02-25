<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Api;

/**
 * Pull Response Sync Interface
 */
interface PullResponseInterface
{
    /**
     * Pull Response Sync Interface
     *
     * @return I95DevConnect\CloudConnect\Api\Data\ResponseInterface
     */
    public function syncResponse();
}
