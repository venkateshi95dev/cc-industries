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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\PaymentHistory;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Date;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

/**
 * Date Renderer block class
 *
 * Class DateRenderer
 */
class DateRenderer extends Date
{
    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * Main Constructor of the File
     *
     * @param DateTime $dateTime
     * @param Context $context
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param array $data
     */
    public function __construct(
        DateTime $dateTime,
        Context $context,
        DateTimeFormatterInterface $dateTimeFormatter,
        array $data = []
    ) {
        /** Parent reconstruct */
        parent::__construct($context, $dateTimeFormatter, $data);
        /** @var  dateTime */
        $this->dateTime = $dateTime;
    }

    /**
     * Render dateTime
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row): string
    {
        return $this->dateTime->date('Y-m-d', $row->getData('paymentDate'));
    }
}
