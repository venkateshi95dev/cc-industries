<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Controller\Adminhtml\DocumentList;

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class Upload
 */
class Upload extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    const UPLOAD_DIR = 'catalog/product/wysiwyg';

    const ADMIN_RESOURCE = 'Silk_ProductImage::save';

    protected $allowedExtension = ['jpeg','jpg','png','gif'];

    protected $mimeType = ['image/png', 'image/jpeg', 'image/gif'];
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\File\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;



    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem\DirectoryList $directoryList
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->uploaderFactory = $uploaderFactory;
        $this->directoryList = $directoryList;
    }

    /**
     * Retrieve path
     *
     * @param string $path
     * @param string $imageName
     * @return string
     */
    private function getFilePath($path, $imageName)
    {
        return rtrim($path, '/') . '/' . ltrim($imageName, '/');
    }

    /**
     * Allow users to upload images to the folder structure
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $fieldName = $this->getRequest()->getParam('param_name');
        $fileUploader = $this->uploaderFactory->create(['fileId' => $fieldName]);

        // Set our parameters
        $fileUploader->setFilesDispersion(false);
        $fileUploader->setAllowRenameFiles(true);
        $fileUploader->setAllowedExtensions($this->allowedExtension);
        $fileUploader->setAllowCreateFolders(true);

        try {
            if (!$fileUploader->checkMimeType($this->mimeType)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('File validation failed.'));
            }

            $result = $fileUploader->save($this->getUploadDir());
            $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $result['url'] = $baseUrl . $this->getFilePath(self::UPLOAD_DIR, $result['file']);
            $result['name'] = $result['file'];
        } catch (\Exception $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }
        return $this->resultJsonFactory->create()->setData($result);
    }

    /**
     * Return the upload directory
     *
     * @return string
     */
    private function getUploadDir()
    {
        return $this->directoryList->getPath('media') . DIRECTORY_SEPARATOR . self::UPLOAD_DIR;
    }
}
