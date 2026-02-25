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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;

class ConfigNavigationController extends Action
{
    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param Action\Context $context
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Action\Context $context,
        RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context);

        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * @return ResultInterface|ResponseInterface|Redirect
     */
    public function execute(): ResultInterface|ResponseInterface|Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl(
            $this->_url->getUrl(
                'adminhtml/system_config/edit/section/payment'
            ) . '#row_payment_us_ebizcharge_ebizcharge'
        );
        return $resultRedirect;
    }
}
