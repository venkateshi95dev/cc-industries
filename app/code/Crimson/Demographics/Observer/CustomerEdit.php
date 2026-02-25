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
 * Class CustomerEdit
 * @package Crimson\Demographics\Observer
 */
class CustomerEdit implements ObserverInterface
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

    /**
     * CustomerEdit constructor.
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param CustomerRepositoryInterface $customerRepository
     */
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
     * Process car demos after customer updated account
     *
     * @param Observer $observer
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();

        $customerEmail = $event->getData('email');

        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerRepository->get($customerEmail);

        $carDemosParams = $this->request->getParam('car_demos', []);

        $carDemos = implode(',', $carDemosParams);

        if ($carDemos) {
            $customer->setCustomAttribute('car_demos', $carDemos);
            $this->customerRepository->save($customer);
        }
    }
}
