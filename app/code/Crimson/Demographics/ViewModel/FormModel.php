<?php

namespace Crimson\Demographics\ViewModel;

use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class FormModel
 * @package Crimson\Demographics\ViewModel
 */
class FormModel implements ArgumentInterface
{
    /**
     * @var Config
     */
    protected $_eavConfig;

    public function __construct(
        Config $eavConfig
    ) {
        $this->_eavConfig = $eavConfig;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getDemographicsOptions(): array
    {
        $attribute = $this->_eavConfig->getAttribute('customer', 'car_demos');

        return $attribute->getSource()->getAllOptions();
    }
}
