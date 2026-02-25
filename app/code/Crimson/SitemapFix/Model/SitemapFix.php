<?php

namespace Crimson\SitemapFix\Model;

use Magento\Sitemap\Model\Sitemap;

/**
 * Class SitemapFix
 * @package Crimson\SitemapFix\Model
 */
class SitemapFix extends Sitemap
{
    /**
     * Get Document root of Magento instance
     *
     * @return string
     */
    protected function _getDocumentRoot(): string
    {
        return realpath($this->_request->getControllerName()
            ? $this->_request->getServer('DOCUMENT_ROOT')
            : $this->_getBaseDir()
        );
    }
}
