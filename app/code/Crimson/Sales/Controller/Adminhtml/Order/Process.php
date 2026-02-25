<?php

namespace Crimson\Sales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\ResourceModel\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;

class Process extends Action
{

    public function __construct(
        Context $context,
        protected OrderRepositoryInterface $orderRepository,
        protected Filter $filter,
        protected CollectionFactory $collectionFactory
    )
    {
        parent::__construct($context);
    }


    public function execute()
    {
        try {
            $orders = $this->filter
                ->getCollection($this->collectionFactory->create())
                ->getItems();

            if(!$orders) {
                throw new \Exception(__("Please select an item to process."));
            }

            foreach ($orders as $order) {
                $this->orderRepository->deleteById($order->getId());
            }

            $this->messageManager->addSuccessMessage(__('We deleted %1 order(s).', sizeof($orders)));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            /** @var Redirect $resultRedirect */
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('sales/order');
    }
}
