<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Model\Source;

use I95DevConnect\DiscountGroups\Model\ItemdiscountgroupFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\ClassModel;

/**
 * Product tax class source model.
 */
class IdgList extends AbstractSource
{
    /**
     * @var ItemdiscountgroupFactory
     */
    protected $itemDiscountGroupModel;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory
     */
    protected $optionFactory;

    /**
     *
     * @param ItemdiscountgroupFactory $itemDiscountGroupModel
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $optionFactory
     */
    public function __construct(
        ItemdiscountgroupFactory $itemDiscountGroupModel,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $optionFactory
    ) {
        $this->optionFactory = $optionFactory;
        $this->itemDiscountGroupModel = $itemDiscountGroupModel;
    }

    /**
     * Retrieve all product tax class options.
     *
     * @param bool $withEmpty
     * @return array
     */
    public function getAllOptions($withEmpty = true)
    {
        $idgs = $this->itemDiscountGroupModel->create()
                    ->getCollection()
                    ->addFieldToSelect("id")
                    ->addFieldToSelect("idg_code");
        if (!$this->_options) {
            foreach ($idgs as $idg) {
                $this->_options[] = [
                    'value' => $idg->getIdgCode(),
                    'label' => $idg->getIdgCode(),
                ];
            }
        }

        if ($withEmpty) {
            if (!$this->_options) {
                return [['value' => '', 'label' => __('None')]];
            } else {
                return array_merge([['value' => '', 'label' => __('None')]], $this->_options);
            }
        }
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();

        foreach ($options as $item) {
            if ($item['value'] == $value) {
                return $item['label'];
            }
        }
        return false;
    }
}
