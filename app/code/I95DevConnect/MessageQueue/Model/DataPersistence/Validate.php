<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * Class which validate ERP data string
 */
class Validate extends AbstractModel
{
    /**
     * @var array
     */
    public $validateFields;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     *
     * @param Context $context
     * @param Registry $registry
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $registry);
    }

    /**
     * Validate data
     *
     * @param type $stringData
     * @return boolean
     * @throws LocalizedException
     */
    public function validateData($stringData)
    {
        $msg = [];

        foreach ($this->validateFields as $key => $value) {
            /*@author Debashis S. Gopal. is_null() and === check added */
            $val = $this->dataHelper->getValueFromArray($key, $stringData);
            if ($val === null || $val === '' || (is_array($val) && count($val) == 0)) {
                $msg[] = __($value);
            }
        }

        if (!empty($msg)) {
            $message = implode(', ', $msg);
            throw new LocalizedException(
                __($message),
                null,
                104
            );
        }
        return true;
    }
}
