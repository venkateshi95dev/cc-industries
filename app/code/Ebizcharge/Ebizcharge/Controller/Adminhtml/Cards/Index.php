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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Cards;

use Magento\Customer\Controller\Adminhtml\Index as CustomerIndex;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;

/**
 * Credit cards Index page
 *
 * Class Index
 */
class Index extends CustomerIndex
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_cards_index';

    /**
     * Execute Method
     *
     * @return ResponseInterface|ResultInterface|Layout
     */
    public function execute()
    {
        return $this->resultLayoutFactory->create();
    }
}
