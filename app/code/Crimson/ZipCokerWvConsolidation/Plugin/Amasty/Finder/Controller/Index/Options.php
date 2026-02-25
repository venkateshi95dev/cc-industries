<?php

namespace Crimson\ZipCokerWvConsolidation\Plugin\Amasty\Finder\Controller\Index;


use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class Options
{
    public function __construct(
        private \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        private \Amasty\Finder\Model\Dropdown $dropdown
    )
    {
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function aroundExecute(\Amasty\Finder\Controller\Index\Options $subject, callable $proceed)
    {
        $parentId = $subject->getRequest()->getParam('parent_id', false);
        $dropdownId = $subject->getRequest()->getParam('dropdown_id');
        $useSavedValues = $subject->getRequest()->getParam('use_saved_values', "false");
        $options = [];

        if ($parentId !== false && $dropdownId) {
            /** @var \Amasty\Finder\Model\Dropdown $dropdown */
            $dropdown = $this->dropdown->load($dropdownId);
            $selectedValue = 0;
            if($useSavedValues === "true") {
                $selectedValue = $dropdown->getFinder()->getSavedValue($dropdown->getId());
            }
            $options  = $dropdown->getOptions($parentId, $selectedValue, false);
            array_unshift($options, ['value'=>'', 'label'=>__($dropdown->getName())]);

        }

        $response = $this->jsonEncoder->encode($options);
        return $subject->getResponse()->setBody($response);
    }
}
