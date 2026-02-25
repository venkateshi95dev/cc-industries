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

namespace Ebizcharge\Ebizcharge\Api\Data;

/**
 * How it works interface
 *
 * Interface HowItWorksInterface
 */
interface HowItWorksInterface
{
    /**
     * EbizCharge How it works logo
     *
     * @const: EBIZ_HOW_IT_WORKS_LOGO
     */
    public const EBIZ_HOW_IT_WORKS_LOGO = 'Ebizcharge_Ebizcharge::images/eBizCharge-logo-mark.svg';

    /**
     * EbizCharge How it works play video logo
     *
     * @const: EBIZ_HOW_IT_WORKS_VIDEO_PLAY_LOGO
     */
    public const EBIZ_HOW_IT_WORKS_VIDEO_PLAY_LOGO = 'Ebizcharge_Ebizcharge::images/play-button-icon.svg';

    /**
     * EbizCharge How it works links with tooltip
     *
     * @const: EBIZ_HOW_IT_WORKS_TOOLTIP_TEMPLATE
     */
    public const EBIZ_HOW_IT_WORKS_TOOLTIP_TEMPLATE = 'Ebizcharge_Ebizcharge::howItWorks/link-with-tooltip.phtml';

    /**
     * EbizCharge How it works play video logo
     *
     * @const: EBIZ_HOW_IT_WORKS_VIDEO_LABEL_LINKS
     */
    public const EBIZ_HOW_IT_WORKS_VIDEO_LABEL_LINKS = [
        'adminhtml_dashboard_index' => [
            [
                'label' => 'Customer-Facing: My Account',
                'link' => 'https://www.youtube.com/embed/iBMRIxsI778'
            ],
            [
                'label' => 'Customer-Facing: Customer Checkout Process',
                'link' => 'https://www.youtube.com/embed/orBWs8YZxhc'
            ],
            [
                'label' => 'Customer-Facing: Manage Payment Methods',
                'link' => 'https://www.youtube.com/embed/neKwXqLLaQc'
            ],
            [
                'label' => 'Customer-Facing: Manage Subscriptions',
                'link' => 'https://www.youtube.com/embed/Tzj2cjOyd9E'
            ]
        ],
        'ebizcharge_ebizcharge_recurrings_addaction' => [
            [
                'label' => 'Add Subscriptions',
                'link' => 'https://www.youtube.com/embed/Qlw55254wnY'
            ]
        ],
        'ebizcharge_ebizcharge_recurrings_index' => [
            [
                'label' => 'Subscriptions',
                'link' => 'https://www.youtube.com/embed/uF-p4YUkfyQ'
            ]
        ],
        'ebizcharge_ebizcharge_recurrings_history' => [
            [
                'label' => 'Subscription Payment History',
                'link' => 'https://www.youtube.com/embed/xwvyAlQoiSE'
            ]
        ],
        'ebizcharge_ebizcharge_recurrings_search' => [
            [
                'label' => 'Upcoming Subscription Orders',
                'link' => 'https://www.youtube.com/embed/ubJYKZ3TiRU'
            ]
        ],
        'ebizcharge_ebizcharge_recurrings_orders' => [
            [
                'label' => 'Subscription Orders',
                'link' => 'https://www.youtube.com/embed/8rqKfHZXtkE'
            ]
        ],
        'ebizcharge_ebizcharge_downloads_ebizchargeresources' => [
            [
                'label' => 'Sync Download',
                'link' => 'https://www.youtube.com/embed/fuceqEpyuNU'
            ]
        ],
        'ebizcharge_ebizcharge_uploads_ebizchargeresources' => [
            [
                'label' => 'Sync Upload',
                'link' => 'https://www.youtube.com/embed/6rUMsxDFWKs'
            ]
        ],
        'ebizcharge_ebizcharge_help_support' => [
            [
                'label' => 'Help & Contact Us',
                'link' => 'https://www.youtube.com/embed/boxwSVnK88Q'
            ]
        ],
        'customer_index_index' => [
            [
                'label' => 'Customers',
                'link' => 'https://www.youtube.com/embed/i382d0588Tk'
            ]
        ],
        'sales_order_index' => [
            [
                'label' => 'Admin-Facing: Orders',
                'link' => 'https://www.youtube.com/embed/4NmEWczJruo'
            ]
        ],
        'adminhtml_system_config_edit' => [
            [
                'label' => 'Configuration',
                'link' => 'https://www.youtube.com/embed/cfG6--pzklI'
            ]
        ]
    ];
}
