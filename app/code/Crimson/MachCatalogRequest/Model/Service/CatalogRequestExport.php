<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/19/2019 9:49 AM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Model\Service;

use Crimson\MachBase\Exception\MachConnectionException;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCatalogRequest\Api\CatalogRequestRepositoryInterface;
use Crimson\MachCatalogRequest\Api\Data\CatalogRequestInterface;
use Crimson\MachCatalogRequest\Api\Data\CatalogRequestSearchResultsInterface;
use Crimson\MachCatalogRequest\Model\Api\CatalogRequest;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CatalogRequestExport
 * @package Crimson\MachCatalogRequest\Model\Service
 */
class CatalogRequestExport
{
    const MAX_PER_RUN = 20;

    /**
     * @var CatalogRequest
     */
    protected $catalogRequestApi;
    /**
     * @var CatalogRequestRepositoryInterface
     */
    protected $catalogRequestRepository;
    /**
     * @var MachConfig
     */
    protected $machConfig;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    public function __construct(
        MachConfig $machConfig,
        CatalogRequest $catalogRequestApi,
        CatalogRequestRepositoryInterface $catalogRequestRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->machConfig               = $machConfig;
        $this->catalogRequestApi        = $catalogRequestApi;
        $this->catalogRequestRepository = $catalogRequestRepository;
        $this->searchCriteriaBuilder    = $searchCriteriaBuilder;
    }

    public function execute(): void
    {
        $unprocessedRequestResult = $this->_getUnprocessedRequests();
        if ($unprocessedRequestResult->getTotalCount() <= 0) {
            return;
        }

        foreach ($unprocessedRequestResult->getItems() as $catalogRequest) {
            try {
                $this->catalogRequestApi->export($catalogRequest);
                $catalogRequest->setProcessed(true);
                $this->catalogRequestRepository->save($catalogRequest);
            } catch (MachConnectionException $e) {
                //mach is down, try on next run.
                //all others can be thrown
                break;
            }
        }
    }

    protected function _getUnprocessedRequests()
    {
        $this->searchCriteriaBuilder
            ->addFilter(CatalogRequestInterface::PROCESSED, false)
            ->setPageSize(self::MAX_PER_RUN);

        return $this->catalogRequestRepository->getList($this->searchCriteriaBuilder->create());
    }
}
