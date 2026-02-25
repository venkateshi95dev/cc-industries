<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Plugin\Block\Adminhtml\Customer\Group\Edit;

use Closure;
use I95DevConnect\MessageQueue\Model\CustomerGroup;
use I95DevConnect\PriceLevel\Helper\Data;
use I95DevConnect\PriceLevel\Model\PriceLevelData;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

/**
 * Customer Group Edit Form
 */
class Form
{
    public const GROUP_CODE_MAX_LENGTH = 15;
    public const PRICELEVEL_ID = 'pricelevel_id';
    public const CUSTOMER_GROUP_CODE = 'customer_group_code';
    public const LABEL = 'label';
    public const CLASS_STR = 'class';
    public const TITLE = 'title';
    public const REQUIRED = 'required';

    public const GROUPNAME = 'Group Name';

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var GroupInterfaceFactory
     */
    protected $groupDataFactory;

    /**
     *
     * @var CustomerGroup
     */
    public $customerGroupModel;

    /**
     *
     * @var PriceLevelData
     */
    public $priceLevelModel;

    /**
     * @var Data
     */
    public $helper;

    /**
     * @var \I95DevConnect\MessageQueue\Helper\Data
     */
    public $baseData;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Class constructor to include all the dependencies
     *
     * @param Registry $registry
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupInterfaceFactory $groupDataFactory
     * @param CustomerGroup $customerGroupModel
     * @param PriceLevelData $priceLevelModel
     * @param Data $dataHelper
     * @param \I95DevConnect\MessageQueue\Helper\Data $baseData
     */
    public function __construct( // NOSONAR
        Registry $registry,
        \Magento\Tax\Helper\Data $taxHelper,
        GroupRepositoryInterface $groupRepository,
        GroupInterfaceFactory $groupDataFactory,
        CustomerGroup $customerGroupModel,
        PriceLevelData $priceLevelModel,
        Data $dataHelper,
        \I95DevConnect\MessageQueue\Helper\Data $baseData
    ) {
        $this->taxHelper = $taxHelper;
        $this->customerGroupModel = $customerGroupModel;
        $this->priceLevelModel = $priceLevelModel;
        $this->helper = $dataHelper;
        $this->baseData = $baseData;
        $this->coreRegistry = $registry;
        $this->groupRepository = $groupRepository;
        $this->groupDataFactory = $groupDataFactory;
    }

    /**
     * Get form HTML
     *
     * @param \Magento\Customer\Block\Adminhtml\Group\Edit\Form $subject
     * @param Closure $proceed
     * @return string
     */
    public function aroundGetFormHtml(
        \Magento\Customer\Block\Adminhtml\Group\Edit\Form $subject,
        Closure $proceed
    ) {
        $component = $this->baseData->getscopeConfig(
            'i95dev_messagequeue/I95DevConnect_settings/component',
            ScopeInterface::SCOPE_WEBSITE
        );
        if (!$this->helper->isEnabled() || $component == 'AX' || $component == 'SAP') {
            return $proceed();
        }
        $groupId = $this->coreRegistry->registry(RegistryConstants::CURRENT_GROUP_ID);
        $groupData = $this->customerGroupModel->getCollection()
            ->addFieldToFilter('customer_group_id', $groupId)
            ->getData();
        $priceLevelId = isset($groupData[0][self::PRICELEVEL_ID]) ? $groupData[0][self::PRICELEVEL_ID] : '';
        $priceLevel = $this->priceLevelModel->load($priceLevelId)->getPricelevelCode();
        $form = $subject->getForm();

        if (is_object($form)) {
            $fieldset = $form->getElement('base_fieldset');
            $groupCodeLength = self::GROUP_CODE_MAX_LENGTH;

            $validateClass = sprintf(
                'required-entry validate-length maximum-length-%d',
                $groupCodeLength
            );

            if ($groupId === null) {
                $customerGroup = $this->groupDataFactory->create();
                $defaultCustomerTaxClass = $this->taxHelper->getDefaultCustomerTaxClass();
            } else {
                $customerGroup = $this->groupRepository->getById($groupId);
                $defaultCustomerTaxClass = $customerGroup->getTaxClassId();
            }

            if ($groupId === null) {
                $form->getElement(
                    self::CUSTOMER_GROUP_CODE
                )->setData(
                    [
                        'name' => 'code',
                        self::LABEL => self::GROUPNAME,
                        self::TITLE => self::GROUPNAME,
                        'note' => __(
                            'Maximum length must be less then %1 symbols',
                            $groupCodeLength
                        ),
                        self::CLASS_STR => $validateClass,
                        self::REQUIRED => true
                        ]
                );
            } else {
                $form->getElement(
                    self::CUSTOMER_GROUP_CODE
                )->setData(
                    [
                        'name' => 'code',
                        self::LABEL => self::GROUPNAME,
                        self::TITLE => self::GROUPNAME,
                        'note' => __(
                            'Maximum length must be less then %1 symbols',
                            $groupCodeLength
                        ),
                        self::CLASS_STR => $validateClass,
                        self::REQUIRED => true,
                        'readonly' => true
                        ]
                );
            }

            if ($component != "SAP") {
                $fieldset->addField(
                    'price_level',
                    'text',
                    [
                        'name' => self::PRICELEVEL_ID,
                        self::LABEL => __('Price Level'),
                        self::TITLE => __('Price Level'),
                        self::REQUIRED => false,
                        'readonly' => true,
                        'value' => $priceLevel,
                    ]
                );
            }
            $form->addValues(
                [
                    'id' => $customerGroup->getId(),
                    self::CUSTOMER_GROUP_CODE => $customerGroup->getCode(),
                    'tax_class_id' => $defaultCustomerTaxClass,
                ]
            );
            $subject->setForm($form);
        }
        return $proceed();
    }
}
