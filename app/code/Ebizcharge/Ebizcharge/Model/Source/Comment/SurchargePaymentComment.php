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

namespace Ebizcharge\Ebizcharge\Model\Source\Comment;

use Ebizcharge\Ebizcharge\Api\Data\CommentsInterface;
use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Config\Model\Config\CommentInterface;
use Magento\Framework\Phrase;

/**
 * Surcharge enabled/disabled comment
 *
 * Class SurchargePaymentComment
 */
class SurchargePaymentComment implements CommentInterface, CommentsInterface
{
    /**
     * @var Config
     */
    private Config $configModel;
    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $customerFactory;

    /**
     * Customer Factory
     *
     * @param Config $configModel
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Config $configModel,
        CustomerFactory $customerFactory
    ) {
        /** @var $configModel */
        $this->configModel = $configModel;
        /** @var $customerFactory */
        $this->customerFactory = $customerFactory;
    }

    /**
     * Get comment Text
     *
     * @param string $elementValue
     * @return Phrase|string
     */
    public function getCommentText($elementValue)
    {
        $customerFactory = $this->customerFactory->create();
        $storeId = $this->configModel->getStoreId();
        /** @var $surchargeSettings */
        $surchargeSettings = $customerFactory->getSurchargeSettings($storeId);

        if (isset($surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_ENABLED]) &&
            $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_ENABLED] !== false) {
            return __(str_replace(
                '%s',
                SurchargeInterface::EBIZCHARGE_API_PAYMENTS_SURCHARGE,
                self::EBIZCHARGE_API_YES_COMMENTS
            ));
        }

        return '<div class="message message-warning">' . __(str_replace(
            '%s',
            SurchargeInterface::EBIZCHARGE_API_PAYMENTS_SURCHARGE,
            self::EBIZCHARGE_API_NO_COMMENTS
        )) . '</div>';
    }
}
