<?php
declare(strict_types=1);

namespace Silk\CKDocument\Model;

use Silk\CKDocument\Api\Data\CKDocumentSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with CKDocument search results.
 */
class CKDocumentSearchResults extends SearchResults implements CKDocumentSearchResultsInterface
{
}
