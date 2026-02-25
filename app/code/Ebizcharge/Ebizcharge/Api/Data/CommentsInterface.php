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

/**
 * Interface CommentsInterface
 *
 * Comments Data Interface
 */
interface CommentsInterface
{
    /**
     * Ebizcharge API Payments Credit Card
     *
     * @const: EBIZCHARGE_API_PAYMENTS_CREDIT_CARD
     */
    public const EBIZCHARGE_API_PAYMENTS_CREDIT_CARD = "Credit Card";

    /**
     * Ebizcharge Api Payments ACH
     *
     * @const: EBIZCHARGE_API_PAYMENTS_ACH
     */
    public const EBIZCHARGE_API_PAYMENTS_ACH = "ACH/Bank Account";

    /**
     * Ebizcharge Api Yes Comments
     *
     * @const: EBIZCHARGE_API_YES_COMMENTS
     * @phpcs:disable
     */
    public const EBIZCHARGE_API_YES_COMMENTS = "Select \"Yes\" to enable %s payments.";

    /**
     * Ebizcharge Api No Comments
     *
     * @cosnt: EBIZCHARGE_API_NO_COMMENTS
     */
    public const EBIZCHARGE_API_NO_COMMENTS = "%s payments are disabled at EBizCharge Payment portal. Please enable them from there and then hit the save button so to changes take effect at checkout.";
    // phpcs:enable

    /**
     * Retrieve element comment by element value
     *
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue);
}
