<?php

namespace Crimson\CorvetteCentralNewsletter\Plugin;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Newsletter\Model\SubscriptionManager;
use Magento\Newsletter\Model\Subscriber;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManager;

class SubscriptionManagerPlugin
{
    public function __construct(
        private RequestInterface            $request,
        private CustomerRepositoryInterface $customerRepository,
        private StoreManager $storeManager
    ) {
    }

    /**
     * Add additional fields to the subscriber after successful subscription.
     *
     * @param SubscriptionManager $subject
     * @param Subscriber $result
     * @param string $email
     * @param int $storeId
     * @return Subscriber
     * @throws InputException
     * @throws Exception
     */
    public function afterSubscribe(
        SubscriptionManager $subject,
        Subscriber $result,
        string $email,
        int $storeId
    ): Subscriber {
        $CCStoreId = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();
        if ($storeId==$CCStoreId && $result->getStatus() === Subscriber::STATUS_SUBSCRIBED) {
            $data = $this->validateFields();
            $this->updateSubscriber($result, $data);
        }
        return $result;
    }

    /**
     * Add additional fields to the subscriber after successful customer subscription.
     *
     * @param SubscriptionManager $subject
     * @param Subscriber $result
     * @param int $customerId
     * @param int $storeId
     * @return Subscriber
     * @throws LocalizedException
     * @throws Exception
     */
    public function afterSubscribeCustomer(
        SubscriptionManager $subject,
        Subscriber $result,
        int $customerId,
        int $storeId
    ): Subscriber {
        $CCStoreId = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();
        if ($storeId==$CCStoreId && $result->getStatus() === Subscriber::STATUS_SUBSCRIBED) {
            $data = $this->retrieveFieldsForCustomer($customerId);
            $this->updateSubscriber($result, $data);
        }
        return $result;
    }

    /**
     * Retrieve and validate required fields for guest subscription.
     *
     * @return array
     * @throws InputException
     */
    private function validateFields(): array
    {
        $requiredFields = [
            'first_name' => __('First Name'),
            'last_name' => __('Last Name'),
            'region' => __('Region'),
            'model_years' => __('Model years'),
        ];

        $data = [];
        foreach ($requiredFields as $field => $label) {
            $value = $this->request->getParam($field);
            if (empty($value)) {
                throw new InputException(__('%1 is required.', $label));
            }
            if($field == 'model_years')
                $value = implode(',',$value);
            $data[$field] = $value;
        }

        return $data;
    }

    /**
     * Retrieve fields for customer subscription, falling back to customer data if necessary.
     *
     * @param int $customerId
     * @return array
     * @throws LocalizedException
     */
    private function retrieveFieldsForCustomer(int $customerId): array
    {
        $customer = $this->customerRepository->getById($customerId);
        return [
            'first_name' => $this->request->getParam('first_name') ?: $customer->getFirstname(),
            'last_name' => $this->request->getParam('last_name') ?: $customer->getLastname(),
            'region' => $this->request->getParam('region') ?: null,
            'model_years' => $this->request->getParam('model_years') ?implode(',',$this->request->getParam('model_years')): null,
        ];
    }

    /**
     * Update all additional fields for the subscriber.
     *
     * @param Subscriber $subscriber
     * @param array $data
     * @return void
     * @throws Exception
     */
    private function updateSubscriber(Subscriber $subscriber, array $data): void
    {
        foreach ($data as $field => $value) {
            $subscriber->setData($field, $value);
        }

        $subscriber->save();
    }
}
