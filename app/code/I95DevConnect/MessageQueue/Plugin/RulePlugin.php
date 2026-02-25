<?php

/**
 * Copyright � Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 * @author Arushi Bansal
 */

namespace I95DevConnect\MessageQueue\Plugin;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\CatalogRule\Model\ResourceModel\Rule;

/**
 * Class RulePlugin for disabling default
 */
class RulePlugin
{
    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * Rule plugin constructor.
     *
     * @param Data $dataHelper
     */
    public function __construct(
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Get catalog rules product price for specific date, website and customer group
     *
     * @param Rule $subject
     * @param object $result
     *
     * @return float|false
     */
    public function afterGetRulePrice(Rule $subject, $result) //NOSONAR
    {
        if ($this->dataHelper->getGlobalValue('i95_skip_final_price')) {
            return false;
        } else {
            return $result;
        }
    }
}
