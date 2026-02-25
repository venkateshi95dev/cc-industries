<?php
namespace Silk\Coker\Preference\Amasty\Finder\Controller\Index;


use Magento\Framework\App\Action\Context;

class Search extends \Amasty\Finder\Controller\Index\Search
{

    private \Amasty\Finder\Api\FinderRepositoryInterface $finderRepository;
    private \Magento\Store\Model\StoreManagerInterface $storeManager;
    private \Magento\Framework\Url\Decoder $urlDecoder;

    public function __construct(
        Context $context,
        \Magento\Framework\Url\Decoder $urlDecoder,
        \Amasty\Finder\Helper\Url $urlHelper,
        \Amasty\Finder\Model\ConfigProvider $configHelper,
        \Amasty\Finder\Api\FinderRepositoryInterface $finderRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Finder\Model\Session $session
    ) {
        $this->urlDecoder = $urlDecoder;
        $this->finderRepository = $finderRepository;
        $this->storeManager = $storeManager;
        parent::__construct($context,$urlDecoder,$urlHelper, $configHelper,$finderRepository,$storeManager,$session);
    }
    public function execute()
    {
        $finderId = $this->getRequest()->getParam('finder_id');

        /** @var \Amasty\Finder\Model\Finder $finder */
        $finder = $this->finderRepository->getById($finderId);
        $backUrl = $this->urlDecoder->decode($this->getRequest()->getParam('back_url'));
        $currentApplyUrl = $this->urlDecoder->decode($this->getRequest()->getParam('current_apply_url'));
        $baseBackUrl = explode('?', $backUrl);
        $baseBackUrl = array_shift($baseBackUrl);
        $dropdowns = $this->getRequest()->getParam('finder');
        if(is_array($dropdowns)) {
            $id = end($dropdowns);
            $backUrl = $this->getBaseUrl()."amfinder/index/index?finder_id=".$id;
        }
        $finder->resetFilter();
        $this->getResponse()->setRedirect($backUrl);
    }

    public function getBaseUrl(){
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }
}
