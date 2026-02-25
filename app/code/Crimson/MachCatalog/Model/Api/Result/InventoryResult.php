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
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class Inventory
 *
 * @package Crimson\MachCatalog\Model\Api\Result
 * @method InventoryResult setProduct(ProductInterface $product)
 *
 * @method ProductInterface|null getProduct()
 */
class InventoryResult extends AbstractResult
{
    const STATUS_CODE_AVAILABLE = 'A';
    const STATUS_CODE_INACTIVE  = 'I';

    /**
     * @return string
     */
    public function getStatusCode(): string
    {
        return $this->_getDataWithDefault('status_code', self::STATUS_CODE_AVAILABLE);
    }

    /**
     * @return float|bool - false on failure or not returned.
     */
    public function getQtyAvailable()
    {
        return $this->_getDataWithDefault('qty_available', false);
    }

    /**
     * @return float|bool - false on failure or not returned.
     */
    public function getListPrice()
    {
        return $this->_getDataWithDefault('list_price', false);
    }

    /**
     * @return bool
     */
    public function getIsDropShip(): bool
    {
        return (bool) $this->_getDataWithDefault('drop_ship', false);
    }

    /**
     * @return bool
     */
    public function getIsGiftCertificate(): bool
    {
        return (bool) $this->_getDataWithDefault('gift_certificate', false);
    }

    /**
     * @return bool
     */
    public function getIsTaxable(): bool
    {
        return (bool) $this->_getDataWithDefault('taxable', true);
    }

    /**
     * @return bool
     */
    public function getHasCoreCharge(): bool
    {
        return (bool) $this->_getDataWithDefault('has_core_charge', false);
    }

    /**
     * @return float
     */
    public function getCoreChargeAmount(): float
    {
        return (float) $this->_getDataWithDefault('core_charge_amount', 0.00);
    }

    /**
     * @return string|null
     */
    public function getCoreChargeSku(): ?string
    {
        return $this->_getDataWithDefault('core_charge_sku');
    }

    /**
     * @return float
     */
    public function getAdditionalAmount(): float
    {
        return (float) $this->_getDataWithDefault('additional_amount', 0.00);
    }
}
