<?php

namespace I95DevConnect\ErrorData\Model\ConfigValues;

use Magento\Framework\Option\ArrayInterface;
use \I95DevConnect\MessageQueue\Helper\Data as MqDataHelper;

/**
 * Class Outbound Entities to get all enable entities.
 */
class OutboundEntities implements ArrayInterface
{
    /**
     * @var MqDataHelper
     */
    private $mqHelper;

    /**
     * Entities constructor.
     *
     * @param MqDataHelper $mqHelper
     */
    public function __construct(
        MqDataHelper $mqHelper
    ) {
        $this->mqHelper = $mqHelper;
    }

    /**
     * Return option values for entity configuration
     *
     * @return array
     */
    public function toOptionArray()
    {
        $entities = $this->mqHelper->getEntityTypeOutboundList();
        $activeEntities = [];
        $i = 0;
        foreach ($entities as $code => $name) {
            $activeEntities[$i]['value'] = $code;
            $activeEntities[$i]['label'] = $name;
            $i++;
        }
        return $activeEntities;
    }
}
