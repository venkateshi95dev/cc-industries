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

namespace Ebizcharge\Ebizcharge\Model;

use Ebizcharge\Ebizcharge\Api\Data\SyncAssetsInterface;
use Ebizcharge\Ebizcharge\Api\SyncAssetsRepositoryInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\SyncAssetsFactory as ModelFactory;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Contains operations related to ebizcharge_token table
 *
 * Class SyncAssetsRepository
 */
class SyncAssetsRepository extends AbstractRepository implements SyncAssetsRepositoryInterface
{
    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * SyncAssetsRepository constructor.
     *
     * @param SyncAssetsFactory $modelFactory
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        ModelFactory $modelFactory,
        EbizchargeLogger $ebizchargeLogger
    ) {
        parent::__construct($ebizchargeLogger);

        /** @var  modelFactory */
        $this->modelFactory = $modelFactory;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Save Method
     *
     * @param SyncAssetsInterface $record
     * @return SyncAssetsInterface|null
     */
    public function save(SyncAssetsInterface $record): ?SyncAssetsInterface
    {
        $this->ebizchargeLogger->addInfo(__("Sync Assets record has been saved to the Sync Table"));
        return parent::saveRecord($record);
    }

    /**
     * Get By Id
     *
     * @param mixed $entityId
     * @param mixed $field
     * @return SyncAssetsInterface|null
     */
    public function getById($entityId, $field = null): ?SyncAssetsInterface
    {
        return parent::getRecordById($entityId, $field);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria): mixed
    {
        return parent::getList($searchCriteria);
    }
}
