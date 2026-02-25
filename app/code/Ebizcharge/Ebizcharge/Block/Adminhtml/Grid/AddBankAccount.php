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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\Grid;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;

/**
 * Add Bank Account Block class
 *
 * Class AddBankAccount
 */
class AddBankAccount extends Container
{
    /**
     * Retrieve text for header element
     *
     * @return Phrase
     */
    public function getHeaderText()
    {
        return __('Bank Account Information');
    }

    /**
     * Get form action URL.
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/save');
    }

    /**
     * Initialize Bank Account Edit Block.
     */
    protected function _construct()
    {
        $this->_objectId = 'row_id';
        $this->_blockGroup = 'Ebizcharge_Ebizcharge';
        $this->_controller = 'adminhtml_grid';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save'));
        $this->buttonList->update(
            'back',
            'onclick',
            "setLocation('" . $this->getUrl('customer/index/edit/', [
                'id' => $this->_request->getParam('id')
            ]) . "')"
        );

        $this->buttonList->remove('reset');
    }
}
