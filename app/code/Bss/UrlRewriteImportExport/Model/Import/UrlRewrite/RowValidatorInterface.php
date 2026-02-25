<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_UrlRewriteImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\UrlRewriteImportExport\Model\Import\UrlRewrite;

interface RowValidatorInterface extends \Magento\Framework\Validator\ValidatorInterface
{
    public const ERROR_INVALID_ENTITY_TYPE = 'errorInvalidEntityType';
    public const ERROR_REQUEST_PATH_NOT_EXIST = 'errorRequestPathNotExist';
    public const ERROR_INVALID_REDIRECT_TYPE = 'errorInvalidRedirectType';
    public const ERROR_EMPTY_ENTITY_TYPE = 'errorEmptyEntityType';
    public const ERROR_PRODUCT_ID_NOT_EXIST = 'errorProductIdNotExist';
    public const ERROR_CATEGORY_ID_NOT_EXIST = 'errorCategoryIdNotExist';
    public const ERROR_CMS_PAGE_ID_NOT_EXIST = 'errorCmsPageIdNotExist';
    public const ERROR_EMPTY_PRODUCT_ID = 'errorEmptyProductId';
    public const ERROR_EMPTY_CATEGORY_ID = 'errorEmptyCategoryId';
    public const ERROR_EMPTY_CMS_PAGE_ID = 'errorEmptyCmsPageId';
    public const ERROR_EMPTY_TARGET_PATH = 'errorEmptyTargetPath';
    public const ERROR_EMPTY_REQUEST_PATH = 'errorEmptyRequestPath';
    public const ERROR_EXISTED_REQUEST_PATH = 'existedRequestPath';
    public const ERROR_INVALID_STORE_ID = 'invalidStoreId';
    public const ERROR_TARGET_PATH_NOT_EXIST = 'targetPathNotExist';
}
