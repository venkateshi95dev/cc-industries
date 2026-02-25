<?php
/**
 * @namespace   Crimson
 * @module      MachBase
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 6:06 PM
 * @brief
 */

namespace Crimson\MachBase\Model\Service;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class GetMachSku
 * @package Crimson\MachBase\Model\Service
 */
class GetMachSku
{
    /**
     * @param ProductInterface|string $product
     *
     * @return string
     */
    public function get($product): string
    {
        if ($product instanceof ProductInterface) {
            $product = $product->getSku();
        }

        if ($product === 'DB-323.Left Front') {
            return 'DB-323.LF';
        }

        if ($product == 'VW-1438.Medium') {
            return 'VW-1438.MD';
        }

        return $product;
    }
}
