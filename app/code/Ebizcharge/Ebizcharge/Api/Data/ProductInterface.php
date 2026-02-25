<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Api\Data;

use Magento\Catalog\Api\Data\ProductInterface as CoreProductInterface;

/**
 * Interface ProductInterface
 *
 * Product Data Interface
 */
interface ProductInterface extends CoreProductInterface
{
    /**
     * Econnect item Sync Status
     *
     * @const EC_ITEM_SYNC_STATUS
     */
    public const EC_ITEM_SYNC_STATUS = 'ec_item_sync_status';

    /**
     * Econnect item Internal Id
     *
     * @const EC_ITEM_INTERNALID
     */
    public const EC_ITEM_INTERNALID = 'ec_item_internalid';

    /**
     * Econnect itemId
     * @const EC_ITEM_ID
     */
    public const EC_ITEM_ID = 'ec_item_id';

    /**
     * Econnect item Last Sync Date
     *
     * @const EC_ITEM_LASTSYNCDATE
     */
    public const EC_ITEM_LASTSYNCDATE = 'ec_item_lastsyncdate';

    /**
     * Econnect Created In
     *
     * @const EC_CREATED_IN
     */
    public const EC_CREATED_IN = 'ec_created_in';

    /**
     * Ebizcharge Software Id
     *
     * @const: EBIZCHARGE_SOFTWARE_ID
     */
    public const EBIZCHARGE_SOFTWARE_ID = 'ec_software_id';

    /**
     * Ebizcharge Division Id
     *
     * @const: EBIZCHARGE_DIVISION_ID
     */
    public const EBIZCHARGE_DIVISION_ID = 'ec_division_id';

    /**
     * Ebizcharge Product Soap Node Price List
     *
     * @const: EBIZCHARGE_PRODUCT_SOAP_NODE_PRICE_LIST
     */
    public const EBIZCHARGE_PRODUCT_SOAP_NODE_PRICE_LIST = 'PriceList';

    /**
     * Get Software Id
     *
     * @return string|null;
     */
    public function getSoftwareId(): string|null;

    /**
     * Set Software Id
     *
     * @param mixed $ecSoftwareId
     * @return ProductInterface
     */
    public function setSoftwareId($ecSoftwareId): ProductInterface;

    /**
     * Get Division Id
     *
     * @return string|null;
     */
    public function getDivisionId(): string|null;

    /**
     * Set Division Id
     *
     * @param mixed $ecDivisionId
     * @return ProductInterface
     */
    public function setDivisionId($ecDivisionId): ProductInterface;

    /**
     * Get EC Itemd Id
     *
     * @return mixed
     */
    public function getEcItemId();

    /**
     * Set EC Item ID
     *
     * @param mixed $ecItemId
     * @return mixed
     */
    public function setEcItemId($ecItemId);

    /**
     * Get Ebizcharge item Sync Status
     *
     * @return mixed
     */
    public function getEcItemSyncStatus();

    /**
     * Set Ebizcharge item Sync Status
     *
     * @param mixed $ecItemSyncStatus
     * @return $this
     */
    public function setEcItemSyncStatus($ecItemSyncStatus);

    /**
     * Get Ebizcharge item Internal Id
     *
     * @return mixed
     */
    public function getEcItemInternalId();

    /**
     * Set Ebizcharge item Internal Id
     *
     * @param mixed $ecItemInternalId
     * @return $this
     */
    public function setEcItemInternalId($ecItemInternalId);

    /**
     * Get Ebizcharge item Last Sync Date
     *
     * @return mixed
     */
    public function getEcItemLastSyncDate();

    /**
     * Set Ebizcharge item Last Sync Date
     *
     * @param mixed $ecItemLastSyncDate
     * @return $this
     */
    public function setEcItemLastSyncDate($ecItemLastSyncDate);

    /**
     * Get EcCredated In
     *
     * @return mixed
     */
    public function getEcCreatedIn();

    /**
     * Set EC Created In
     *
     * @param mixed $ecCreatedIn
     * @return mixed
     */
    public function setEcCreatedIn($ecCreatedIn);

    /**
     * Software Id
     *
     * @const SOFTWARE_ID
     */
    public const SOFTWARE_ID = "Magento2";

    /**
     * Low Stock Cron Email Templat
     *
     * @const: LOW_STOCK_CRON_EMAIL_TEMPLATE
     */
    public const LOW_STOCK_CRON_EMAIL_TEMPLATE = 'payment_us_ebizcharge_ebizcharge_low_stock_email';

    /**
     * Default Attribute Set Id
     *
     * @const DEFAULT_ATTRIBUTE_SET_ID
     */
    public const DEFAULT_ATTRIBUTE_SET_ID = 4;

    /**
     * Manage Config Stock
     *
     * @const MANAGE_CONFIG_STOCK
     */
    public const DEFAULT_MANAGE_CONFIG_STOCK = 0;

    /**
     * Product Type at Ebizcharge Service
     *
     * @const: EBIZCHARGE_PRODUCT_TYPE_SERVICE
     */
    public const EBIZCHARGE_PRODUCT_TYPE_SERVICE = 'service';

    /**
     * Product Tye at Ebizcharge Sale
     *
     * @const: EBIZCHARGE_PRODUCT_TYPE_SALE
     */
    public const EBIZCHARGE_PRODUCT_TYPE_SALE = 'sale';

    /**
     * Default Manage Stock
     *
     * @const DEFAULT_MANAGE_STOCK
     */
    public const DEFAULT_MANAGE_STOCK = 1;

    /**
     * Default Is product In Stock
     *
     * @const DEFAULT_IS_PRODUCT_IN_STOCK
     */
    public const DEFAULT_IS_PRODUCT_IN_STOCK = 1;

    /**
     * Default Product Qty
     *
     * @const EBIZCHARGE_PRODUCT_DEFAULT_QTY
     */
    public const EBIZCHARGE_PRODUCT_DEFAULT_QTY = 0;

    /**
     * Default Product Name
     *
     * @const EBIZCHARGE_PRODUCT_DEFAULT_NAME
     */
    public const EBIZCHARGE_PRODUCT_DEFAULT_NAME = '';

    /**
     *  Product Type Configurable
     *
     * @const: PRODUCT_TYPE_CONFIGURABLE
     */
    public const PRODUCT_TYPE_CONFIGURABLE = 'configurable';

    /**
     * Default Product Description
     *
     * @const EBIZCHARGE_PRODUCT_DEFAULT_DESCRIPTION
     */
    public const EBIZCHARGE_PRODUCT_DEFAULT_DESCRIPTION = '';

    /**
     * Product Default Taxable
     *
     * @const EBIZCHARGE_PRODUCT_DEFAULT_TAXABLE
     */
    public const EBIZCHARGE_PRODUCT_DEFAULT_TAXABLE = false;

    /**
     * Default Unit of Measurement
     *
     * @const EBIZCHARGE_PRODUCT_DEFAULT_UNIT_OF_MEASUREMEANT
     */
    public const EBIZCHARGE_PRODUCT_DEFAULT_UNIT_OF_MEASUREMEANT = 'kg';

    /**
     * Default Product Price
     *
     * @const EBIZCHARGE_PRODUCT_DEFAULT_PRICE
     */
    public const EBIZCHARGE_PRODUCT_DEFAULT_PRICE = 0;

    /**
     * Discount Product Amount
     *
     * @const EBIZCHARGE_PRODUCT_DEFAULT_DISCOUNT
     */
    public const EBIZCHARGE_PRODUCT_DEFAULT_DISCOUNT = 0;

}
