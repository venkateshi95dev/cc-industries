<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/11/2019 4:02 PM
 * @brief
 */

namespace Crimson\MachCatalog\Model\Api\Result;

use Crimson\MachBase\Model\Api\Result\AbstractResult;

/**
 * Class PriceResult
 *
 * @package Crimson\MachCatalog\Model\Api\Result
 * @method PriceResult setProduct(\Magento\Catalog\Api\Data\ProductInterface $product)
 * @method PriceResult setCustomerNumber(int $customerNumber)
 * @method PriceResult setRequestedQty(int $requestedQty)
 *
 * @method \Magento\Catalog\Api\Data\ProductInterface|null getProduct()
 * @method int|null getCustomerNumber()
 */
class PriceResult extends AbstractResult
{
    /**
     * @return float
     */
    public function getRequestedQty(): float
    {
        return (float) $this->_getDataWithDefault('requested_qty', 1);
    }

    /**
     * @return float
     */
    public function getQtyAvailable(): float
    {
        return (float) $this->_getDataWithDefault('qty_available', 0);
    }

    /**
     * @return float
     */
    public function getQtyBackordered(): float
    {
        return (float) $this->_getDataWithDefault('qty_backordered', 0);
    }

    /**
     * @return int
     */
    public function getLeadTimeInDays(): int
    {
        return (int) $this->_getDataWithDefault('lead_time', 0);
    }

    /**
     * Final Price (or price) is the price of this item for the specified customer.
     *
     * @return float|boolean
     */
    public function getFinalPrice()
    {
        return $this->_getDataWithDefault('final_price', false);
    }

    /**
     * Standard price is the normal price for the item (not including any customer specific discounts)
     *
     * @return float
     */
    public function getStandardPrice(): float
    {
        if ($this->_getDataWithDefault('standard_price', false) !== false) {
            return (float) $this->_getData('standard_price');
        }

        return (float) $this->getProduct()->getPrice();
    }
}
