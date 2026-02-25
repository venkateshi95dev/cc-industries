<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/10/2019
 */
namespace Crimson\Brand\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface BrandSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return \Crimson\Brand\Api\Data\BrandInterface[]
     */
    public function getItems();

    /**
     * @param \Crimson\Brand\Api\Data\BrandInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}