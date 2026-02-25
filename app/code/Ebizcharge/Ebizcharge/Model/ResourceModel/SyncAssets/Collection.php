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

namespace Ebizcharge\Ebizcharge\Model\ResourceModel\SyncAssets;

use Ebizcharge\Ebizcharge\Model\ResourceModel\SyncAssets as SyncAssetsResource;
use Ebizcharge\Ebizcharge\Model\SyncAssets as SyncAssetsModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Sync Assets Collection
 *
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * Construct Method
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SyncAssetsModel::class, SyncAssetsResource::class);
    }
}
