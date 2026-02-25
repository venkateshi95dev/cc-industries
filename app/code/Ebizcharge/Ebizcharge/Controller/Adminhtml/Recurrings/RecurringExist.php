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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\UrlInterface;
use Zend_Db_Expr;

/**
 * To check Recurring exists
 *
 * Class RecurringExist
 */
class RecurringExist extends Action implements HttpPostActionInterface
{
    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @var RecurringRepositoryInterface
     */
    private RecurringRepositoryInterface $recurringRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var EbizchargeLogger
     */
    private EbizchargeLogger $ebizchargeLogger;

    /**
     * Main Class Constructor
     *
     * @param Context $context
     * @param RequestInterface $request
     * @param RecurringRepositoryInterface $recurringRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param JsonFactory $jsonFactory
     * @param UrlInterface $urlBuilder
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Context $context,
        RequestInterface $request,
        RecurringRepositoryInterface $recurringRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        JsonFactory $jsonFactory,
        UrlInterface $urlBuilder,
        EbizchargeLogger $ebizchargeLogger
    ) {
        parent::__construct($context);

        /** @var  urlBuilder */
        $this->urlBuilder = $urlBuilder;
        /** @var  jsonFactory */
        $this->jsonFactory = $jsonFactory;
        /** @var  recurringRepository */
        $this->recurringRepository = $recurringRepository;
        /** @var  searchCriteriaBuilder */
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        /** @var  request */
        $this->request = $request;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Execute Method
     *
     * @return mixed
     * @throws NotFoundException
     */
    public function execute()
    {
        if ($this->request->isAjax()) {
            return $this->isSubscriptionAlreadyExist();
        }
        $this->ebizchargeLogger->addError(__("Invalid Request for fetching recurring "));
        throw new NotFoundException(__('Invalid Request for fetching recurring '));
    }

    /**
     * If recurring already exist
     *
     * @return Json
     */
    private function isSubscriptionAlreadyExist(): Json
    {
        $customerId = $this->request->getParam('customer_id');
        $indefinitRecurring = $this->request->getParam('rec_indefinite');
        $startDate = $this->request->getParam('start_date');
        $endDate = $this->request->getParam('end_date');

        if ($indefinitRecurring && empty($endDate)) {
            $endDate = date('Y-m-d', strtotime('+10 years'));
        }

        $productId = $this->request->getParam('product_id');
        $recId = $this->request->getParam('rec_id');
        /**
         * Search Criteria
         */
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(RecurringInterface::MAGE_CUST_ID, $customerId)
            ->addFilter(RecurringInterface::MAGE_ITEM_ID, $productId)
            ->addFilter(new Zend_Db_Expr("date(eb_rec_start_date)"), $startDate, 'gteq')
            ->addFilter(new Zend_Db_Expr("date(eb_rec_end_date)"), $endDate, 'lteq');

        if (!empty($recId)) {
            $searchCriteria->addFilter(RecurringInterface::REC_ID, $recId, 'neq');
        }
        $result = $this->recurringRepository->getList($searchCriteria->create());

        if ($result->getTotalCount() > 0) {
            $firstItem = (array)$result->getItems();
            $firstItem = reset($firstItem);

            $redirectUrl = $this->urlBuilder->getUrl(
                '*/*/editaction',
                [
                    '_secure' => true,
                    'mid' => $firstItem->getEbRecScheduledPaymentInternalId(),
                    'id' => $firstItem->getEntityId(),
                    'magcid' => $firstItem->getMageCustId()
                ]
            );
            $response = [
                'status' => 'E',
                'recid' => $firstItem->getEntityId(),
                'id' => $firstItem->getEntityId(),
                'mid' => $firstItem->getEbRecScheduledPaymentInternalId(),
                'magcid' => $firstItem->getMageCustId(),
                'message' => sprintf(
                    // phpcs:ignore
                    "The selected product already subscribed with id <a href='%s'>%s</a>. Please select a different date range.",
                    $redirectUrl,
                    $recId
                )
            ];
        } else {
            $response = ['status' => 'P'];
        }
        $resultJson = $this->jsonFactory->create();
        $resultJson->setData($response);

        return $resultJson;
    }
}
