<?php
/**
 * Created by PhpStorm.
 * User: mauro
 * Date: 2/15/2019
 * Time: 1:35 AM
 */

namespace Crimson\MachCustomer\Service;

/**
 * Class CustomerPreventMachDataProvider
 * @package Crimson\MachCustomer\Service
 */
class CustomerPreventMachDataProvider
{

    protected $_preventExportMach = [];

    /**
     * @param int $customerId
     * @return $this
     */
    public function setPreventMachExport(int $customerId): CustomerPreventMachDataProvider
    {
        $this->_preventExportMach[$customerId] = true;

        return $this;
    }

    /**
     * @param int $customerId
     * @return $this
     */
    public function setPreventMachExportFinished(int $customerId): CustomerPreventMachDataProvider
    {
        if (isset($this->_preventExportMach[$customerId])) {
            unset($this->_preventExportMach[$customerId]);
        }

        return $this;
    }

    /**
     * @param int $customerId
     * @return bool
     */
    public function isPreventMachExport(int $customerId): bool
    {
        if (isset($this->_preventExportMach[$customerId])) {
            return true;
        }
        return false;
    }

}
