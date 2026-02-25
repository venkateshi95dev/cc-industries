<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Peter Talavera
 * @email       ptalavera@crimsonagility.com
 * @date        1/21/2019 4:03 PM
 * @brief
 */

namespace Crimson\Megamenu\Helper;

/**
 * Class Mddata
 * @package Crimson\Megamenu\Helper
 */
class Mddata extends \Magedelight\Megamenu\Helper\Mddata
{

    /**
     * @return array
     */
    public function getAllowedDomainsCollection(): array
    {
        $allStores = $this->_storeManager->getStores(true);

        $allDomains = array();

        foreach ($allStores as $store) {
            $defaultStoreView = $store->getDefaultStoreView();
            $baseUrl = $store->getBaseUrl();
            $parsedUrl = parse_url($baseUrl);
            $domain = str_replace(array('www.', 'http://', 'https://'), '', $parsedUrl['host']);
            array_push($allDomains, $domain );
        }

        return $allDomains;


    }

}
