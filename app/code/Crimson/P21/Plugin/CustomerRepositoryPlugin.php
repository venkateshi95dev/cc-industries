<?php
declare(strict_types=1);

namespace Crimson\P21\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtensionFactory;

class CustomerRepositoryPlugin
{
    public function __construct(
        protected CustomerExtensionFactory $customerExtensionFactory
    ) {
    }

    /**
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     */
    public function afterGetById(
        CustomerRepositoryInterface $subject,
        CustomerInterface $customer
    ): CustomerInterface {
        $p21Attribute = $customer->getCustomAttribute('p21_account_id');
        if (!$customer->getExtensionAttributes()) {
            $extensionAttributes = $this->customerExtensionFactory->create();
        } else {
            $extensionAttributes = $customer->getExtensionAttributes();
        }

        if (!$extensionAttributes->getP21AccountId() && $p21Attribute) {
            $extensionAttributes->setP21AccountId($p21Attribute->getValue());
        }

        $customer->setExtensionAttributes($extensionAttributes);
        return $customer;
    }

    /**
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @param null|string $passwordHash
     * @return array
     */
    public function beforeSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface $customer,
        $passwordHash = null
    ): array {
        $extensionAttributes = $customer->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getP21AccountId()) {
            $customer->setCustomAttribute('p21_account_id', $extensionAttributes->getP21AccountId());
        }

        return [$customer, $passwordHash];
    }
}
