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

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Payment method renderer
 *
 * Class PaymentMethod
 */
class PaymentMethod extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $paymentMethod = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$paymentMethod])) {
                    $item[$paymentMethod] = $this->getPaymentMethod($item[$paymentMethod]);
                }
            }
        }
        return $dataSource;
    }

    /**
     * Get Payment Method
     *
     * @param mixed $paymentMethodName
     * @return string
     */
    private function getPaymentMethod($paymentMethodName)
    {
        $methodName = trim($paymentMethodName);
        $methodNameParts = explode(' ', trim($methodName));
        $methodNamePart2 = isset($methodNameParts[1]) ? $methodNameParts[1] : '';
        $methodNamePart1 = isset($methodNameParts[0]) ? $methodNameParts[0] : '';
        $methodName = $methodNamePart1."-".$methodNamePart2;
        $nums = explode(" ", $methodNamePart2);
        $cardEndingNumber = false;

        foreach ($nums as $num) {
            if ((strlen($num) === 4) && (preg_match_all('!\d+!', $num))) {
                $cardEndingNumber = $num;
                break;
            }
        }
        $paymentMethodJson = json_decode($paymentMethodName);
        if (is_object($paymentMethodJson)) {
            $methodName = $paymentMethodJson->a."-".$paymentMethodJson->b;
        }

        if ($cardEndingNumber) {
            return $methodName . ' ending in ' . $cardEndingNumber;
        }

        return $methodName;
    }
}
