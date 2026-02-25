<?php

namespace Crimson\Testimonial\Controller\Adminhtml\Category;

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
     * @param \Magento\Backend\App\Action\Context
     * @param \Magento\Framework\ObjectManagerInterface
     * @param \Magento\Framework\Filesystem
     * @param \Magento\Backend\Helper\Js
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Backend\Helper\Js $jsHelper,
        \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone
    ) {
        $this->_fileSystem  = $filesystem;
        $this->jsHelper     = $jsHelper;
        $this->_stdTimezone = $_stdTimezone;
        parent::__construct($context);
    }


    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Crimson_Testimonial::category_save');
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
        if(!empty($links)) {
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
            $model = $this->_objectManager->create('Crimson\Testimonial\Model\Category');

            $id = $this->getRequest()->getParam('category_id');
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

            $data['create_time'] = $dateTimeNow;

            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved this Category.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['category_id' => $model->getId(), '_current' => true]);
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
            return $resultRedirect->setPath('*/*/edit', ['category_id' => $this->getRequest()->getParam('category_id')]);
        }//end if

        return $resultRedirect->setPath('*/*/');
    }
}
