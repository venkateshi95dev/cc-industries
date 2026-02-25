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

use Ebizcharge\Ebizcharge\Model\Config;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Text;
use Magento\Framework\DataObject;

/**
 * Payment History Result Renderer
 *
 * Class ResultRenderer
 */
class ResultRenderer extends Text
{
    /**
     * Admin Grid Class
     *
     * @const ADMIN_GRID_CLASS
     */
    public const ADMIN_GRID_CLASS = 'history';

    /**
     * Result Code Accepted
     *
     * @const: RESULT_CODE_ACCEPTED
     */
    public const RESULT_CODE_ACCEPTED = 'accepted';

    /**
     * Result Declined
     *
     * @const RESULT_CODE_DECLINED
     */
    public const RESULT_CODE_DECLINED = 'declined';

    /**
     * Result Rejcted
     *
     * @const RESULT_CODE_REJECTED
     */
    public const RESULT_CODE_REJECTED = 'rejected';

    /**
     * @var Config
     */
    protected Config $_ebizchargeConfigModel;

    /**
     * ResultRenderer constructor.
     *
     * @param Context $context
     * @param Config $ebizchargeConfigModel
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $ebizchargeConfigModel,
        array $data = []
    ) {
        parent::__construct($context, $data);

        /** @var _ebizchargeConfigModel */
        $this->_ebizchargeConfigModel = $ebizchargeConfigModel;
    }

    /**
     * Render Row
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row): string
    {
        $rowResult = (array)$row->getData('Response');
        return $this->_getStatusHtml($rowResult['ResultCode']);
    }

    /**
     * Response of Status
     *
     * @param string $resultCodeResponse
     * @return string
     */
    protected function _getStatusHtml(string $resultCodeResponse): string
    {
        $resultCode = $this->_ebizchargeConfigModel->getTransactionResponseType($resultCodeResponse);
        $resultCode = strtolower((string)$resultCode);

        $class = 'grid-severity-critical';
        $label = __('Not found');

        switch ($resultCode) {
            case self::RESULT_CODE_ACCEPTED:
                $class = 'grid-severity-notice';
                $label = __('Approved');
                break;
            case self::RESULT_CODE_DECLINED:
                $class = 'grid-severity-minor';
                $label = __('Declined');
                break;
            case self::RESULT_CODE_REJECTED:
                $label = __('Rejected');
                break;
        }
        /** @var $actionName */
        $actionName = $this->getRequest()->getActionName();

        return $actionName == self::ADMIN_GRID_CLASS ? '<span class="' . $class . '"><span>' .
            $label . '</span></span>' : (string)$label;
    }
}
