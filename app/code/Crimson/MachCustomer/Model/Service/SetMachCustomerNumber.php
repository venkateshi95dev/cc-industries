<?php
/**
 * @namespace   Crimson
 * @module      MachCustomer
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/28/2019 2:40 PM
 * @brief
 */

namespace Crimson\MachCustomer\Model\Service;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;

/**
 * Class SetMachCustomerNumber
 * @package Crimson\MachCustomer\Model\Service
 */
class SetMachCustomerNumber
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param $customer
     * @param $machCustomerNumber
     *
     * @return bool
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     */
    public function set($customer, $machCustomerNumber): bool
    {
        $customer = $this->_getCustomer($customer);
        if (!$customer || !$customer->getId()) {
            return false;
        }

        $customer->setCustomAttribute('mach_customer_number', $machCustomerNumber);

        $this->customerRepository->save($customer);

        return true;
    }

    /**
     * @param $customer
     * @param $machCustomerNumber
     *
     * @return bool|CustomerInterface|null
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     */
    public function setMachNumberAndGetCustomer($customer, $machCustomerNumber)
    {
        $customer = $this->_getCustomer($customer);
        if (!$customer || !$customer->getId()) {
            return false;
        }

        $customer->setCustomAttribute('mach_customer_number', $machCustomerNumber);

        $customer = $this->customerRepository->save($customer);

        return $customer;
    }

    /**
     * @param int|string|CustomerInterface $input - int|email|object
     *
     * @return CustomerInterface|null
     */
    protected function _getCustomer($input): ?CustomerInterface
    {
        if ($input instanceof CustomerInterface) {
            return $input;
        }

        try {
            if (is_numeric($input)) {
                return $this->customerRepository->getById($input);
            } else {
                return $this->customerRepository->get($input);
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}
