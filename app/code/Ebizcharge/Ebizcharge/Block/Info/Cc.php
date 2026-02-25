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

namespace Ebizcharge\Ebizcharge\Block\Info;

use Exception;
use Magento\Framework\DataObject;

/**
 * Cc Info block class
 *
 * Class Cc
 */
class Cc extends \Magento\Payment\Block\Info\Cc
{
    /**
     * Prepare credit card related payment info
     *
     * @param DataObject|array $transport
     * @return DataObject
     * @throws Exception
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = parent::_prepareSpecificInformation($transport);
        $data = [];

        if ($this->getCcTypeName() == 'ACH') {
            if ($ccType = $this->getCcTypeName()) {
                $data[(string)__('Payment Type')] = $ccType;
            }

            if ($this->getInfo()->getCcLast4()) {
                $data[(string)__('Account Number')] = sprintf(
                    'xxxx%s',
                    substr($this->getInfo()->getCcLast4(), -4)
                );
            }
        } else {
            if ($ccType = $this->getCcTypeName()) {
                $data[(string)__('Credit Card Type')] = $ccType;
            }

            if ($this->getInfo()->getCcLast4()) {
                $data[(string)__('Credit Card Number')] = sprintf(
                    'xxxx-%s',
                    $this->getInfo()->getCcLast4()
                );
            }
        }

        if (!$this->getIsSecureMode()) {

            if ($ccSsIssue = $this->getInfo()->getCcSsIssue()) {
                $data[(string)__('Switch/Solo/Maestro Issue Number')] = $ccSsIssue;
            }
            $year = $this->getInfo()->getCcSsStartYear();
            $month = $this->getInfo()->getCcSsStartMonth();

            if ($year && $month) {
                $data[(string)__('Switch/Solo/Maestro Start Date')] = $this->_formatCardDate(
                    $year,
                    $month
                );
            }
        }
        return $transport->setData(array_merge($data, $transport->getData()));
    }
}
