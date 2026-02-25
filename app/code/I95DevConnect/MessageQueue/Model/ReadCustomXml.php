<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for reading custom xml
 */
class ReadCustomXml
{
    /**
     * @var object
     */
    public $idAttributes;

    /**
     * @var Data
     */
    public $messageQueueHelper;

    /**
     *
     * @param Data $messageQueueHelper
     */
    public function __construct(
        Data $messageQueueHelper
    ) {
        $this->messageQueueHelper = $messageQueueHelper;
    }

    /**
     * Gets entity code list by sort order
     *
     * @return array
     * @throws LocalizedException
     */
    public function getXmlDataOrderBySortOrder()
    {
        try {
            $entityList = $this->getEntityList();
            $entityCodeList = [];
            foreach ($entityList as $entityKey => $entityVal) {
                $entityCodeList[$entityKey]['id'] = $entityKey;
                $entityCodeList[$entityKey]['title'] = $entityVal;
            }
        } catch (LocalizedException $ex) {
            $message = $ex->getMessage();
            throw new LocalizedException(__($message));
        }

        return $entityCodeList;
    }

    /**
     * Gets entity code list by sync order
     *
     * @return array
     * @throws LocalizedException
     */
    public function getXmlDataOrderBySyncOrder()
    {
        try {
            $entityList = $this->messageQueueHelper->getEntityTypeListBySyncOrder();
            $entityCodeList = [];
            foreach ($entityList as $entityKey => $entityVal) {
                $entityCodeList[$entityKey]['id'] = $entityKey;
                $entityCodeList[$entityKey]['title'] = $entityVal;
            }
        } catch (LocalizedException $ex) {
            $message = $ex->getMessage();
            throw new LocalizedException(__($message));
        }

        return $entityCodeList;
    }

    /**
     * Get entity list array
     *
     * @return array
     */
    public function getEntityList()
    {
        return $this->messageQueueHelper->getEntityTypeList();
    }
}
