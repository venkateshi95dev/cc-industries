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

namespace Ebizcharge\Ebizcharge\Observer;

use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Ebizcharge\Ebizcharge\Model\TranApi as  SoapApiModelFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Observe and upload customer to EConnect automatically
 * Config Validation observer
 *
 * Class ConfigObserver
 */
class ConfigObserver implements ObserverInterface
{

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * @var ConfigFactory
     */
    protected ConfigFactory $configFactory;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $messageManagerInterface;

    /**
     * @var SoapApiModelFactory
     */
    protected  SoapApiModelFactory $soapApiModelFactory;

    /**
     * @param ConfigFactory $configFactory
     * @param RequestInterface $request
     * @param ManagerInterface $messageManagerInterface
     * @param SoapApiModelFactory $soapApiModelFactory
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        ConfigFactory    $configFactory,
        RequestInterface $request,
        ManagerInterface $messageManagerInterface,
        SoapApiModelFactory $soapApiModelFactory,
        EbizchargeLogger $ebizchargeLogger
    )
    {
        /** @var  configFactory */
        $this->configFactory = $configFactory;
        /** @var  request */
        $this->request = $request;
        /** @var messageManagerInterface */
        $this->messageManagerInterface = $messageManagerInterface;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $requestParams = $this->request->getParams();
        if ($requestParams) {
            if(isset($requestParams['group'])) {
                $paymentMethodsParams = $requestParams['group'][PaymentInterface::EBIZCHARGE_PAYMENT_METHOD];

                $merchantCredentails = [
                    'merchant_key' => isset($paymentMethodsParams['fields']['sourcekey']['value']) ? $paymentMethodsParams['fields']['sourcekey']['value'] : '',
                    'merchant_id' => isset($paymentMethodsParams['fields']['sourceid']['value']) ? $paymentMethodsParams['fields']['sourceid']['value'] : '',
                    'merchant_pin' => isset($paymentMethodsParams['fields']['sourcepin']['value']) ? $paymentMethodsParams['fields']['sourcepin']['value'] : '',
                ];

                /** Validation of API Key */
                if (!$merchantCredentails || !$this->configFactory->create()->isEbizchargeActive()) {

                    /** Logging if payment method is empty or Ebizcharge is not active */
                    $this->ebizchargeLogger->addInfo(__('Ebizcharge is not active or payment method is not found'));

                    return $this;
                }
                /** Validating the api key */
                $keysValidationResponse = $this->soapApiModelFactory->create()->validateMerchantAPICredentials($merchantCredentails);

                /** sending back response to Config Message Manager */
                if ($keysValidationResponse) {
                    // $this->messageManagerInterface->addSuccessMessage(__("Success, the Keys are also validated and saved "));
                } else {
                    $this->messageManagerInterface->addErrorMessage(__(
                        "Sorry provided Keys are not validated with EBizCharge Gateway"
                    ));
                }
            }
            return $this;
        }
    }
}
