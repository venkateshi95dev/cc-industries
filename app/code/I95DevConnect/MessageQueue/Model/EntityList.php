<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model;

use Exception;
use I95DevConnect\MessageQueue\Helper\Data;

/**
 * Class for inserting and deleting entities in i95dev_entity table
 */
class EntityList
{
    public const ENTITYCODE = "entity_code";

    /**
     * @var EntityFactory
     */
    public $entityModel;

    /**
     * @var ReadCustomXml
     */
    public $readCustomXml;

    /**
     * @var Data
     */
    public $helperData;

    /**
     *
     * @param EntityFactory $entityModel
     * @param Data $helperData
     * @param ReadCustomXml $readCustomXml
     */
    public function __construct(
        EntityFactory $entityModel,
        Data $helperData,
        ReadCustomXml $readCustomXml
    ) {
        $this->entityModel = $entityModel;
        $this->readCustomXml = $readCustomXml;
        $this->helperData = $helperData;
    }

    /**
     * Inserting new entity data in i95dev_entity table
     *
     * @param type $entityData
     *
     * @throws Exception
     */
    public function insertEntityToEntityList($entityData)
    {
        $entity_name = $entityData['entity_name'];
        $entity_code = $entityData[self::ENTITYCODE];
        $support_for_inbound = $entityData['support_for_inbound'];
        $support_for_outbound = $entityData['support_for_outbound'];
        $model = $this->entityModel->create();
        $existingObject = $model->load($entity_code, self::ENTITYCODE);
        if (!$existingObject->getId() > 0) {
            $model = $this->entityModel->create();
            $model->setData("entity_name", $entity_name);
            $model->setData(self::ENTITYCODE, $entity_code);
            $model->setData("support_for_inbound", $support_for_inbound);
            $model->setData("support_for_outbound", $support_for_outbound);
            $model->save();
        }
    }

    /**
     * Deleting existing entity from i95dev_entity
     *
     * @param type $entityData
     * @throws Exception
     */
    public function deleteEntityToEntityList($entityData)
    {
        $entity_code = $entityData[self::ENTITYCODE];
        $model = $this->entityModel->create();
        $existingObject = $model->load($entity_code, self::ENTITYCODE);
        if (!$existingObject->getId() > 0) {
            $existingObject->delete();
        }
    }
}
