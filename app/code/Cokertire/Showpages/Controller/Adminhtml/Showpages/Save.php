<?php

namespace Cokertire\Showpages\Controller\Adminhtml\Showpages;
use Magento\Framework\App\Filesystem\DirectoryList;
class Save extends \Cokertire\Showpages\Controller\Adminhtml\Showpages
{

    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * constructor
     *
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Cokertire\Showpages\Model\ShowpagesFactory $postFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Cokertire\Showpages\Model\ShowpagesFactory $showpagesFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->_backendSession = $backendSession;
        parent::__construct($showpagesFactory, $registry, $resultRedirectFactory, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('post');
        $show_date= substr($data["show_date"],0,1);
        if($show_date=="0"){
            $data["show_date"]= substr($data["show_date"],1);
        }
        $show_date_end= substr($data["show_date_end"],0,1);
        if($show_date_end=="0"){
            $data["show_date_end"]= substr($data["show_date_end"],1);
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $showpages = $this->_initShowpages();
            if(!isset($data["showpages_id"])){
                $request_path = "shows/".$data["identifier"];
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $mainUrlRewrite = $objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')->getCollection()->addFieldToFilter("request_path",$request_path)->getFirstItem();
                if($mainUrlRewrite->getId() && $mainUrlRewrite){
                    $this->messageManager->addError("Request Path for Specified Store already exists.");
                    $resultRedirect->setPath('showpages/showpages/new');
                    return $resultRedirect;
                }
            }

            $imageRequest = $this->getRequest()->getFiles('view_img');
            if ($imageRequest) {
                if (isset($imageRequest['name'])) {
                    $fileName = $imageRequest['name'];
                } else {
                    $fileName = '';
                }
            } else {
                $fileName = '';
            }

            if ($imageRequest && strlen($fileName)) {
                /*
                 * Save image upload
                 */
                try {
                    $uploader = $this->_objectManager->create(
                        'Magento\MediaStorage\Model\File\Uploader',
                        ['fileId' => 'view_img']
                    );
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                    /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
                    $imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();

                    $uploader->addValidateCallback('showpages_image', $imageAdapter, 'validateUploadFile');

                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(true);
                    /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
                    $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                        ->getDirectoryRead(DirectoryList::MEDIA);
                    $result = $uploader->save(
                        $mediaDirectory->getAbsolutePath(\Cokertire\Showpages\Model\Showpages::BASE_MEDIA_PATH)
                    );


                    $data['view_img'] = \Cokertire\Showpages\Model\Showpages::BASE_MEDIA_PATH.$result['file'];

                } catch (\Exception $e) {
                    if ($e->getCode() == 0) {
                        $this->messageManager->addError($e->getMessage());
                    }
                }
            } else {
                if (isset($data['view_img']) && isset($data['view_img']['value'])) {
                    if (isset($data['view_img']['delete'])) {
                        $data['view_img'] = null;
                        $data['delete_image'] = true;
                    } elseif (isset($data['view_img']['value'])) {
                        $data['view_img'] = $data['view_img']['value'];
                    } else {
                        $data['view_img'] = null;
                    }
                }
            }
            $showpages->setData($data);

            try {
                $showpages->save();
                $this->messageManager->addSuccess(__('The Showpages has been saved.'));
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'showpages/showpages/edit',
                        [
                            'id' => $showpages->getShowpagesId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('showpages/showpages/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Showpages.'));
            }
            $resultRedirect->setPath(
                'showpages/showpages/edit',
                [
                    'id' => $showpages->getShowpagesId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('showpages/showpages/');
        return $resultRedirect;
    }


}
