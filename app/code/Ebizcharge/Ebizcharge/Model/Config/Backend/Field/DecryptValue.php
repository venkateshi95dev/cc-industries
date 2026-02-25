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

namespace Ebizcharge\Ebizcharge\Model\Config\Backend\Field;

use Ebizcharge\Ebizcharge\Api\Data\ConfigModelInterface;
use Magento\Backend\Block\Template\Context as CoreContext;
use Magento\Config\Block\System\Config\Form\Field as CoreField;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Unit Of Measure backend model class for saving weight_unit value
 *
 * Class UnitOfMeasure
 */
class DecryptValue extends CoreField
{
    /**
     * @var WriterInterface
     */
    protected WriterInterface $configWriter;
    /**
     * @var EncryptorInterface
     */
    protected EncryptorInterface $encryptor;

    /**
     * Main Constructor
     *
     * @param CoreContext $context
     * @param EncryptorInterface $encryptor
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param WriterInterface $configWriter
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        CoreContext          $context,
        EncryptorInterface   $encryptor,
        Registry             $registry,
        ScopeConfigInterface $config,
        TypeListInterface    $cacheTypeList,
        WriterInterface      $configWriter,
        AbstractResource     $resource = null,
        AbstractDb           $resourceCollection = null,
        array                $data = []
    )
    {

        /** @var $configWriter */
        $this->configWriter = $configWriter;
        /** @var $encryptor */
        $this->encryptor = $encryptor;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Get Element Html function
     *
     * @param AbstractElement|null $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element = null)
    {
        $fieldvalue = $element->getValue();
        if ($fieldvalue && $fieldvalue !== "") {
            $ebizEncryptKey = ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_API_KEY;
            $fieldvalue = str_replace(ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_API_KEY, "", $fieldvalue);
            $decryptedValue = $this->encryptor->decrypt($fieldvalue);
            /** Masked last 4 characters **/
            $length = strlen($decryptedValue) > 35 ? strlen($decryptedValue) : 35;
            $maskedDecryptedValue = str_repeat('*', max(0, $length - 4)) . substr($decryptedValue, -4);
            $element->setData('value', $maskedDecryptedValue);
        }
        return parent::_getElementHtml($element);
    }

}
