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

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column\SubscriptionOrders;

use Ebizcharge\Ebizcharge\Api\Data\FutureSubscriptionInterface;
use Ebizcharge\Ebizcharge\Api\Data\OrderSubscriptionInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * This class is used to add different classes to status as per status value
 *
 * Class StatusRenderer
 */
class StatusColumnRenderer extends Column
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
            $status = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {

                    $item[$status] = $this->getStatusHtml((int)$item[$status]);

            }
        }
        return $dataSource;
    }

    /**
     * Get status html as per status code
     *
     * @param int $status
     * @return string
     */
    private function getStatusHtml(int $status): string
    {

        $class = 'grid-severity-major';
        $label = __(OrderSubscriptionInterface::EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_FAILED_TITLE);
        switch ($status) {
            case OrderSubscriptionInterface::EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_FAILED:
                $class = 'grid-severity-minor';
                $label = __(OrderSubscriptionInterface::EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_FAILED_TITLE);
                break;
            case OrderSubscriptionInterface::EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_COMPLETED:
                $class = 'grid-severity-notice';
                $label = __(OrderSubscriptionInterface::EBIZCHARGE_SUBSCRIPTION_ORDER_STATUS_COMPLETED_TITLE);
                break;
        }

        return '<span class="' . $class . '"><span>' . $label . '</span></span>';
    }
}
