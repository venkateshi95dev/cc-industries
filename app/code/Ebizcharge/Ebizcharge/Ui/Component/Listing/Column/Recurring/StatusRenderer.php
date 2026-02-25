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

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column\Recurring;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Model\Recurring;
use Ebizcharge\Ebizcharge\Model\RecurringFactory;
use Exception;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * This class is used to add different classes to status as per status value
 *
 * Class StatusRenderer
 */
class StatusRenderer extends Column
{
    /**
     * @var RecurringFactory
     */
    protected RecurringFactory $_recurringFactory;
    /**
     * @var Recurring
     */
    protected Recurring $_recurringModel;


    /**
     * @param ContextInterface $context
     * @param Recurring $recurringModel
     * @param RecurringFactory $recurringFactory
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(

        ContextInterface   $context,
        Recurring          $recurringModel,
        RecurringFactory   $recurringFactory,
        UiComponentFactory $uiComponentFactory,
        array              $components = [],
        array              $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        /** @var  _recurringFactory */
        $this->_recurringFactory = $recurringFactory;
        /** @var  _recurringModel */
        $this->_recurringModel = $recurringModel;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @throws Exception
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $status = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$status])) {
                    $item[$status] = $this->getStatusHtml($item, $status);
                }
            }
        }
        return $dataSource;
    }

    /**
     * @param array $item
     * @param string $status
     * @return string
     * @throws Exception
     */
    private function getStatusHtml(array $item, string $status = ''): string
    {
        $class = 'grid-severity-major';
        $label = __(RecurringInterface::EBIZCHARGE_RECURRING_STATUSES[RecurringInterface::EBIZCHARGE_RECURRING_STATUS_EXPIRED]);

        /** @var $status */
        $status =  $item[$status];
        switch ($status) {
            case RecurringInterface::EBIZCHARGE_RECURRING_STATUS_ACTIVE:
                $class = 'grid-severity-notice';
                $label = RecurringInterface::EBIZCHARGE_RECURRING_STATUSES[RecurringInterface::EBIZCHARGE_RECURRING_STATUS_ACTIVE];
                break;
            case RecurringInterface::EBIZCHARGE_RECURRING_STATUS_SUSPENDED:
                $class = 'grid-severity-minor';
                $label = RecurringInterface::EBIZCHARGE_RECURRING_STATUSES[RecurringInterface::EBIZCHARGE_RECURRING_STATUS_SUSPENDED];
                break;
            case RecurringInterface::EBIZCHARGE_RECURRING_STATUS_EXPIRED:
                $label = RecurringInterface::EBIZCHARGE_RECURRING_STATUSES[RecurringInterface::EBIZCHARGE_RECURRING_STATUS_EXPIRED];
                break;
            case RecurringInterface::EBIZCHARGE_RECURRING_STATUS_CANCELED:
                $class = 'grid-severity-critical';
                $label = RecurringInterface::EBIZCHARGE_RECURRING_STATUSES[RecurringInterface::EBIZCHARGE_RECURRING_STATUS_CANCELED];
                break;
        }

        return '<span class="' . $class . '"><span>' . $label . '</span></span>';
    }
}
