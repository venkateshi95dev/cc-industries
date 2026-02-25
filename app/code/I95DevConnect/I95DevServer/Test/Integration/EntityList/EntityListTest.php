<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Entitylist;

/**
 * Test case Entity List
 */
class EntitylistTest extends \PHPUnit\Framework\TestCase
{
    public const CUSTOM_CODE = 'customcode';
    public const ENTITY_CODE = 'entity_code';

    /**
     * @author Kavya Koona
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->scopeConfig = $objectManager->create(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        );
        $this->entityList = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\EntityList::class
        );
        $this->readCustomXml = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\ReadCustomXml::class
        );
        $this->helperData = $objectManager->create(
            \I95DevConnect\MessageQueue\Helper\Data::class
        );
        $this->entityModel = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\EntityFactory::class
        );
    }

    /**
     * Test case for Inserting new entity data in to i95dev_entity table
     *
     * @magentoDbIsolation enabled
     * @author             Kavya Koona
     */
    public function testInsertEntityToEntityList()
    {
        $file = "/json/EntityData.json";
        $entityData = $this->readJsonFile($file);
        $dataArray = json_decode($entityData, true);
        $this->entityList->insertEntityToEntityList($dataArray);
        $model = $this->entityModel->create();
        $existingObject = $model->load(self::CUSTOM_CODE, self::ENTITY_CODE);
        $this->assertEquals(1, count($existingObject), "Issue came in saving entity into entitylist");
    }

    /**
     * Test case for deletion of existing entity from i95dev_entity table
     *
     * @magentoDbIsolation enabled
     * @author             Kavya Koona
     */
    public function testDeleteEntityToEntityList()
    {

        $file = "/json/EntityData.json";
        $entityData = $this->readJsonFile($file);
        $dataArray = json_decode($entityData, true);
        $this->entityList->insertEntityToEntityList($dataArray);
        $model = $this->entityModel->create();
        $existingObject = $model->load(self::CUSTOM_CODE, self::ENTITY_CODE);
        $this->assertEquals(1, count($existingObject), "Issue came in saving entity into entitylist");
        $this->entityList->deleteEntityToEntityList($entityData);
        $existingObject = $model->load(self::CUSTOM_CODE, self::ENTITY_CODE);
        $this->assertEquals(1, count($existingObject), "Issue came in delete entity");
    }

    /**
     * Read data from json file
     *
     * @createdBy Kavya Koona
     *
     * @param $fileName
     *
     * @return false|string
     */
    public function readJsonFile($fileName)
    {
        $path = realpath(dirname(__FILE__)) . $fileName;
        return file_get_contents($path);
    }
}
