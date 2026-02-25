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
use Ebizcharge\Ebizcharge\Model\Quote as QuoteModel;

/**
 * Authorize Data Request Builder
 *
 * Class AuthorizeDataBuilder
 */
class AuthorizeDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private SubjectReader $subjectReader;

    /**
     * @var QuoteModel
     */
    private QuoteModel $quoteModel;

    /**
     * AuthorizeDataBuilder constructor.
     *
     * @param SubjectReader $subjectReader
     * @param QuoteModel $quoteModel
     */
    public function __construct(
        SubjectReader $subjectReader,
        QuoteModel $quoteModel
    ) {
        /** @var  subjectReader */
        $this->subjectReader = $subjectReader;
        /** @var  quoteModel */
        $this->quoteModel = $quoteModel;
    }

    /**
     * Build Function
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $quote = $this->quoteModel->getQuote();
        $amount = $quote->getGrandTotal();

        return [
            'amount' => $amount,
            'payment' => $paymentDO->getPayment()
        ];
    }
}
