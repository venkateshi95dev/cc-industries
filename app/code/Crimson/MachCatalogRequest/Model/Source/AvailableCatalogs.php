<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/15/2019 11:06 AM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Model\Source;

use Crimson\MachCatalogRequest\Model\Config;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Option\ArrayInterface;

/***
 * Class AvailableCatalogs
 * @package Crimson\MachCatalogRequest\Model\Source
 */
class AvailableCatalogs implements OptionSourceInterface
{
    protected $_optionHash;
    protected $_options;

    const CATALOG_VALUE = 'value';
    const CATALOG_LABEL = 'label';
    const CATALOG_IMAGE_URL = 'catalog_image_url';
    const SORT_ORDER = 'sort_order';

    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if (!$this->_options) {
            $this->_options = $this->config->getAvailableCatalogs();

            if (!empty($this->_options)) {
                uasort($this->_options, function ($option1, $option2) {
                    return ($option1[self::SORT_ORDER] ?? 0) <=> ($option2[self::SORT_ORDER] ?? 0);
                });
            }
        }

        return $this->_options;
    }

    /**
     * @return mixed
     */
    public function toOptionHash()
    {
        if (!$this->_optionHash) {
            foreach ($this->toOptionArray() as $option) {
                $this->_optionHash[$option['value']] = $option['label'];
            }
        }

        return $this->_optionHash;
    }
}
