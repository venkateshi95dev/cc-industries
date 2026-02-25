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

namespace Ebizcharge\Ebizcharge\Model\Config;

use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Framework\Option\ArrayInterface;

/**
 * Customer Receipts Email Templates Model class
 *
 * Class CustomerReceiptsEmailTemplates
 */
class CustomerReceiptsEmailTemplates implements ArrayInterface
{
    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * CustomerReceiptsEmailTemplates constructor.
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        CustomerFactory $customerFactory
    ) {
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        /** @var $optionsArray */
        $optionsArray = $this->getCustomerReceiptEmailTemplates();
        return $optionsArray['array_options_resp'];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        /** @var $optionsArray */
        $optionsArray = $this->getCustomerReceiptEmailTemplates();
        return $optionsArray['array_resp'];
    }

    /**
     * Get Customer Receipt Email Templates
     *
     * @return array
     */
    public function getCustomerReceiptEmailTemplates()
    {
        $optionResponse = [];

        /** @var $toArrayResponse */
        $toArrayResponse[] = [
            '' => __('Please Select Email Template')
        ];
        /** @var $toOptionArrayResponse */
        $toOptionArrayResponse[] = [
            'value' => '',
            'label' => __('Please Select Email Template')
        ];

        /** @var $emailTemplates */
        $emailTemplates = $this->_customerFactory->create()->getEbizCustomerReceiptEmailTemplates();

        if (count($emailTemplates) > 0) {
            foreach ($emailTemplates as $emailTemplate) {
                $emailTemplate = (array)$emailTemplate;
                $toArrayResponse[$emailTemplate['TemplateInternalId']] = $emailTemplate['TemplateSubject'];
                $toOptionArrayResponse[] = [
                    'value' => $emailTemplate['TemplateInternalId'],
                    'label' => __($emailTemplate['TemplateSubject'])
                ];
            }
        }

        $optionResponse['array_resp'] = $toArrayResponse;
        $optionResponse['array_options_resp'] = $toOptionArrayResponse;

        return $optionResponse;
    }
}
