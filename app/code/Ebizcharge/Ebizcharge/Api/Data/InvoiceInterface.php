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

use Magento\Sales\Api\Data\InvoiceInterface as CoreInvoiceInterface;

/**
 * Interface InvoiceInterface
 *
 * Invoice Data Interface
 */
interface InvoiceInterface extends CoreInvoiceInterface
{
    /**
     *
     * Ebizcharge Invoice Sync Status
     *
     * @const: EBIZ_INVOICE_SYNC_STATUS
     */
    public const EBIZ_INVOICE_SYNC_STATUS = 'ec_invoice_sync_status';

    /**
     * Ebizcharge Invoice Internal Id
     *
     * @const: EBIZ_INVOICE_INTERNAL_ID
     */
    public const EBIZ_INVOICE_INTERNAL_ID = 'ec_invoice_internalid';

    /**
     *
     * Ebizcharge Invoice Id
     *
     * @const: EBIZ_INVOICE_ID
     */
    public const EBIZ_INVOICE_ID = 'ec_invoice_id';

    /**
     *
     * Ebizcharge Customer Id
     *
     * @const: EBIZ_CUSTOMER_ID
     */
    public const EBIZ_CUSTOMER_ID = 'ec_cust_id';

    /**
     * Ec Invoice Lastsync Date
     *
     * @const: EBIZ_INVOICE_LASTSYNCDATE
     */
    public const EBIZ_INVOICE_LASTSYNCDATE = 'ec_invoice_lastsyncdate';

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
     * @const INVOICE_TYPE
     */
    public const INVOICE_TYPE = "invoice";

    /**
     * Get Software Id
     *
     * @return string
     */
    public function getSoftwareId(): string;

    /**
     * Set Software Id
     *
     * @param mixed $ecSoftwareId
     * @return InvoiceInterface
     */
    public function setSoftwareId($ecSoftwareId): InvoiceInterface;

    /**
     * Get Division Id
     *
     * @return string
     */
    public function getDivisionId(): string;

    /**
     * Set Division Id
     *
     * @param mixed $ecDivisionId
     * @return InvoiceInterface
     */
    public function setDivisionId($ecDivisionId): InvoiceInterface;

    /**
     * Get Ec Invoice Sync Status
     *
     * @return mixed
     */
    public function getEcInvoiceSyncStatus();

    /**
     * Get Ebizcharge Invoice Internal Id
     *
     * @return mixed
     */
    public function getEcInvoiceInternalid();

    /**
     * Get Ec Invoice Id
     *
     * @return mixed
     */
    public function getEcInvoiceId();

    /**
     * Get Ebizchare Customer Id
     *
     * @return mixed
     */
    public function getEcCustId();

    /**
     * Get Ebizcharge Invoice Last Sync Date
     *
     * @return mixed
     */
    public function getEcInvoiceLastsyncdate();

    /**
     * Set Ebizcharge Invoice Status
     *
     * @param mixed $ebizInvoiceStatus
     * @return mixed
     */
    public function setEcInvoiceSyncStatus($ebizInvoiceStatus);

    /**
     * Sets Ebizcharge Invoice Internal Id
     *
     * @param mixed $ebizInvoiceInternalId
     * @return mixed
     */
    public function setEcInvoiceInternalid($ebizInvoiceInternalId);

    /**
     * Sets Ebizcharge Invoice Id
     *
     * @param mixed $ebizInvoiceId
     * @return mixed
     */
    public function setEcInvoiceId($ebizInvoiceId);

    /**
     * Set Ebizcharge Customer Id
     *
     * @param mixed $ebizCustomerId
     * @return mixed
     */
    public function setEcCustId($ebizCustomerId);

    /**
     * Set Ebizcharge Invoice Last Sync Date
     *
     * @param mixed $ebizInvoiceLastSyncDate
     * @return mixed
     */
    public function setEcInvoiceLastsyncdate($ebizInvoiceLastSyncDate);

    /**
     * Get Ebizcharge Surcharge Amount
     *
     * @return mixed
     */
    public function getEcSurchargeAmount();

    /**
     * Get Ebizcharge Surcharge Percentage
     *
     * @return mixed
     */
    public function getEcSurchargePercentage();

    /**
     * Get Ebizcharge Surcharge Ineligible
     *
     * @return mixed
     */
    public function getEcSurchargeIneligible();

    /**
     * Set Ebizcharge Surcharge Amount
     *
     * @param mixed $ebizSurchargeAmount
     * @return InvoiceInterface
     */
    public function setEcSurchargeAmount($ebizSurchargeAmount);

    /**
     * Set Ebizcharge Surcharge Percentage
     *
     * @param mixed $ebizSurchargePercentage
     * @return InvoiceInterface
     */
    public function setEcSurchargePercentage($ebizSurchargePercentage);

    /**
     * Set Ec Surcharge Ineligible
     *
     * @param float|null $surchargeIneligible
     * @return InvoiceInterface
     */
    public function setEcSurchargeIneligible($surchargeIneligible);
}
