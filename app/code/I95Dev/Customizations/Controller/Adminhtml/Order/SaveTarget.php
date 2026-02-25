<?php
namespace I95Dev\Customizations\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use I95DevConnect\MessageQueue\Model\SalesOrder;
use I95DevConnect\MessageQueue\Api\Data\I95DevMagMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Model\ResourceModel\I95DevMagMQ\CollectionFactory;

class SaveTarget extends Action
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var SalesOrder
     */
    protected $customSalesOrder;

    /**
     * @var I95DevMagMQInterfaceFactory
     */
    protected $I95DevMagMQData;

    /**
     * @var I95DevMagMQRepositoryInterfaceFactory
     */
    protected $I95DevMagMQRepository;

    /**
     * @var CollectionFactory
     */
    protected $mqCollectionFactory;
 
    public function __construct(
        Action\Context $context,
        OrderRepositoryInterface $orderRepository,
        SalesOrder $customSalesOrder,
        I95DevMagMQInterfaceFactory $I95DevMagMQData,
        I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQRepository,
        CollectionFactory $mqCollectionFactory
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->customSalesOrder = $customSalesOrder;
        $this->I95DevMagMQData = $I95DevMagMQData;
        $this->I95DevMagMQRepository = $I95DevMagMQRepository;
        $this->mqCollectionFactory = $mqCollectionFactory;
    }

    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh the page.'));
            return $this->resultRedirectFactory->create()->setPath('sales/order/index');
        }

        $post = $this->getRequest()->getPostValue();

        if (!$post || empty($post['order_id'])) {
            $this->messageManager->addErrorMessage(__('Missing order ID.'));
            return $this->resultRedirectFactory->create()->setPath('sales/order/index');
        }

        try {
            $order = $this->orderRepository->get($post['order_id']);
            $sourceOrderId = $order->getIncrementId();

            // Get custom order using the same pattern as Response.php
            $customOrder = $this->getCustomOrder($sourceOrderId);
            
            if ($customOrder->getId()) {
                // Update custom order data
                $customOrder->setTargetOrderId($post['target_order_id'] ?? null);
                $customOrder->setTargetOrderStatus($post['target_order_status'] ?? null);
                $customOrder->setUpdatedAt(date('Y-m-d H:i:s'));
                $customOrder->setUpdateBy('Admin');
                $customOrder->save();

                // Update outbound message queue
                $this->updateOutboundMessageQueue($sourceOrderId, $post['target_order_id'] ?? null);

            $this->messageManager->addSuccessMessage(
                __('Order NAV ID updated successfully.')
            );
            } else {
                $this->messageManager->addErrorMessage(
                    __('Custom order record not found.')
                );
            }

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('Unable to update target order data: %1', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Unable to update target order data.')
            );
        }

        return $this->resultRedirectFactory->create()->setPath(
            'sales/order/view',
            ['order_id' => (int)$post['order_id']]
        );
    }
 
    /**
     * Retrieve i95dev custom order by source order id
     *
     * @param string $sourceOrderId
     * @return SalesOrder
     */
    protected function getCustomOrder($sourceOrderId)
    {
        $customOrderModel = $this->customSalesOrder;
        $customOrderData = $customOrderModel->getCollection()
            ->addFieldToSelect('id')
            ->addFieldToFilter('source_order_id', $sourceOrderId);
        $customOrderData->getSelect()->limit(1);
        $customOrderData = $customOrderData->getData();

        $customOrderId = (isset($customOrderData[0]['id']) ? $customOrderData[0]['id'] : '');

        return $customOrderModel->load($customOrderId);
    }

    /**
     * Update outbound message queue using repository pattern
     *
     * @param string $sourceOrderId
     * @param string|null $targetOrderId
     * @return void
     */
   protected function updateOutboundMessageQueue($sourceOrderId, $targetOrderId)
   {
    try {
        $mqCollection = $this->mqCollectionFactory->create()
            ->addFieldToFilter('magento_id', $sourceOrderId);
        
        foreach ($mqCollection as $mqRecord) {
            // Load fresh instance using the data factory
            $I95DevMagMQ = $this->I95DevMagMQData->create();
            
            // Set the message ID from existing record
            $I95DevMagMQ->setMsgId($mqRecord->getMsgId());
            $I95DevMagMQ->setTargetId($targetOrderId);
            $I95DevMagMQ->setStatus(5);
            $I95DevMagMQ->setUpdatedby('Admin');
            
            // Save using repository
            $this->I95DevMagMQRepository->create()->saveMQData($I95DevMagMQ);
        }
    } catch (\Exception $e) {
        // Log error but don't throw - this is a secondary operation
    }
}

}