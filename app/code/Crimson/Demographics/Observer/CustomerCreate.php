<?php

namespace Crimson\Demographics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class CustomerCreate
 * @package Crimson\Demographics\Observer
 */
class CustomerCreate implements ObserverInterface
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param Observer $observer
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();

        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $event->getData('customer');

        $carDemosParams = $this->request->getParam('car_demos', []);

        $carDemos = implode(',', $carDemosParams);

        if ($carDemos) {
            $customer->setCustomAttribute('car_demos', $carDemos);
            $this->customerRepository->save($customer);
        }
    }
}
