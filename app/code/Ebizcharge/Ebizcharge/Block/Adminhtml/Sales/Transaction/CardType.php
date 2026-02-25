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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\Sales\Transaction;

use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Helper\Data;
use Ebizcharge\Ebizcharge\Model\Payment;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Payment card type
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

        /** @var _configResource */
        $this->_dataHelper = $dataHelper;
    }

    /**
     * Render
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        /** @var $cardLast4Digit */
        $cardLast4Digit = $row->getOrder()->getPayment()->getCcLast4();
        /** @var $ccType */
        $ccType = $row->getOrder()->getPayment()->getCcType();
        /** @var $ccOwners */
        $ccOwners = $row->getOrder()->getPayment()->getCcOwner();

        /** check the last digits */
        if (!empty($cardLast4Digit)) {

            /** @var $cardLast4Digit */
            $cardLast4Digit = str_replace('X', '', $cardLast4Digit);
            /** @var $checkingOrSaving */
            $checkingOrSaving = '';

            if ($ccType === PaymentInterface::ACH) {
                $checkingOrSaving = PaymentInterface::ACH;

                stristr($ccOwners, 'Checking') !== false && $checkingOrSaving = 'Checking';
                stristr($ccOwners, 'Savings') !== false && $checkingOrSaving = 'Savings';

                $checkingOrSaving = ucfirst($checkingOrSaving);
            }
            /** @var $cardLast4Digit */
            $cardLast4Digit = $checkingOrSaving . ' ending in ' . $cardLast4Digit;
        }

        /** @var $cardType */
        $cardType = $row->getOrder()->getPayment()->getCcType();

        if (empty($cardType)) {
            return $cardLast4Digit;
        }
        /** @var $imagePath */
        $imagePath = $this->_dataHelper->getCardImagePath($cardType);

        /** @var $imagePathUrl */
        $imagePathUrl = $this->getViewFileUrl($imagePath);
        /** @var $cardName */
        $cardName = $imagePath == '' ? $this->_dataHelper->getCardName($cardType) : ' ';

        /** @var $imageFor */
        $imageFor = 'card';

        strtolower($cardType) == 'ach' && $imageFor = 'ach';

        return $this->_dataHelper->getImageTag($imagePathUrl, $imageFor) . ' ' . $cardName . ' ' . $cardLast4Digit;
    }
}
