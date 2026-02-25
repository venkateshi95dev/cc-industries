<?php

namespace Crimson\Demographics\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ConfigProvider
 * @package Crimson\Demographics\Model\Checkout
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Crimson\Demographics\Helper\Data
     */
    private $helper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        CheckoutSession $checkoutSession,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Crimson\Demographics\Helper\Data $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->eavConfig = $eavConfig;
        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getConfig() : array
    {
        return [
            'demographics_enabled' => $this->helper->isEnabled($this->storeManager->getStore()->getId()),
            'demographics_required' => $this->helper->isRequired($this->storeManager->getStore()->getId()),
            'demographics_options' => $this->getDemographicsOptions(),
            'demographics_chosen' => $this->getCustomerDemographics(),
            'demographics_message' => $this->helper->getDemographicsMessage() ?: 'Corvette Demographics',
        ];
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function getDemographicsOptions(): array
    {
        $attribute = $this->eavConfig->getAttribute('customer', 'car_demos');
        $options = $attribute->getSource()->getAllOptions();

        if ($options) {
            array_shift($options);
        }

        return $options;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getCustomerDemographics(): array
    {
        $customerId = $this->checkoutSession->getQuote()->getCustomerId();
        $carDeomos = [];

        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);

            if ($customer->getCustomAttribute('car_demos')) {
                $carDeomos = explode(',', $customer->getCustomAttribute('car_demos')->getValue());
            }
        }

        return $carDeomos;
    }
}
