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

namespace Ebizcharge\Ebizcharge\Gateway\Validator;

use Ebizcharge\Ebizcharge\Gateway\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Response Validator Gateway class
 *
 * Class ResponseValidator
 */
class ResponseValidator extends AbstractValidator
{
    /**
     * @var SubjectReader
     */
    private SubjectReader $subjectReader;

    /**
     * ResponseValidator constructor.
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader          $subjectReader
    ) {
        /** @var  subjectReader */
        $this->subjectReader = $subjectReader;

        parent::__construct($resultFactory);
    }

    /**
     * Validate Method
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = $this->subjectReader->readResponseObject($validationSubject);
        $isValid = true;
        $errorMessages = [];
        $errorCodes = [];

        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }
}
