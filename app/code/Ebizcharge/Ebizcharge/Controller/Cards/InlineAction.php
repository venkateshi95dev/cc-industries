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

namespace Ebizcharge\Ebizcharge\Controller\Cards;

use Ebizcharge\Ebizcharge\Model\TranApi;
use Exception;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Inline Credit Card Action
 *
 * Class InlineAction
 */
class InlineAction implements AccountInterface, HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var RedirectFactory
     */
    private RedirectFactory $redirectFactory;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var TranApi
     */
    private TranApi $tranApi;

    /**
     * @param RedirectFactory $redirectFactory
     * @param RequestInterface $request
     * @param TranApi $tranApi
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        TranApi $tranApi
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->request = $request;
        $this->tranApi = $tranApi;
    }

    /**
     * Execute Method
     *
     * @return false|ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $cid = $this->request->getParam('cid');
        $mid = $this->request->getParam('mid');

        if ($cid === null || $mid === null) {
            return false;
        }

        try {
            $params = [
                'securityToken' => $this->tranApi->getUeSecurityToken(),
                'customerToken' => $cid,
                'paymentMethodId' => $mid,
            ];

            $this->tranApi->getClient()->deleteCustomerPaymentMethodProfile($params);

        } catch (Exception $e) {
            return false;
        }
        return $this->redirectFactory->create()->setPath('ebizcharge/cards/listaction');
    }
}
