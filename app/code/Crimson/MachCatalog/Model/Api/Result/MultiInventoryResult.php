<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/11/2019 4:02 PM
 * @brief
 */

namespace Crimson\MachCatalog\Model\Api\Result;

use Crimson\MachBase\Model\Api\Result\AbstractResult;

/**
 * Class MultiInventoryResult
 * @package Crimson\MachCatalog\Model\Api\Result
 */
class MultiInventoryResult extends AbstractResult
{

    /**
     * @param string $sku
     *
     * @return string
     */
    public function getStatusCode(string $sku) :string
    {
        $result = '';
        $data = $this->getData();
        if (isset($data[$sku]['status_code'])) {
            $result = (string)$data[$sku]['status_code'];
        }
        return $result;
    }

    /**
     * @param string $sku
     *
     * @return bool|float - false on failure or not returned.
     */
    public function getQtyAvailable(string $sku)
    {
        $result = false;
        $data = $this->getData();
        if (isset($data[$sku]['qty_available'])) {
            $result = (float)$data[$sku]['qty_available'];
        }
        return $result;
    }

    /**
     * @param string $sku
     *
     * @return bool|float - false on failure or not returned.
     */
    public function getListPrice(string $sku)
    {
        $result = false;
        $data = $this->getData();
        if (isset($data[$sku]['list_price'])) {
            $result = (float)$data[$sku]['list_price'];
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function getResponseStatus(): bool
    {
        return (bool) $this->_getDataWithDefault('response_status', false);
    }

    /**
     * @param string $sku
     * @return bool
     */
    public function getIsSkuInResponse(string $sku): bool
    {
        $data = $this->getData();
        if (isset($data[$sku])) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getDropship(string $sku): bool
    {
        $result = false;
        $data = $this->getData();
        if (isset($data[$sku]['drop_ship'])) {
            $result = (bool) $data[$sku]['drop_ship'];
        }
        return $result;
    }

    public function getDropshipForCartPage(string $sku)
    {
        $data = $this->getData();
        if (isset($data[$sku]['drop_ship'])) {
            return (bool) $data[$sku]['drop_ship'];
        } else {
            return null;
        }
    }
}
