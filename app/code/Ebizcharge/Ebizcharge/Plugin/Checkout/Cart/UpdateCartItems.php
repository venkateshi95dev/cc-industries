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

namespace Ebizcharge\Ebizcharge\Plugin\Checkout\Cart;

use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Exception;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\QuoteFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory;
use Magento\Checkout\Model\Cart as Subject;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Default config provider Plugin
 *
 * Class DefaultConfigProvider
 */
class UpdateCartItems
{

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $messageManagerInterface;

    /**
     * @var QuoteFactory
     */
    protected QuoteFactory $quoteFactory;

    /**
     * @var ConfigFactory
     */
    protected ConfigFactory $configFactory;

    /**
     * Main Constructor of the Class
     *
     * @param RequestInterface $request
     * @param ManagerInterface $messageManagerInterface
     * @param QuoteFactory $quoteFactory
     * @param ConfigFactory $configFactory
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        RequestInterface $request,
        ManagerInterface $messageManagerInterface,
        QuoteFactory     $quoteFactory,
        ConfigFactory $configFactory,
        EbizchargeLogger $ebizchargeLogger
    )
    {
        /** @var $request */
        $this->request = $request;
        /** @var $messageManagerInterface */
        $this->messageManagerInterface = $messageManagerInterface;
        /** @var $ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var $quoteFactory */
        $this->quoteFactory = $quoteFactory;
        /** @var $configFactory */
        $this->configFactory = $configFactory;
    }

    /**
     * @param CartRepositoryInterface $subject
     * @param \Closure $proceed
     * @param CartInterface $quote
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function aroundSave(
        CartRepositoryInterface $subject,
        \Closure                $proceed,
        CartInterface           $quote
    )
    {
        /** @var  $productReqParams */
        $productReqParams = $this->request->getParams();
        $productId = $this->request->getParam("product");
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if($isEbizChargeActive) {

            if ($productId) {
                try {
                    $productReqParams["product"] = $productId ?? "";

                    if (!isset($productReqParams["recurring"]["rec_activate"]) && !empty($productReqParams["recurring"]["rec_activate"])) {
                        $productReqParams["is_group"] = true;
                    }
                    /**
                     * Checking if Product already exists
                     */
                    $isProductSubscribed = $this->quoteFactory->create()->isSubscriptionExistsBeforeItemPlugin($productReqParams);
                    //  $updateProductInCart = $this->quoteFactory->create()->updateSubscribedProductInCart($quote, $productReqParams);

                    return $proceed($quote);
                } catch (Exception $exception) {
                    $this->ebizchargeLogger->addCritical(__("Exception occurred during adding subscriptions, please try to edit it in My Accounts "));
                }
            }
        }
        return $proceed($quote);
    }


    /**
     * Before plugin for addProduct method
     *
     * @param Subject $subject
     * @param \Magento\Catalog\Model\Product $product
     * @param $buyRequest
     * @return array
     * @throws LocalizedException
     */
    public function AddProduct(Subject $subject, $product, $buyRequest)
    {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if($isEbizChargeActive) {
            try {
                $productId = $product->getId();
                $productReqParams = $this->request->getParams();
                $productReqParams["product"] = $productId;
                if (!isset($productReqParams["recurring"]["rec_activate"])) {
                    $productReqParams["is_group"] = true;
                }
                /**
                 * Checking if Product already exists
                 */
                $isProductSubscribed = $this->quoteFactory->create()->isSubscriptionExistsBeforeItemPlugin($productReqParams);

                /**
                 * if Is Subscription against this product already exists
                 */
                if ($isProductSubscribed) {
                    $this->request->setParam('product', false);
                    $this->request->setParam('return_url', false);
                    // phpcs:disable
                    $this->messageManagerInterface->addErrorMessage(__('Subscription already exist between selected dates.
            Please choose any other subscription dates or edit existing subscriptions. Please browse to:  (My Account -> Manage Subscriptions)'));

                    $this->ebizchargeLogger->addCritical(__($productReqParams['product'] . " - This product already subscribed, please try to edit it in My Accounts "));
                    // phpcs:enable
                    return [$product];
                }


            } catch (Exception $exception) {
                $this->ebizchargeLogger->addCritical(__("Exception occurred during adding subscriptions, please try to edit it in My Accounts "));
            }
        }
        return [$product, $buyRequest];
    }
}
