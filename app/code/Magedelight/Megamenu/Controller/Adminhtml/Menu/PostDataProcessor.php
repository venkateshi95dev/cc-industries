<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Controller\Adminhtml\Menu;

use Magento\Framework\Stdlib\DateTime\Filter\Date as FiterDate;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Model\Layout\Update\ValidatorFactory;
use Magento\Framework\App\ProductMetadataInterface;

class PostDataProcessor
{
    /**
     * @var FiterDate
     */
    protected $dateFilter;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Constructor
     *
     * @param FiterDate $dateFilter
     * @param ManagerInterface $messageManager
     * @param ValidatorFactory $validatorFactory
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        FiterDate $dateFilter,
        ManagerInterface $messageManager,
        ValidatorFactory $validatorFactory,
        ProductMetadataInterface $productMetadata
    ) {
        $this->dateFilter = $dateFilter;
        $this->messageManager = $messageManager;
        $this->validatorFactory = $validatorFactory;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array $data
     * @return array
     */
    public function filter($data)
    {
        $filterRules = [];
        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.4.6', '>=')) {
            return (new \Magento\Framework\Filter\FilterInput($filterRules, [], $data))->getUnescaped();
        } else {
            //@codingStandardsIgnoreStart
            return (new \Zend_Filter_Input($filterRules, [], $data))->getUnescaped();
            //@codingStandardsIgnoreEnd
        }
    }

    /**
     * Check if required fields is not empty
     *
     * @param array $data
     * @return bool
     */
    public function validateRequireEntry(array $data)
    {
        $requiredFields = [
            'menu_name' => __('Menu Title'),
            'stores' => __('Store View'),
            'is_active' => __('Enable Menu')
        ];
        $errorNo = true;
        foreach ($data as $field => $value) {
            if (in_array($field, array_keys($requiredFields)) && $value === '') {
                $errorNo = false;
                $this->messageManager->addErrorMessage(
                    __('To apply changes you should fill in required "%1" field', $requiredFields[$field])
                );
            }
        }
        return $errorNo;
    }
}
