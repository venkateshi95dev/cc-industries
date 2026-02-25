<?php
/**
 * @namespace   Crimson
 * @module      MachBase
 * @author      Matheus Gontijo
 * @email       mgontijo@crimsonagility.com
 * @date        12/20/2018 1:51 PM
 */

namespace Crimson\MachBase\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Monolog\Logger;

/**
 * Class LogLevel
 * @package Crimson\MachBase\Model\Config\Source
 */
class LogLevel implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => Logger::CRITICAL,
                'label' => __('Critical Only')
            ],
            [
                'value' => Logger::INFO,
                'label' => __('Informational')
            ],
            [
                'value' => Logger::DEBUG,
                'label' => __('Debug')
            ],
        ];
    }

    public function toArray(): array
    {
        return [
            Logger::CRITICAL => __('Critical Only'),
            Logger::INFO     => __('Informational'),
            Logger::DEBUG    => __('Debug'),
        ];
    }
}
