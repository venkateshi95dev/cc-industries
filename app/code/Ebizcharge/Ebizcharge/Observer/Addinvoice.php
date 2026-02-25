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

namespace Ebizcharge\Ebizcharge\Observer;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\Data;
use Ebizcharge\Ebizcharge\Model\Order\Invoice;
use Ebizcharge\Ebizcharge\Helper\Data as EbizDataHelper;
use Exception;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Observe and upload invoice to EConnect automatically
 *
 * Class Addinvoice
 */
class Addinvoice implements ObserverInterface
{
    /**
     * @var SessionFactory
     */
    private SessionFactory $customerSession;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var Data
     */
    private $dataClass;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var EbizchargeLogger
     */
    private EbizchargeLogger $ebizchargeLogger;

    /**
     * @var Invoice
     */
    private Invoice $invoiceFactory;
    /**
     * @var SessionManagerInterface
     */
    private SessionManagerInterface $sessionManagerInterface;

    /**
     * @param Config $config
     * @param Data $dataClass
     * @param Order $order
     * @param OrderRepositoryInterface $orderRepository
     * @param SessionFactory $customerSession
     * @param Invoice $invoiceFactory
     * @param SessionManagerInterface $sessionManagerInterface
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Config                   $config,
        Data                     $dataClass,
        Order                    $order,
        OrderRepositoryInterface $orderRepository,
        SessionFactory           $customerSession,
        Invoice                  $invoiceFactory,
        SessionManagerInterface  $sessionManagerInterface,
        EbizchargeLogger         $ebizchargeLogger
    ) {
        /** @var  config */
        $this->config = $config;
        /** @var  dataClass */
        $this->dataClass = $dataClass;
        /** @var  order */
        $this->order = $order;
        /** @var  customerSession */
        $this->customerSession = $customerSession;
        /** @var  orderRepository */
        $this->orderRepository = $orderRepository;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var invoiceFactory */
        $this->invoiceFactory = $invoiceFactory;
        /** @var  sessionManagerInterface */
        $this->sessionManagerInterface = $sessionManagerInterface;
    }

    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     * @return Addinvoice
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isEbizchargeActive() == 0) {
            /** Ebizcharge Payment Method is not active logging to the logger */
            $this->ebizchargeLogger->addError(__('EBizCharge module is not active in system configuration'));

            return $this;
        }

        /*if ($this->config->isEconnectUploadEnabled() == 0) {
            $this->ebizchargeLogger->addInfo(__('EBizCharge Hub Upload not Enabled from your system configuration'));

            return $this;
        }*/

        try {
            $invoice = $observer->getEvent()->getInvoice();

            if ($this->isAdmin()) {
                $currentOrderEntityId = $invoice->getOrderId();

                $order = $this->orderRepository->get($currentOrderEntityId);
                $invoiceCollection[] = $invoice;
            } else {
                $orderIds = $observer->getEvent()->getOrderIds();
                $currentOrderEntityId = $orderIds[0];
                $order = $this->orderRepository->get($currentOrderEntityId);
                $invoiceCollection = $order->getInvoiceCollection();
            }

            if (!empty($currentOrderEntityId) && !empty($invoiceCollection)) {
                if ($this->customerSession->create()->isLoggedIn() || $this->isAdmin()) {
                    /** @var adding invoice to Ebizcharge $uploadInvoiceToEbizcharge */
                    $uploadInvoiceToEbizcharge = $this->invoiceFactory->createInvoiceToEbizcharge(
                        $order->getEntityId()
                    );
                    // var_dump("<pre>", $uploadInvoiceToEbizcharge);exit;
                }
            }
        } catch (LocalizedException $localizedException) {
            /** logging logs of invoice to the logger */
            $this->ebizchargeLogger->addInfo(__( 'Could not add invoice to EBizCharge! ' . $localizedException->getMessage()));
        }
        return $this;
    }

    /**
     * Check is Admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        try {
            /** logging to the logger with current Appstate */
            $this->ebizchargeLogger->addInfo(__('Current loaded Appstate is : ' . Area::AREA_ADMINHTML));

            return EbizDataHelper::getAreaCode() == Area::AREA_ADMINHTML;
        } catch (Exception $e) {
            /** Logging to the logger the error  */
            $this->ebizchargeLogger->addInfo(__('Error occurred during changing the AppState ' .
                $e->getMessage()));
            return false;
        }
    }

    /**
     * Log added
     *
     * @param mixed $message
     * @param mixed $level
     * @return void
     */
    public function log($message, $level = null)
    {
        $this->ebizchargeLogger->addInfo(__($message));
    }
}
