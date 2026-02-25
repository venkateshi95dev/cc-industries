<?php

namespace Crimson\Testimonial\Controller\Adminhtml\Testimonial;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends Action
{

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;

    /**
     * stdlib timezone.
     *
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $_stdTimezone;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;

    /**
     * @param \Magento\Backend\App\Action\Context
     * @param \Magento\Framework\ObjectManagerInterface
     * @param \Magento\Framework\Filesystem
     * @param \Magento\Backend\Helper\Js
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Backend\Helper\Js $jsHelper,
        \Magento\Framework\App\Request\Http $httpRequest,
        \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone
    ) {
        $this->_fileSystem  = $filesystem;
        $this->jsHelper     = $jsHelper;
        $this->httpRequest  = $httpRequest;
        $this->_stdTimezone = $_stdTimezone;
        parent::__construct($context);
    }


    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Crimson_Testimonial::testimonial_save');
    }


    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data        = $this->getRequest()->getPostValue();
        $dateTimeNow = $this->_stdTimezone->date()->format('Y-m-d H:i:s');
        $links       = $this->getRequest()->getPost('links');
        $links       = is_array($links) ? $links : [];
        if(!empty($links) && isset($links['products'])) {
            $testimonialProducts          = $this->jsHelper->decodeGridSerializedInput($links['products']);
            $data['testimonial_products'] = $testimonialProducts;
        }else{
            $data['testimonial_products'] = [];
        }

        if(!empty($links) && isset($links['posts'])) {
            $posts         = $this->jsHelper->decodeGridSerializedInput($links['posts']);
            $data['posts'] = $posts;
        }

        /*
            *
            *
            * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
        */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create('Crimson\Testimonial\Model\Testimonial');

            $id = $this->getRequest()->getParam('testimonial_id');
            if ($id) {
                $model->load($id);
            }

            /*
                *
                *
                * @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory
            */
            $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                ->getDirectoryRead(DirectoryList::MEDIA);
            $mediaFolder    = 'ves/testimonial/';
            $path           = $mediaDirectory->getAbsolutePath($mediaFolder);

            // Delete, Upload Image
            $imagePath = $mediaDirectory->getAbsolutePath($model->getImage());
            if(isset($data['image']['delete']) && file_exists($imagePath)) {
                unlink($imagePath);
                $data['image'] = '';
            }

            if(isset($data['image']) && is_array($data['image'])) {
                unset($data['image']);
            }

            if($image = $this->uploadImage('image')) {
                $data['image'] = $image;
            }

            $data['create_time'] = $dateTimeNow;

            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved this Testimonial.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['testimonial_id' => $model->getId(), '_current' => true]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the testimonial.'));
                $this->messageManager->addError($e->getMessage());
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['testimonial_id' => $this->getRequest()->getParam('testimonial_id')]);
        }//end if

        return $resultRedirect->setPath('*/*/');
    }


    public function uploadImage($fieldId = 'image')
    {
        $image = $this->httpRequest->getFiles($fieldId);
        if (isset($image['error']) && $image['error'] == 0) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $uploader       = $this->_objectManager->create(
                'Magento\Framework\File\Uploader',
                array('fileId' => $fieldId)
            );
            if ($uploader) {
                $uploader = $this->_objectManager->create(
                    'Magento\Framework\File\Uploader',
                    array('fileId' => $fieldId)
                );
                $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                    ->getDirectoryRead(DirectoryList::MEDIA);
                $mediaFolder    = 'ves/testimonial/';
                try {
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    $result = $uploader->save(
                        $mediaDirectory->getAbsolutePath($mediaFolder)
                    );
                    return $mediaFolder.$result['name'];
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                    $this->messageManager->addError($e->getMessage());
                    return $resultRedirect->setPath('*/*/edit', ['testimonial_id' => $this->getRequest()->getParam('testimonial_id')]);
                }
            }//end if
        }
        return;
    }
}
