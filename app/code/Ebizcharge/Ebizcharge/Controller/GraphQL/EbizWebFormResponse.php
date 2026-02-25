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

namespace Ebizcharge\Ebizcharge\Controller\GraphQL;

use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;

/**
 * Ebiz Web Form Response Action class
 *
 * Class EbizWebFormResponse
 */
class EbizWebFormResponse extends Action implements ActionInterface
{
    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param Context $context
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerFactory
    ) {
        $this->_customerFactory = $customerFactory;
        parent::__construct($context);
    }

    /**
     * Execute function
     *
     * @return ResponseInterface|ResultInterface|Page
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        /** @var  $searchParams */
        $graphQLResponseParams = $this->getRequest()->getParams();

        $cartId = 15;
        $storeId = 1;
        $url = "http://m246.local/ebizcharge/graphql/ebizwebformresponse";

        /** @var  $webform */
        $webform = $this->_customerFactory->create()->prepareEbizWebFormUrl($cartId, $url, $url, $url, $storeId);

        // phpcs:disable
        var_dump("<pre>", $graphQLResponseParams);
        var_dump($webform);
        exit;
        // return $this->_pageFactory->create();
        // phpcs:enable
    }
}
