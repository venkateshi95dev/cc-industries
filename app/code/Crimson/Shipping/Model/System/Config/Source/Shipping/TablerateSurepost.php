<?php
/**
 * @namespace   Crimson
 * @module      Shipping
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/25/2019
 */
namespace Crimson\Shipping\Model\System\Config\Source\Shipping;

use \Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class TablerateSurepost
 * @package Crimson\Shipping\Model\System\Config\Source\Shipping
 */
class TablerateSurepost implements OptionSourceInterface
{
    /**
     * @var \Crimson\Shipping\Model\Carrier\Tableratesurepost
     */
    protected $_carrierTablerate;

    /**
     * @param \Crimson\Shipping\Model\Carrier\Tableratesurepost
     */
    public function __construct(\Crimson\Shipping\Model\Carrier\Tableratesurepost $carrierTablerate)
    {
        $this->_carrierTablerate = $carrierTablerate;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray(): array
    {
        $arr = [];
        foreach ($this->_carrierTablerate->getCode('condition_name') as $k => $v) {
            $arr[] = ['value' => $k, 'label' => $v];
        }
        
        return $arr;
    }
}
