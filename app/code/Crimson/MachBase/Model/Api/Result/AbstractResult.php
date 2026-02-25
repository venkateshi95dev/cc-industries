<?php
/**
 * @namespace   Crimson
 * @module      MachBase
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/11/2019 4:05 PM
 * @brief
 */

namespace Crimson\MachBase\Model\Api\Result;

use Magento\Framework\DataObject;

/**
 * Class AbstractResult
 *
 * @package Crimson\MachBase\Model\Api\Result
 * @method \Crimson\MachBase\Model\Api\Result\AbstractResult setErrorNumber($errorNumber)
 * @method \Crimson\MachBase\Model\Api\Result\AbstractResult setErrorMessage($errorMessage)
 *
 * @method \Crimson\MachBase\Model\Api\Result\AbstractResult getErrorNumber()
 * @method \Crimson\MachBase\Model\Api\Result\AbstractResult getErrorMessage()
 */
abstract class AbstractResult extends DataObject
{
    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    protected function _getDataWithDefault($key, $default = null)
    {
        if ($this->hasData($key)) {
            return $this->_getData($key);
        }

        return $default;
    }
}
