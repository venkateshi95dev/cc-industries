<?php
namespace Cokertire\Showpages\Plugin;

class SkipUrlParseFromAmasty
{

   /**
    * Undocumented variable
    *
    * @var \Magento\Framework\App\Request\Http
    */
    private $request;

    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    /**
     * Undocumented function
     *
     * @param \Amasty\ShopbySeo\Helper\UrlParser $subject
     * @param array $result
     * @return array
     */
    public function afterParseSeoPart(
        \Amasty\ShopbySeo\Helper\UrlParser $subject,
        $result
    ){

        if($this->request->getFrontName() == 'showpages') {
            return [];
        }

        return $result;
    }
}


