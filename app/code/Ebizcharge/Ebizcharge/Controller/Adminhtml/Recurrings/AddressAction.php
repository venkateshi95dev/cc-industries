<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class AddressAction
 *
 * Save address of customer
 * Deletes the customer's saved payment method.
 */
class AddressAction extends Action implements HttpPostActionInterface
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_subscriptions_orders_add';

    /**
     * @var AddressInterfaceFactory
     */
    private AddressInterfaceFactory $addressFactory;

    /**
     * @var AddressRepositoryInterface
     */
    private AddressRepositoryInterface $addressRepository;

    /**
     * @var EbizchargeLogger
     */
    private EbizchargeLogger $ebizchargeLogger;

    /**
     * @var RegionInterfaceFactory
     */
    private RegionInterfaceFactory $regionFactory;

    private JsonFactory $jsonFactory;


    public function __construct(
        RegionInterfaceFactory     $regionFactory,
        EbizchargeLogger           $ebizchargeLogger,
        AddressInterfaceFactory    $addressFactory,
        AddressRepositoryInterface $addressRepository,
        JsonFactory                $jsonFactory,
        Context                    $context
    )
    {
        parent::__construct($context);

        /** @var  addressFactory */
        $this->addressFactory = $addressFactory;
        /** @var  addressRepository */
        $this->addressRepository = $addressRepository;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var  regionFactory */
        $this->regionFactory = $regionFactory;
        /**
         *
         */
        $this->jsonFactory = $jsonFactory;
    }


    public function execute()
    {
        $addressResponse = [
            "error" => true,
            "status" => false
        ];
        $resultJson = $this->jsonFactory->create();
        // phpcs:disable
        if ($this->getRequest()->getPostValue('customerIdAddress')) {
            $addressResponse = $this->saveAddress();
        }
        $resultJson->setData($addressResponse);
        return  $resultJson;

        // phpcs:enable
    }

    /**
     * @return array
     */
    public function saveAddress(): array
    {
        $addressResponse = [
            "error" => true,
            "status" => false,
            "html_data" => ""
        ];
        try {
            $address = $this->addressFactory->create();
            $post = $this->getRequest()->getPostValue();

            $street = join(' ', array_filter(
                [$post['ship_address1'] ?? '',
                    $post['ship_address2'] ?? '',
                    $post['ship_address3'] ?? '']
            ));

            $address->setFirstname($post['ship_first_name'] ?? '')
                ->setLastname($post['ship_last_name'] ?? '')
                ->setCompany($post['ship_company'] ?? '')
                ->setCustomerId($post['customerIdAddress'] ?? '')
                ->setTelephone($post['ship_phone'] ?? '')
                ->setData('street', $street)
                ->setCity($post['ship_city'] ?? '')
                ->setRegionId($post['ship_region'] ?? '')
                ->setRegion($this->regionFactory->create()->setRegionId((int)$post['ship_region'] ?? ''))
                ->setPostcode($post['ship_zipcode'] ?? '')
                ->setCountryId($post['ship_country'] ?? '');

            $this->addressRepository->save($address);
            $this->ebizchargeLogger->addInfo(__("Saved Addresses to database"));
            $addressResponse["error"] = false;
            $addressResponse["status"] = true;

            if ($address->getId()) {
                $htmlAddress = $address->getFirstname()." ".$address->getLastname();
                $htmlAddress .= $address->getStreet(0);
                $htmlAddress .= $post['ship_region'] ?? '';
                $htmlAddress .= $post['ship_zipcode'] ?? '';
                $htmlAddress .= $post['ship_country'] ?? '';

                $addressResponse["html_data"] = "<option value='" . $address->getId() . "'>".$htmlAddress."</option>";
            }

        } catch (Exception $e) {
            $this->ebizchargeLogger->addCritical(__("Exception occurred " . $e->getMessage()));

        }
        return $addressResponse;

    }
}
