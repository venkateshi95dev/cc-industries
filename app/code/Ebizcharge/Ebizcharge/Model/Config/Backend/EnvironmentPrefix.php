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

namespace Ebizcharge\Ebizcharge\Model\Config\Backend;

use Ebizcharge\Ebizcharge\Api\Data\ConfigModelInterface;
use Ebizcharge\Ebizcharge\Api\Data\SoapApiModelInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Environment Prefix backend model class for saving Division Id value
 *
 * Class EnvironmentPrefix
 */
class EnvironmentPrefix extends Value
{
    /**
     * @var WriterInterface
     */
    protected WriterInterface $_configWriter;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param WriterInterface $configWriter
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        WriterInterface $configWriter,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );

        /** @var  _configWriter */
        $this->_configWriter = $configWriter;
    }

    /**
     * After Save method
     *
     * @return EnvironmentPrefix
     */
    public function afterSave()
    {
        $value = $this->getValue();
        $divisionIdValue = $value ? $value  : '';
      //  $divisionIdValue = $value ? $value . '-' : '';
       // $divisionIdValue .= SoapApiModelInterface::EBIZCHARGE_DIVISION_ID;

        $this->_configWriter->save(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_DIVISION_ID,
            $divisionIdValue,
            $this->getScope(),
            $this->getScopeId()
        );

        return parent::afterSave();
    }
}
