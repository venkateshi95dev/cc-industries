<?php

/**
 * Century Business Solutions.
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
 *
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 *
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model\Order\Create\Total;

use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\Surcharge as SurchargeModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;

/**
 * Surcharge Total on admin order create page.
 *
 * Class Surcharge
 */
class Surcharge extends Total\AbstractTotal
{
    protected Config $ebizConfig;
    protected SurchargeModel $surchargeModel;
    protected EbizchargeLogger $ebizchargeLogger;
    protected CustomerFactory $customerFactory;

    private string $surchargeTitle = '';

    public function __construct(
        Config           $ebizConfig,
        SurchargeModel   $surchargeModel,
        CustomerFactory  $customerFactory,
        EbizchargeLogger $ebizchargeLogger
    )
    {
        // @var  ebizConfig
        $this->ebizConfig = $ebizConfig;
        // @var  surchargeModel
        $this->surchargeModel = $surchargeModel;
        // @var  ebizchargeLogger
        $this->ebizchargeLogger = $ebizchargeLogger;
        // Customer Factory
        $this->customerFactory = $customerFactory;
    }

    /**
     * Collect fee.
     *
     * @return $this
     *
     * @throws NoSuchEntityException
     */
    public function collect(
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total
    )
    {
        parent::collect($quote, $shippingAssignment, $total);

        if (!count($shippingAssignment->getItems())) {
            return $this;
        }

        $customerFactory = $this->customerFactory->create();
        $isCheckoutPage = $customerFactory->isCheckoutPage();
        $isSurchargeEnabled = false;
        if ($isCheckoutPage) {
            $storeId = $this->ebizConfig->getStoreId();
            $quoteSurchargePercentage = (float)$quote->getEcSurchargePercentage() ?? 0;
            if (0 === $quoteSurchargePercentage) {
                $isSurchargeEnabled = $customerFactory->isSurchargeEnabled($storeId);
            }
            // check if surcharge is enabled
            if ($isSurchargeEnabled) {
                $total->setTotalAmount($this->getCode(), 0);
                $total->setBaseTotalAmount($this->getCode(), 0);
                $total->setTotalAmount(SurchargeInterface::EC_SURCHARGE_PERCENTAGE, 0);

                $surchargeSessionData = $this->surchargeModel->getSurchargeSessionData();
                $surchargeAmount = $surchargeSessionData[SurchargeInterface::EBIZ_SURCHARGE_AMOUNT] ?? 0;
                $surchargePercentage = $surchargeSessionData[SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE] ?? 0;
                $surchargePercentage = floatval($surchargePercentage);
                $this->ebizchargeLogger->addInfo(__('Surcharge data ', 100, $surchargeSessionData));

                if ($surchargePercentage > 0) {
                    $surchargeAmount = isset($surchargeSessionData[SurchargeInterface::EBIZ_SURCHARGE_INELIGIBLE])
                    && $surchargeSessionData[SurchargeInterface::EBIZ_SURCHARGE_INELIGIBLE] ? 0 : $surchargeAmount;
                    $total->setTotalAmount($this->getCode(), $surchargeAmount);
                    $total->setBaseTotalAmount($this->getCode(), $surchargeAmount);
                    $total->setEcSurchargeAmount($surchargeAmount);
                    $total->setEcSurchargePercentage($surchargePercentage);
                    $total->setEcSurchargeIneligible($surchargeSessionData[SurchargeInterface::EBIZ_SURCHARGE_INELIGIBLE] ?? 0);

                    $quote->setEcSurchargeAmount($surchargeAmount);
                    $quote->setEcSurchargePercentage($surchargePercentage);
                    $quote->setEcSurchargeIneligible($surchargeSessionData[SurchargeInterface::EBIZ_SURCHARGE_INELIGIBLE] ?? 0);
                    $this->ebizchargeLogger->addInfo('Surcharge Shipping Assignment items ' . count($shippingAssignment->getItems()));
                }
            }
        }

        return $this;
    }

    /**
     * Fetch fee.
     *
     * @param Quote $quote
     * @param Total $total
     * @return array
     */
    public function fetch(Quote $quote, Total $total): array
    {
        $surchargeAmount = $total->getEcSurchargeAmount();
        $surchargePercentage = $total->getEcSurchargePercentage() ?: '';
        $ineligible = $total->getEcSurchargeIneligible();
        $surchargeAmount = $ineligible ? SurchargeInterface::EBIZ_INELIGIBLE_LABEL : $surchargeAmount;
        $this->surchargeTitle = 'Surcharge (' . $surchargePercentage . '%)';

        if (floatval($surchargePercentage) > 0) {
            return [
                [
                    'code' => $this->getCode(),
                    'title' => $this->surchargeTitle,
                    'value' => $surchargeAmount,
                ],
            ];
        }

        return [];
    }

    /**
     * Get label.
     *
     * @return Phrase
     */
    public function getLabel(): Phrase
    {
        return __($this->surchargeTitle);
    }
}
