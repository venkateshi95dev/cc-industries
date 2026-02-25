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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml;

use Magento\Backend\Block\Widget\Container;
use Ebizcharge\Ebizcharge\Block\Adminhtml\PaymentHistory\Grid as PaymentHistoryGrid;
use Magento\Framework\Exception\LocalizedException;

/**
 * Payment History block class
 *
 * Class PaymentHistory
 */
class PaymentHistory extends Container
{
    /**
     * Template file inclusion
     *
     * @var string
     */
    protected $_template = 'paymenthistory/view.phtml';

    /**
     * Get Grid Html
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    /**
     * Prepare Layout
     *
     * @return PaymentHistory
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()
                ->createBlock(PaymentHistoryGrid::class, 'grid.view.grid')
        );

         return parent::_prepareLayout();
    }
}
