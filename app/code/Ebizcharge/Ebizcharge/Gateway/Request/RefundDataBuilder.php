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

namespace Ebizcharge\Ebizcharge\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Refund Data Request Builder
 *
 * Class RefundDataBuilder
 */
class RefundDataBuilder implements BuilderInterface
{
    /**
     * @const AMOUNT
     */
    public const AMOUNT = 'amount';

    /**
     * Order Id
     *
     * @const ORDER_ID
     */
    public const ORDER_ID = 'orderId';

    /**
     * @var SubjectReader
     */
    private SubjectReader $subjectReader;

    /**
     * RefundDataBuilder constructor.
     *
     * @param SubjectReader $subjectReader
     **/
    public function __construct(
        SubjectReader $subjectReader
    ) {
        /** @var  subjectReader */
        $this->subjectReader = $subjectReader;
    }

    /**
     * Build Function
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDriver = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDriver->getOrder();

        return [
            self::AMOUNT => $this->subjectReader->readAmount($buildSubject),
            self::ORDER_ID => $order->getOrderIncrementId()
        ];
    }
}
