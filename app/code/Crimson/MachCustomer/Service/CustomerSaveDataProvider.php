<?php

namespace Crimson\MachCustomer\Service;

/**
 * Class CustomerSaveDataProvider
 * @package Crimson\MachCustomer\Service
 */
class CustomerSaveDataProvider
{
    protected $_saveInProgress = [];

    /**
     * @param int $customerId
     * @return $this
     */
    public function setSaveInProgress(int $customerId): CustomerSaveDataProvider
    {
        $this->_saveInProgress[$customerId] = true;

        return $this;
    }

    /**
     * @param int $customerId
     * @return $this
     */
    public function setSaveProcessFinished(int $customerId): CustomerSaveDataProvider
    {
        if (isset($this->_saveInProgress[$customerId])) {
            unset($this->_saveInProgress[$customerId]);
        }

        return $this;
    }

    /**
     * @param int $customerId
     * @return bool
     */
    public function isSaveInProgress(int $customerId): bool
    {
        if (isset($this->_saveInProgress[$customerId])) {
            return true;
        }
        return false;
    }
}
