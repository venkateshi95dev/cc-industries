<?php
declare(strict_types=1);

namespace Crimson\Catalog\Plugin\Block\Product\Compare;

use Magento\Catalog\Block\Product\Compare\ListCompare;

class SortByComparePosition
{
    /**
     * @param ListCompare $subject
     * @param array $result
     * @return array
     */
    public function afterGetAttributes(ListCompare $subject, array $result) : array
    {
        usort($result, function ($a, $b) {
            return ($a->getData('compare_position') ?? 0) <=> ($b->getData('compare_position') ?? 0);
        });

        return $result;
    }
}
