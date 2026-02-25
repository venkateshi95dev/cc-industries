<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 3:07 PM
 * @brief
 */

namespace Crimson\MachCatalog\Model\Service\UpdateProducts;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface ProcessorInterface
 * @package Crimson\MachCatalog\Model\Service\UpdateProducts
 */
interface ProcessorInterface
{

    /**
     * @param ProductInterface $product
     *
     * @return void
     * @throws LocalizedException
     */
	public function process(ProductInterface $product): void;
}
