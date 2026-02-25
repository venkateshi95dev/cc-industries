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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\CustomerEdit\Tab\View;

use Ebizcharge\Ebizcharge\Helper\Data;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Card type and card image
 *
 * Class CardType
 */
class CardType extends AbstractRenderer
{
    /**
     * Helper Data
     *
     * @var Data
     */
    protected Data $_dataHelper;

    /**
     * CardType constructor.
     *
     * @param Context $context
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        /** @var  _dataHelper */
        $this->_dataHelper = $dataHelper;
    }

    /**
     * Renders grid column
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        /** @var $cardNumber */
        $cardNumber = $row->getData('cardNumber');

        /** @var  $cardType */
        $cardType = $row->getData('cardType');

        /** case for Bank Account */
        if ($cardNumber == null && $cardType == null) {
            $cardNumber = $row->getData('accountType');
            $cardType = 'ach';
        }

        /** @var  $imagePath */
        $imagePath = $this->_dataHelper->getCardImagePath($cardType);

        /** @var $imagePath */
        $imagePath = $this->getViewFileUrl($imagePath);

        return $this->_dataHelper->getImageTag($imagePath) . ' ' . $cardNumber;
    }
}
