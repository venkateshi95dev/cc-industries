<?php
declare(strict_types=1);

namespace Crimson\Theme\Plugin\Filter;
use Magento\Framework\Filter\StripTags as FilterStripTags;
use Magento\Framework\Phrase;

class StripTags
{
    /**
     * @param FilterStripTags $filterStripTags
     * @param mixed $value
     * @return array|string[]
     */
    public function beforeFilter(FilterStripTags $filterStripTags, mixed $value): array
    {
        if (is_array($value)) {
            return [implode(', ', $value)];
        } elseif ($value instanceof Phrase) {
            return [$value->render()];
        }
        return [(string) $value];
    }
}
