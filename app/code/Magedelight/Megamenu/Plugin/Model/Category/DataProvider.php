<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Plugin\Model\Category;

use Magento\Framework\Message\ManagerInterface;

class DataProvider
{
    /**
     * @var ManagerInterface
     */
    public $messageManager;

    /**
     * DataProvider Constructor
     *
     * @param ManagerInterface $messageManager
     */
    public function __construct(ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    /**
     * After Get Data
     *
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param array $result
     * @return ManagerInterface|array
     */
    public function afterGetData(\Magento\Catalog\Model\Category\DataProvider $subject, $result)
    {
        try {
            $category = $subject->getCurrentCategory();
            $result[$category->getId()]['visibleMegamenuTab'] = false;
            if (isset($result[$category->getId()]['level'])) {
                /* Below lines are commented to show Megamenu category block in all Category */
                if ($result[$category->getId()]['level'] == 2 || $result[$category->getId()]['level'] == 3) {
                    $result[$category->getId()]['visibleMegamenuTab'] = true;
                }
            }
        } catch (\Exception $e) {
            return $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $result;
    }

    /**
     * After Prepare Meta.
     *
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterPrepareMeta(\Magento\Catalog\Model\Category\DataProvider $subject, $result)
    {
        $result['magedelight_megamenu']['arguments']['data']['config']['componentType'] =
            \Magento\Ui\Component\Form\Fieldset::NAME;

        return $result;
    }
}
