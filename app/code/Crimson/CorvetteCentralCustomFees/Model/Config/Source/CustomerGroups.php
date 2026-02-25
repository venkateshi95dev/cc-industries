<?php
namespace Crimson\CorvetteCentralCustomFees\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

class CustomerGroups implements ArrayInterface
{
    private CollectionFactory $groupCollectionFactory;

    public function __construct(CollectionFactory $groupCollectionFactory)
    {
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    public function toOptionArray(): array
    {
        $options = [];

        // All customer groups included "NOT LOGGED IN" group
        $groups = $this->groupCollectionFactory->create();
        foreach ($groups as $group) {
            if($group->getCode() == 'NOT LOGGED IN' || str_contains($group->getCode(), 'CC-'))
            $options[] = [
                'value' => $group->getId(),
                'label' => $group->getCode()
            ];
        }

        return $options;
    }
}
