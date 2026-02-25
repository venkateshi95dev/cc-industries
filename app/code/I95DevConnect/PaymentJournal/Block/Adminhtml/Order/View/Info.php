<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentJournal
 */

namespace I95DevConnect\PaymentJournal\Block\Adminhtml\Order\View;

use I95DevConnect\PaymentJournal\Model\PaymentJournalFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

/**
 * Block for displaying cash receipt information in order view page
 * @api
 */
class Info extends Template
{
    /**
     * @var PaymentJournalFactory
     */
    public $paymentJournalFactory;

    /**
     * Core registry
     * @var Registry
     */
    public $registry;
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param PaymentJournalFactory $paymentJournalFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PaymentJournalFactory $paymentJournalFactory,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->paymentJournalFactory = $paymentJournalFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get current order
     *
     * @return string|null
     * @author Hrusikesh Manna
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Get Cash Receipt Id
     *
     * @return array|null
     * @author Hrusikesh Manna
     */
    public function getCashReceipt()
    {
        $order = $this->getOrder();
        $customCollection = $this->paymentJournalFactory->create()->getCollection();
        $customCollection->addFieldToSelect('receipt_id')
                ->addFieldToFilter('source_order_id', $order->getId());
        $customCollection->getSelect()->limit(1);
        return $customCollection->getData();
    }
}
