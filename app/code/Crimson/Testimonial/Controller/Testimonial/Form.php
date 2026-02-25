<?php

namespace Crimson\Testimonial\Controller\Testimonial;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

class Form extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Crimson\Testimonial\Model\Testimonial
     **/
    protected $testimonialCollection;

    /**
     * stdlib timezone.
     *
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     ":?"
     */
    protected $_stdTimezone;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;

    /**
     * @var \Crimson\Testimonial\Helper\Data
     */
    protected $_helper;

    private static $_siteVerifyUrl = "https://www.google.com/recaptcha/api/siteverify?";
    private $_secret;
    private static $_version = "php_1.0";

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Crimson\Testimonial\Model\Testimonial $testimonialCollection,
        \Magento\Framework\Stdlib\DateTime\Timezone $stdTimezone,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\App\Request\Http $httpRequest,
        \Crimson\Testimonial\Helper\Data $helper
    ) {
        $this->resultPageFactory     = $resultPageFactory;
        $this->testimonialCollection = $testimonialCollection;
        $this->_stdTimezone          = $stdTimezone;
        $this->_storeManager         = $storeManager;
        $this->_objectManager        = $context->getObjectManager();
        $this->_fileSystem           = $filesystem;
        $this->_helper               = $helper;
        $this->httpRequest           = $httpRequest;
        $this->_remoteAddress        = $remoteAddress;
        parent::__construct($context);
    }


    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $param          = $this->getRequest()->getParams();

        $post = $this->getRequest()->getPostValue();
        $enableAvatar = $this->_helper->getConfig("general/enable_avatar");
        // reCaptcha begin
        $enableCatpcha = $this->_helper->getConfig("general/enable_retcaptcha");
        if($enableCatpcha) {
            if(isset($post['g-recaptcha-response']) && ((int) $post['g-recaptcha-response']) === 0) {
                $this->messageManager->addError(__('Please check reCaptcha and try again.'));
                $this->_redirect($param['return_url']);
                return;
            }

            if (isset($post['g-recaptcha-response'])) {
                $captcha      = $post['g-recaptcha-response'];
                $secretKey    = $this->_helper->getCaptchaSecretKey();
                $ip           = $this->_remoteAddress->getRemoteAddress();
                $response     = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $secretKey . "&response=" . $captcha . "&remoteip=" . $ip);
                $responseKeys = json_decode($response, true);
                if (intval($responseKeys["success"]) !== 1) {
                    $this->messageManager->addError(__('Please check reCaptcha and try again.'));
                    $this->_redirect($param['return_url']);
                    return;
                }
            }
        }

        // reCaptcha End
        $store       = $this->_storeManager->getStore();
        $param       = $this->getRequest()->getParams();
        $dateTimeNow = $this->_stdTimezone->date()->format('Y-m-d H:i:s');
        $param['title'] = isset($param['title'])?$param['title']:"";
        $param['nick_name'] = isset($param['nick_name'])?$param['nick_name']:"";
        $param['name'] = isset($param['name'])?$param['name']:"";
        if($param['name'] && !$param['nick_name']){
            $param['nick_name'] = $param['name'];
        }
        if($param['name'] && !$param['title']){
            $param['title'] = $param['name'];
        }

        $param['is_active']   = '0';
        $param['create_time'] = $dateTimeNow;
        if($enableAvatar) {
            $param['image']       = $this->uploadImage();
        } else {
            $param['image']       = "";
        }

        $param['stores']      = [$store->getId()];

        $this->testimonialCollection->setData($param);
        try{
            $this->testimonialCollection->save();
            $this->messageManager->addSuccess(__('Thank for your testimonial.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving your testimonial.'));
            $this->messageManager->addError($e->getMessage());
        }

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }


    public function uploadImage()
    {
        $image = $this->httpRequest->getFiles('image');
        if (isset($image['error']) && $image['error'] == 0) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $uploader       = $this->_objectManager->create(
                'Magento\Framework\File\Uploader',
                array('fileId' => 'image')
            );

            if ($uploader) {
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
                }
            }
        }

        return;
    }
}
