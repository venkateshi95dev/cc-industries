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
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class GetMachCustomerNumber
 * @package Crimson\MachCustomer\Model\Service
 */
class GetMachCustomerNumber
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var Session
     */
    protected $customerSession;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerSession    = $customerSession;
    }

    /**
     * @param CustomerInterface|OrderInterface|DataObject|string|int|null $input
     *
     * @return string|null
     */
    public function get($input): ?string
    {
        //try to load logged in customer first.
        if (is_null($input)) {
            $input = $this->customerSession->getCustomerData();
        }

        if ($input instanceof OrderInterface) {
            if ($input->getExtensionAttributes()->getMachCustomerNumber()) {
                return $input->getExtensionAttributes()->getMachCustomerNumber();
            } elseif ($input->getCustomerId()) {
                $input = $input->getCustomerId();
            } else {
                $input = $input->getCustomerEmail();
            }
        }

        if ($input instanceof DataObject) {
            //if mach customer number is on input, return it.
            $machCustomerNumber = $input->getData('mach_customer_number');
            if ($machCustomerNumber) {
                return $machCustomerNumber;
            }
        }

        //if it is scalar try and load customer by email and id.
        $input = $this->_getCustomer($input);
        if (!$input) {
            return null;
        } elseif ($input instanceof CustomerInterface) {
            return $input->getCustomAttribute('mach_customer_number')
                ? $input->getCustomAttribute('mach_customer_number')->getValue()
                : null;
        }

        return null;
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
