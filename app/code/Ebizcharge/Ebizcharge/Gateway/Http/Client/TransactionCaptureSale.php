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

namespace Ebizcharge\Ebizcharge\Gateway\Http\Client;

/**
 * Http client Transaction Capture Sale
 *
 * Class TransactionCaptureSale
 */
class TransactionCaptureSale extends AbstractTransaction
{
    /**
     * Process Method
     *
     * @param array $data
     * @return mixed
     */
    protected function process(array $data)
    {
        $storeId = $data['store_id'] ?? null;
        unset($data['store_id']);

        return $this->adapterFactory->create($storeId)->sale($data);
    }
}
