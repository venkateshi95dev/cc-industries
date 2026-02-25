<?php

namespace Crimson\Demographics\Block;

use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;

/**
 * Class Demographics
 * @package Crimson\Demographics\Block
 */
class Demographics extends Template
{
    /**
     * @var Config
     */
    protected $_eavConfig;

    public function __construct(
        Template\Context $context,
        Config $eavConfig,
        array $data = []
    ) {
        $this->_eavConfig = $eavConfig;
        parent::__construct($context, $data);
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
