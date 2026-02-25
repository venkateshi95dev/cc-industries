<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/15/2019 1:13 PM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Block;

use Crimson\MachCatalogRequest\Model\Config;
use Magento\Framework\App\Http\Context;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Footer;

/**
 * Class CatalogRequestLink
 * @package Crimson\MachCatalogRequest\Block
 */
class CatalogRequestLink extends Footer
{
    /**
     * @var Config
     */
    protected $catalogRequestConfig;

    public function __construct(
        Template\Context $context,
        Context $httpContext,
        Config $catalogRequestConfig,
        array $data = []
    ) {
        parent::__construct($context, $httpContext, $data);
        $this->catalogRequestConfig = $catalogRequestConfig;
    }

    /**
     * @return string
     */
    public function getRequestPageUrl(): string
    {
        return $this->getUrl('catalog/request/form');
    }

    /**
     * @return string|null
     */
    public function getCatalogRequestFooterImageUrl(): ?string
    {
        return $this->catalogRequestConfig->getFooterRequestCatalogImageUrl();
    }

    /**
     * @return int
     */
    protected function getCacheLifetime(): int
    {
        return 604800;
    }
}
