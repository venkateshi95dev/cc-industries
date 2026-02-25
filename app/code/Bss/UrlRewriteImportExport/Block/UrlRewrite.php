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
 * =================================================================
 *
 * MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_UrlRewriteImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\UrlRewriteImportExport\Block;

use Magento\Framework\View\Element\Template;

class UrlRewrite extends Template
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Authorization\Model\Acl\AclRetriever
     */
    protected $aclRetriever;

    /**
     * UrlRewrite constructor.
     * @param Template\Context $context
     * @param \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->authSession=$authSession;
        $this->aclRetriever=$aclRetriever;
    }

    /**
     * @return \Magento\User\Model\User|null
     */
    public function getCurrentUser()
    {
        return $this->authSession->getUser();
    }

    /**
     * @return string[]
     */
    public function getRoleResource()
    {
        $user = $this->authSession->getUser();
        $role = $user->getRole();
        $resources = $this->aclRetriever->getAllowedResourcesByRole($role->getId());
        return $resources;
    }
}
