<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Silk\ProductImage\Model;

use Silk\ProductImage\Api\Data\DocumentSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Document search results.
 */
class DocumentSearchResults extends SearchResults implements DocumentSearchResultsInterface
{
}
