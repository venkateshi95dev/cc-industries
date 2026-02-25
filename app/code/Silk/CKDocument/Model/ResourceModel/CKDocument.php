<?php

namespace Silk\CKDocument\Model\ResourceModel;

use Silk\CKDocument\Api\Data\CKDocumentInterface;
use Silk\CKDocument\Model\CKDocument as CmsCKDocument;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Cms document mysql resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CKDocument extends AbstractDb
{
    /**
     * Store model
     *
     * @var null|Store
     */
    protected $_store = null;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param DateTime $dateTime
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cms_document', 'document_id');
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(CKDocumentInterface::class)->getEntityConnection();
    }

    /**
     * Process document data before saving
     *
     * @param AbstractModel $object
     * @return $this
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        return parent::_beforeSave($object);
    }


    /**
     * Retrieves cms document title from DB by passed id.
     *
     * @param string $id
     * @return string|false
     */
    public function getCmsCKDocumentTitleById($id)
    {
        $connection = $this->getConnection();
        $entityMetadata = $this->metadataPool->getMetadata(CKDocumentInterface::class);

        $select = $connection->select()
            ->from($this->getMainTable(), 'title')
            ->where($entityMetadata->getIdentifierField() . ' = :document_id');

        return $connection->fetchOne($select, ['document_id' => (int)$id]);
    }


}
