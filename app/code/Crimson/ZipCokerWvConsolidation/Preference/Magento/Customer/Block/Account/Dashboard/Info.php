<?php
namespace Crimson\ZipCokerWvConsolidation\Preference\Magento\Customer\Block\Account\Dashboard;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\SubscriberFactory;

class Info extends \Magento\Customer\Block\Account\Dashboard\Info
{
    /**
     * Constructor
     *
     * @param Context $context
     * @param CurrentCustomer $currentCustomer
     * @param SubscriberFactory $subscriberFactory
     * @param View $helperView
     * @param array $data
     */
    public function __construct(
        Context $context,
        CurrentCustomer $currentCustomer,
        SubscriberFactory $subscriberFactory,
        View $helperView,
        private readonly \Magento\Eav\Model\Entity\Attribute $attribute,
        private readonly \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $eavCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context,$currentCustomer,$subscriberFactory, $helperView, $data);
    }

    public function loadAttribute($attributeCode)
    {
        $entityType = 'customer';
        return $this->attribute->loadByCode($entityType, $attributeCode);
    }

    public function getAttributeOptionSingle($attributeId, $value)
    {
        $attributeOptionSingle = $this->eavCollectionFactory->create();
        return $attributeOptionSingle->setAttributeFilter($attributeId)
            ->setIdFilter($value)
            ->setStoreFilter()
            ->load()
            ->getFirstItem();
    }
}
