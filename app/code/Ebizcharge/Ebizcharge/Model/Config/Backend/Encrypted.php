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

namespace Ebizcharge\Ebizcharge\Model\Config\Backend;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory as EbizConfigFactory;
use Exception;
use Magento\Config\Model\Config\Backend\Encrypted as CoreEncrypted;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Model\Context as CoreContext;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Encrypted model class for saving weight_unit value
 *
 * Class Encrypted
 */
class Encrypted extends CoreEncrypted
{
    /**
     * @var EbizConfigFactory
     */
    protected EbizConfigFactory $configFactory;
    protected EbizchargeLogger $ebizchargeLogger;

    public function __construct(
        CoreContext          $context,
        Registry             $registry,
        ScopeConfigInterface $config,
        TypeListInterface    $cacheTypeList,
        EncryptorInterface   $encryptor,
        EbizConfigFactory    $configFactory,
        EbizchargeLogger     $ebizchargeLogger,
        AbstractResource     $resource = null,
        AbstractDb           $resourceCollection = null,
        array                $data = []
    )
    {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $encryptor,
            $resource,
            $resourceCollection,
            $data
        );
        /** @var $configFactory */
        $this->configFactory = $configFactory;
        /** @var $ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;

    }

    /**
     * Before Save and Encrypt Configuration values
     *
     * @return void
     */
    public function beforeSave()
    {
        $configFactory = $this->configFactory->create();
        try {
            $paramValue = $this->getValue();
            if ($configFactory->isPostFixExits($paramValue)) {
                $paramValue = $configFactory->decryptWithPostFix($paramValue);
                $paramValue = $configFactory->addPostFix($paramValue);
                $paramValue = $this->_encryptor->encrypt($paramValue);
            } else {
                $paramValue = $configFactory->addPostFix($paramValue);
                $paramValue = $this->_encryptor->encrypt($paramValue);
            }
            $this->setValue($paramValue);
            // parent::beforeSave();
        } catch (Exception $exception) {
            $this->ebizchargeLogger->addCritical(
                __("Could not save the config value. error: " . $exception->getMessage())
            );
        }
    }
}
