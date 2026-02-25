<?php

namespace Crimson\AutoInvoice\Model;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;

class Creater
{
    public function __construct(
        private readonly Logger $logger,
        private readonly \Magento\Sales\Model\Service\InvoiceService $_invoiceService,
        private readonly \Magento\Framework\DB\Transaction $_transaction,
        private readonly \Magento\Store\Model\StoreManagerInterface $storeManager,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly FilterBuilder $filterBuilder
    ) {}

    public function execute()
    {
        //loop approved orders and create capture
        $orders = $this->getOrders();
        if ($orders) {
            foreach ($orders as $order) {
                $receiver = $order->getShippingAddress() ? $order->getShippingAddress() : $order->getBillingAddress();
                if ($order->canInvoice() && $receiver->getCountryId() == 'US') {
                    try {
                        $invoice = $this->_invoiceService->prepareInvoice($order);
                        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                        $invoice->register();
                        $invoice->save();
                        $transactionSave = $this->_transaction->addObject(
                            $invoice
                        )->addObject(
                            $invoice->getOrder()
                        );
                        $transactionSave->save();
                        $invoice->getOrder()->save();
                        $order->setStatus('auto_invoiced');
                        $order->addStatusToHistory('auto_invoiced', 'Automatically Invoiced.');
                        $this->orderRepository->save($order);
                    } catch (\Exception $e) {
                        $error_msg = "Faield to create invoice for order ID " . $order->getIncrementId() . ": " . $e->getMessage();
                        $this->logger->info($error_msg);
                    }
                }
            }
        }
    }

    private function getOrders()
    {
        $cokerTireStoreId        = $this->storeManager->getStore(CokerStoreInterface::COKER_STORE_CODE)->getId();
        $cokerTireDefaultStoreId = $this->storeManager->getStore(CokerStoreInterface::COKER_DEFAULT_STORE_CODE)->getId();
        $wvStoreId               = $this->storeManager->getStore(WVStoreInterface::WV_STORE_CODE)->getId();
        $filters = [
            $this->filterBuilder->setField('store_id')->setValue(
                [$cokerTireStoreId,$cokerTireDefaultStoreId,$wvStoreId]
            )->setConditionType('in')->create(),
            $this->filterBuilder->setField('ext_order_id')->setValue(0)->setConditionType('gt')->create(),
            $this->filterBuilder->setField('status')->setValue('processing')->setConditionType('eq')->create(),
            $this->filterBuilder->setField('total_invoiced')->setConditionType('null')->create(),
            $this->filterBuilder->setField('created_at')->setValue('2023-09-24')->setConditionType('gt')->create(),
        ];
        $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)->create();
        $orders = $this->orderRepository->getList($searchCriteria);
        if($orders->getTotalCount() > 0)
            return $orders->getItems();
        return null;
    }

}
