<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_AjaxCart
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AjaxCart\Api;

/**
 * Interface Use to get config module
 *
 * @api
 * @since 100.0.2
 */
interface GetConfigModuleInterface
{
    /**
     * Get module configs
     *
     * @param int $storeId
     * @return string[]
     */
    public function getConfig(int $storeId);
}
