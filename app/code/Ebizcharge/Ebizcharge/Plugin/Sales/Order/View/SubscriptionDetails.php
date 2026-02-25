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

namespace Ebizcharge\Ebizcharge\Plugin\Sales\Order\View;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\OrderSubscriptionRepositoryInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Ebizcharge\Ebizcharge\Model\Recurring;
use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer;

/**
 * Subscription details column in admin sales order view
 *
 * Class SubscriptionDetails
 */
class SubscriptionDetails
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var RecurringRepositoryInterface
     */
    protected RecurringRepositoryInterface $recurringRepository;

    /**
     * @var OrderSubscriptionRepositoryInterface
     */
    protected OrderSubscriptionRepositoryInterface $orderSubscriptionRepository;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;
    /**
     * @var ConfigFactory
     */
    protected ConfigFactory $configFactory;

    /**
     * Main Class Constructor
     *
     * @param OrderSubscriptionRepositoryInterface $orderSubscriptionRepository
     * @param RecurringRepositoryInterface $recurringRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ConfigFactory $configFactory
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        OrderSubscriptionRepositoryInterface $orderSubscriptionRepository,
        RecurringRepositoryInterface $recurringRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ConfigFactory $configFactory,
        EbizchargeLogger $ebizchargeLogger
    ) {
        /** @var $searchCriteriaBuilder */
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        /** @var $recurringRepository */
        $this->recurringRepository = $recurringRepository;
        /** @var $orderSubscriptionRepository */
        $this->orderSubscriptionRepository = $orderSubscriptionRepository;
        /** @var $ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        $this->configFactory = $configFactory;
    }

    /**
     * After plugin to add subscription details
     *
     * @param DefaultRenderer $subject
     * @param mixed $result
     * @param DataObject $item
     * @param string $column
     * @param null|mixed $field
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws NoSuchEntityException
     * @since 100.1.0
     */
    public function afterGetColumnHtml(
        DefaultRenderer $subject,
        $result,
        DataObject $item,
        $column,
        $field = null
    ) {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if($isEbizChargeActive) {
            if ($column === RecurringInterface::SUBSCRIBED_PRODUCT_COLUMN_TITLE) {

                return $this->renderReccuredItemColumnSubscription($item);
            }
        }
        return $result;
    }

    /**
     * Show subscription in order history page
     *
     * @param DataObject $item
     * @return string
     */
    public function renderReccuredItemColumnSubscription(DataObject $item)
    {
        try {
            /** @var  $order */
            $order = $item->getOrder();
            /** @var  $rawData */
            $productOptions = $item->getData("product_options");
            $infoBuyRequest = isset($productOptions['info_buyRequest']) ? $productOptions['info_buyRequest'] : [];
            $recurringData = isset($infoBuyRequest["recurring"]) ? $infoBuyRequest["recurring"] : [];
            $recurringFrequency = ucfirst($recurringData["rec_frequency"] ?? '');

            /** if recurring data is available */
            if (is_array($recurringData) && count($recurringData) > 0 && !empty($recurringFrequency)) {
                $recurringQty = isset($infoBuyRequest['qty']) ? $infoBuyRequest['qty'] : 0;
                $startDate = isset($recurringData['sdate']) ? $recurringData['sdate'] : '';
                $endDate = isset($recurringData['edate']) ? $recurringData['edate'] : '';
                $isRecurringIndefinte = isset($recurringData['rec_indefinitely']) &&
                    (int)$recurringData['rec_indefinitely'] > 0;

                $finalString = '<div class="ordersub">Status: <strong>' . __("Subscribed") . '</strong></div>';
                $finalString .= '<div class="ordersub">Frequency: <strong>' . $recurringFrequency . '</strong></div>';
                $finalString .= '<div class="ordersub">Qty Subscribed: <strong>' . $recurringQty . '</strong></div>';
                $finalString .= '<div class="ordersub">Start Date: <strong>' . $startDate . '</strong></div>';

                if ($isRecurringIndefinte) {
                    $finalString .= '<div class="ordersub"> <strong>' . "Indefinite(" .
                        Recurring::DEFAULT_INDEFINITE_RECURRING_LIMIT . " Year)" . '</strong></div>';
                } else {
                    $finalString .= '<div class="ordersub">End Date: <strong>' . $endDate . '</strong></div>';
                }

            } else {
                $finalString = '<div class="ordersub">' . __('Not subscribed') . '</div>';
            }

            /** logging the final subscription */
            $this->ebizchargeLogger->addInfo(__('Final subscription ' . $finalString));

            return $finalString;
        } catch (Exception $ex) {
            /** Logging the exception to the logger */
            $this->ebizchargeLogger->addError(__('Exception occurred and no record found ' . $ex->getMessage()));

            return '<div class="ordersub">' . __('Not subscribed') . '</div>';
        }
    }
}
