<?php

namespace Crimson\Catalog\Model\Layer\Filter;

class Category extends \Magento\CatalogSearch\Model\Layer\Filter\Category
{

	/**
	 * Checks whether the option reduces the number of results
	 *
	 * @param int $optionCount Count of search results with this option
	 * @param int $totalSize Current search results count
	 * @return bool
	 */
	protected function isOptionReducesResults($optionCount, $totalSize): bool
    {
		return $optionCount <= $totalSize;
	}
}
