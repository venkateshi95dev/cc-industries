<?php

namespace Crimson\CorvetteCentralOSCO\Observer;

use Crimson\CorvetteCentralOSCO\UI\DataProvider\Product\Form\Modifier\Packages;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\SerializerInterface;
use I95DevConnect\MessageQueue\Helper\Data;


class SaveCCPackages implements ObserverInterface
{

    public function __construct(
        protected RequestInterface $request,
        protected SerializerInterface $serializer,
        protected Data $data
    ) {}

    public function execute(Observer $observer)
    {

        if ($this->data->getGlobalValue('i95_observer_skip') || $this->request->getParam('isI95DevRestReq') == 'true') {
            return;
        }
        /** @var $product Product */
        $product = $observer->getEvent()->getProduct();
        if (!$product) {
            return;
        }

        $packagesData = $product[Packages::PRODUCT_ATTRIBUTE_CODE] ?? [];
        if (is_array($packagesData) && !empty($packagesData)) {
            $packagesData = $this->removeEmptyArray($packagesData);
            $product->setData(Packages::PRODUCT_ATTRIBUTE_CODE, $this->serializer->serialize($packagesData));
        } else {
            $product->setData(Packages::PRODUCT_ATTRIBUTE_CODE, $this->serializer->serialize([]));
        }

    }

    private function removeEmptyArray(array $packagesData): array
    {
        foreach ($packagesData as $packageId => $packageData) {
            if (empty($packageData['length']) ||
                empty($packageData['width']) ||
                empty($packageData['height']) ||
                empty($packageData['weight'])
            ) {
                unset($packagesData[$packageId]);
                continue;
            }

            $packagesData[$packageId]['length'] = number_format($packageData['length'], 4, '.', '');
            $packagesData[$packageId]['width']  = number_format($packageData['width'], 4, '.', '');
            $packagesData[$packageId]['height'] = number_format($packageData['height'], 4, '.', '');
            $packagesData[$packageId]['weight'] = number_format($packageData['weight'], 4, '.', '');
        }

        return $packagesData;
    }
}
