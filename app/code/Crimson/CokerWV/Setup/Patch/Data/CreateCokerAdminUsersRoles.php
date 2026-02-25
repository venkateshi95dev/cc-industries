<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

class CreateCokerAdminUsersRoles implements DataPatchInterface
{

    public function __construct(
        private readonly RoleFactory $roleFactory,
        private readonly RulesFactory $rulesFactory,
        private readonly WebsiteRepositoryInterface $websiteRepository,
        private readonly StoreRepositoryInterface $storeRepository
    ) {}


    public function apply(): void
    {
        //GoDataFeed
        //Websites
        $cokerTireWebsiteId = $this->getWebsiteId(CokerStoreInterface::COKER_WEBSITE_CODE);
        $wvWebsiteId = $this->getWebsiteId(WVStoreInterface::WV_WEBSITE_CODE);
        $websitesIds[] = $cokerTireWebsiteId;
        $websitesIds[] = $wvWebsiteId;
        $websitesIdsImploded = implode(",", $websitesIds);

        //StoreGroups
        $cokerTireStoreGroupId = $this->getGroupIdSByStoreCode(CokerStoreInterface::COKER_STORE_CODE);
        $wvStoreGroupId        = $this->getGroupIdSByStoreCode(WVStoreInterface::WV_STORE_CODE);
        $storeGroupIds[] = $cokerTireStoreGroupId;
        $storeGroupIds[] = $wvStoreGroupId;
        $storeGroupsImploded = implode(",", $storeGroupIds);

        $roleGoDataFeed = $this->roleFactory->create();
        $roleGoDataFeed
            ->setName('GoDataFeed')
            ->setPid(0)
            ->setRoleType(RoleGroup::ROLE_TYPE)
            ->setUserType(UserContextInterface::USER_TYPE_ADMIN)
            ->setGwsIsAll(1)
            ->setGwsWebsites($websitesIdsImploded)
            ->setGwsStoreGroups($storeGroupsImploded)
        ;
        $roleGoDataFeed->save();

        $resourceGoDataFeed = [
            'Magento_Backend::all',
        ];
        $this->rulesFactory->create()
            ->setRoleId($roleGoDataFeed->getId())
            ->setResources($resourceGoDataFeed)
            ->saveRel()
        ;

        //Standard
        $roleStandard = $this->roleFactory->create();
        $roleStandard
            ->setName('Standard')
            ->setPid(0)
            ->setRoleType(RoleGroup::ROLE_TYPE)
            ->setUserType(UserContextInterface::USER_TYPE_ADMIN)
            ->setGwsIsAll(1)
            ->setGwsWebsites($websitesIdsImploded)
            ->setGwsStoreGroups($storeGroupsImploded)
        ;
        $roleStandard->save();

        $resourceStandard = [
            "Aheadworks_Faq::article",
            "Aheadworks_Faq::category",
            "Aheadworks_Faq::elements",
            "Amasty_Finder::finder",
            "Bss_UrlRewriteImportExport::importexport_url_rewrite_export",
            "Bss_UrlRewriteImportExport::importexport_url_rewrite_import",
            "Cokertire_Disclaimers::disclaimers",
            "Cokertire_Distributors::distributors",
            "CommerceExtensions_UrlrewritesImportExport::import_export",
            "Ct_Productquestions::productquestions",
            "Magento_AdminNotification::adminnotification",
            "Magento_AdminNotification::adminnotification_remove",
            "Magento_AdminNotification::mark_as_read",
            "Magento_AdminNotification::show_list",
            "Magento_AdminNotification::show_toolbar",
            "Magento_AdvancedCheckout::magento_advancedcheckout",
            "Magento_AdvancedCheckout::update",
            "Magento_AdvancedCheckout::view",
            "Magento_Backend::admin",
            "Magento_Backend::cache",
            "Magento_Backend::content",
            "Magento_Backend::content_elements",
            "Magento_Backend::convert",
            "Magento_Backend::custom",
            "Magento_Backend::extensions",
            "Magento_Backend::global_search",
            "Magento_Backend::local",
            "Magento_Backend::marketing",
            "Magento_Backend::marketing_user_content",
            "Magento_Backend::myaccount",
            "Magento_Backend::stores",
            "Magento_Backend::stores_attributes",
            "Magento_Backend::system",
            "Magento_Backend::system_other_settings",
            "Magento_Backend::tools",
            "Magento_Backup::backup",
            "Magento_Backup::rollback",
            "Magento_Cart::cart",
            "Magento_Cart::manage",
            "Magento_Catalog::attributes_attributes",
            "Magento_Catalog::catalog",
            "Magento_Catalog::catalog_inventory",
            "Magento_Catalog::categories",
            "Magento_Catalog::products",
            "Magento_Catalog::sets",
            "Magento_Catalog::update_attributes",
            "Magento_CatalogPermissions::catalog_magento_catalogpermissions",
            "Magento_CatalogRule::promo",
            "Magento_CatalogRule::promo_catalog",
            "Magento_Cms::media_gallery",
            "Magento_CurrencySymbol::currency_rates",
            "Magento_CurrencySymbol::symbols",
            "Magento_CurrencySymbol::system_currency",
            "Magento_Customer::customer",
            "Magento_Customer::manage",
            "Magento_Customer::online",
            "Magento_CustomerCustomAttributes::attributes",
            "Magento_CustomerCustomAttributes::customer_address_attributes",
            "Magento_CustomerCustomAttributes::customer_attributes",
            "Magento_CustomerSegment::customersegment",
            "Magento_CustomerSegment::segment",
            "Magento_EncryptionKey::crypt_key",
            "Magento_GiftCardAccount::customer_giftcardaccount",
            "Magento_ImportExport::export",
            "Magento_ImportExport::history",
            "Magento_ImportExport::import",
            "Magento_Indexer::changeMode",
            "Magento_Indexer::index",
            "Magento_Invitation::general",
            "Magento_Invitation::magento_invitation_customer",
            "Magento_Invitation::order",
            "Magento_Invitation::report_magento_invitation",
            "Magento_Logging::backups",
            "Magento_Logging::magento_logging",
            "Magento_Logging::magento_logging_events",
            "Magento_MediaGalleryUiApi::create_folder",
            "Magento_MediaGalleryUiApi::delete_assets",
            "Magento_MediaGalleryUiApi::delete_folder",
            "Magento_MediaGalleryUiApi::edit_assets",
            "Magento_MediaGalleryUiApi::insert_assets",
            "Magento_MediaGalleryUiApi::upload_assets",
            "Magento_MultipleWishlist::wishlist",
            "Magento_Newsletter::problem",
            "Magento_Paypal::actions_manage",
            "Magento_Paypal::billing_agreement",
            "Magento_Paypal::billing_agreement_actions",
            "Magento_Paypal::billing_agreement_actions_view",
            "Magento_Paypal::fetch",
            "Magento_Paypal::paypal_settlement_reports",
            "Magento_Paypal::paypal_settlement_reports_view",
            "Magento_Paypal::use",
            "Magento_PricePermissions::edit_product_price",
            "Magento_PricePermissions::edit_product_status",
            "Magento_PricePermissions::read_product_price",
            "Magento_PromotionPermissions::edit",
            "Magento_PromotionPermissions::quote_edit",
            "Magento_Reports::abandoned",
            "Magento_Reports::accounts",
            "Magento_Reports::bestsellers",
            "Magento_Reports::coupons",
            "Magento_Reports::customers",
            "Magento_Reports::customers_orders",
            "Magento_Reports::downloads",
            "Magento_Reports::invoiced",
            "Magento_Reports::lowstock",
            "Magento_Reports::product",
            "Magento_Reports::refunded",
            "Magento_Reports::report",
            "Magento_Reports::report_marketing",
            "Magento_Reports::report_products",
            "Magento_Reports::report_search",
            "Magento_Reports::review",
            "Magento_Reports::review_customer",
            "Magento_Reports::review_product",
            "Magento_Reports::salesroot",
            "Magento_Reports::salesroot_sales",
            "Magento_Reports::shipping",
            "Magento_Reports::shopcart",
            "Magento_Reports::sold",
            "Magento_Reports::statistics",
            "Magento_Reports::statistics_refresh",
            "Magento_Reports::tax",
            "Magento_Reports::totals",
            "Magento_Reports::viewed",
            "Magento_Review::ratings",
            "Magento_Review::reviews_all",
            "Magento_Reward::reward_balance",
            "Magento_Reward::reward_spend",
            "Magento_Rma::magento_rma",
            "Magento_Rma::rma_attribute",
            "Magento_Sales::actions",
            "Magento_Sales::actions_edit",
            "Magento_Sales::actions_view",
            "Magento_Sales::cancel",
            "Magento_Sales::capture",
            "Magento_Sales::comment",
            "Magento_Sales::create",
            "Magento_Sales::creditmemo",
            "Magento_Sales::email",
            "Magento_Sales::emails",
            "Magento_Sales::hold",
            "Magento_Sales::invoice",
            "Magento_Sales::reorder",
            "Magento_Sales::review_payment",
            "Magento_Sales::sales",
            "Magento_Sales::sales_creditmemo",
            "Magento_Sales::sales_invoice",
            "Magento_Sales::sales_operation",
            "Magento_Sales::sales_order",
            "Magento_Sales::ship",
            "Magento_Sales::shipment",
            "Magento_Sales::transactions",
            "Magento_Sales::transactions_fetch",
            "Magento_Sales::unhold",
            "Magento_SalesArchive::add",
            "Magento_SalesArchive::archive",
            "Magento_SalesArchive::creditmemos",
            "Magento_SalesArchive::invoices",
            "Magento_SalesArchive::orders",
            "Magento_SalesArchive::remove",
            "Magento_SalesArchive::shipments",
            "Magento_SalesRule::quote",
            "Magento_ScheduledImportExport::magento_scheduled_operation",
            "Magento_Support::support",
            "Magento_Support::support_backup",
            "Magento_Support::support_report",
            "Magento_TargetRule::targetrule",
            "Magento_Tax::manage_tax",
            "Magento_TaxImportExport::import_export",
            "Magento_User::acl",
            "Magento_User::acl_roles",
            "Magento_User::acl_users",
            "Magento_User::locks",
            "Magento_Variable::variable"
        ];
        $this->rulesFactory->create()
            ->setRoleId($roleStandard->getId())
            ->setResources($resourceStandard)
            ->saveRel()
        ;

        //WV Sales
        $roleWVSales = $this->roleFactory->create();
        $roleWVSales
            ->setName('WV Sales')
            ->setPid(0)
            ->setRoleType(RoleGroup::ROLE_TYPE)
            ->setUserType(UserContextInterface::USER_TYPE_ADMIN)
            ->setGwsIsAll(0)
            ->setGwsWebsites($wvWebsiteId)
        ;
        $roleWVSales->save();

        $resourceWVSales = [
            "Magento_AdvancedCheckout::magento_advancedcheckout",
            "Magento_AdvancedCheckout::update",
            "Magento_AdvancedCheckout::view",
            "Magento_Backend::admin",
            "Magento_Backend::content",
            "Magento_Backend::content_elements",
            "Magento_Backend::dashboard",
            "Magento_Cart::cart",
            "Magento_Cart::manage",
            "Magento_Cms::media_gallery",
            "Magento_MediaGalleryUiApi::create_folder",
            "Magento_MediaGalleryUiApi::delete_assets",
            "Magento_MediaGalleryUiApi::delete_folder",
            "Magento_MediaGalleryUiApi::edit_assets",
            "Magento_MediaGalleryUiApi::insert_assets",
            "Magento_MediaGalleryUiApi::upload_assets",
            "Magento_Paypal::actions_manage",
            "Magento_Paypal::authorization",
            "Magento_Paypal::billing_agreement",
            "Magento_Paypal::billing_agreement_actions",
            "Magento_Paypal::billing_agreement_actions_view",
            "Magento_Paypal::use",
            "Magento_Reward::reward_spend",
            "Magento_Rma::magento_rma",
            "Magento_Sales::actions",
            "Magento_Sales::actions_edit",
            "Magento_Sales::actions_view",
            "Magento_Sales::cancel",
            "Magento_Sales::capture",
            "Magento_Sales::comment",
            "Magento_Sales::create",
            "Magento_Sales::creditmemo",
            "Magento_Sales::email",
            "Magento_Sales::emails",
            "Magento_Sales::hold",
            "Magento_Sales::invoice",
            "Magento_Sales::reorder",
            "Magento_Sales::review_payment",
            "Magento_Sales::sales",
            "Magento_Sales::sales_creditmemo",
            "Magento_Sales::sales_invoice",
            "Magento_Sales::sales_operation",
            "Magento_Sales::sales_order",
            "Magento_Sales::ship",
            "Magento_Sales::shipment",
            "Magento_Sales::transactions",
            "Magento_Sales::transactions_fetch",
            "Magento_Sales::unhold",
            "Magento_SalesArchive::add",
            "Magento_SalesArchive::archive",
            "Magento_SalesArchive::creditmemos",
            "Magento_SalesArchive::invoices",
            "Magento_SalesArchive::orders",
            "Magento_SalesArchive::remove",
            "Magento_SalesArchive::shipments",
            "MageWorx_OrderEditor::delete_order",
            "MageWorx_OrderEditor::edit_account",
            "MageWorx_OrderEditor::edit_address",
            "MageWorx_OrderEditor::edit_info",
            "MageWorx_OrderEditor::edit_items",
            "MageWorx_OrderEditor::edit_order",
            "MageWorx_OrderEditor::edit_payment",
            "MageWorx_OrderEditor::edit_shipping"
        ];
        $this->rulesFactory->create()
            ->setRoleId($roleWVSales->getId())
            ->setResources($resourceWVSales)
            ->saveRel()
        ;

        //CTC Sales
        $roleCTCSales = $this->roleFactory->create();
        $roleCTCSales
            ->setName('CTC Sales')
            ->setPid(0)
            ->setRoleType(RoleGroup::ROLE_TYPE)
            ->setUserType(UserContextInterface::USER_TYPE_ADMIN)
            ->setGwsIsAll(0)
            ->setGwsWebsites($cokerTireWebsiteId)
        ;
        $roleCTCSales->save();

        $resourceCTCSales = [
            "Amasty_Mostviewed::mostviewed_sales",
            "Amasty_Mostviewed::pack_sales",
            "Amasty_Xsearch::activity",
            "Amasty_Xsearch::advanced_search",
            "Amasty_Xsearch::search_analytics",
            "Amasty_Xsearch::wanted",
            "ClassyLlama_AvaTax::avalara_update_customer_information",
            "ClassyLlama_AvaTax::customer_certificates",
            "Dotdigitalgroup_Email::abandoned",
            "Dotdigitalgroup_Email::automation_enrollment",
            "Dotdigitalgroup_Email::campaign",
            "Dotdigitalgroup_Email::catalog",
            "Dotdigitalgroup_Email::contact",
            "Dotdigitalgroup_Email::cron",
            "Dotdigitalgroup_Email::dashboard",
            "Dotdigitalgroup_Email::importer",
            "Dotdigitalgroup_Email::logviewer",
            "Dotdigitalgroup_Email::order",
            "Dotdigitalgroup_Email::reports",
            "Dotdigitalgroup_Email::review",
            "Dotdigitalgroup_Email::wishlist",
            "Magento_AdvancedCheckout::magento_advancedcheckout",
            "Magento_AdvancedCheckout::update",
            "Magento_AdvancedCheckout::view",
            "Magento_Analytics::advanced_reporting",
            "Magento_Analytics::bi_essentials",
            "Magento_Analytics::business_intelligence",
            "Magento_Backend::admin",
            "Magento_Backend::content",
            "Magento_Backend::content_elements",
            "Magento_Backend::dashboard",
            "Magento_Backend::myaccount",
            "Magento_Cart::cart",
            "Magento_Cart::manage",
            "Magento_Cms::media_gallery",
            "Magento_Customer::actions",
            "Magento_Customer::customer",
            "Magento_Customer::delete",
            "Magento_Customer::group",
            "Magento_Customer::invalidate_tokens",
            "Magento_Customer::manage",
            "Magento_Customer::online",
            "Magento_Customer::reset_password",
            "Magento_CustomerSegment::customersegment",
            "Magento_CustomerSegment::segment",
            "Magento_Invitation::general",
            "Magento_Invitation::magento_invitation_customer",
            "Magento_Invitation::order",
            "Magento_Invitation::report_magento_invitation",
            "Magento_MediaGalleryUiApi::create_folder",
            "Magento_MediaGalleryUiApi::delete_assets",
            "Magento_MediaGalleryUiApi::delete_folder",
            "Magento_MediaGalleryUiApi::edit_assets",
            "Magento_MediaGalleryUiApi::insert_assets",
            "Magento_MediaGalleryUiApi::upload_assets",
            "Magento_MultipleWishlist::wishlist",
            "Magento_Newsletter::problem",
            "Magento_Paypal::actions_manage",
            "Magento_Paypal::authorization",
            "Magento_Paypal::billing_agreement",
            "Magento_Paypal::billing_agreement_actions",
            "Magento_Paypal::billing_agreement_actions_view",
            "Magento_Paypal::fetch",
            "Magento_Paypal::paypal_settlement_reports",
            "Magento_Paypal::paypal_settlement_reports_view",
            "Magento_Paypal::use",
            "Magento_Reports::abandoned",
            "Magento_Reports::accounts",
            "Magento_Reports::bestsellers",
            "Magento_Reports::coupons",
            "Magento_Reports::customers",
            "Magento_Reports::customers_orders",
            "Magento_Reports::downloads",
            "Magento_Reports::invoiced",
            "Magento_Reports::lowstock",
            "Magento_Reports::product",
            "Magento_Reports::refunded",
            "Magento_Reports::report",
            "Magento_Reports::report_marketing",
            "Magento_Reports::report_products",
            "Magento_Reports::report_search",
            "Magento_Reports::review",
            "Magento_Reports::review_customer",
            "Magento_Reports::review_product",
            "Magento_Reports::salesroot",
            "Magento_Reports::salesroot_sales",
            "Magento_Reports::shipping",
            "Magento_Reports::shopcart",
            "Magento_Reports::sold",
            "Magento_Reports::statistics",
            "Magento_Reports::statistics_refresh",
            "Magento_Reports::tax",
            "Magento_Reports::totals",
            "Magento_Reports::viewed",
            "Magento_Reward::reward_balance",
            "Magento_Reward::reward_spend",
            "Magento_Rma::magento_rma",
            "Magento_Sales::actions",
            "Magento_Sales::actions_edit",
            "Magento_Sales::actions_view",
            "Magento_Sales::cancel",
            "Magento_Sales::capture",
            "Magento_Sales::comment",
            "Magento_Sales::create",
            "Magento_Sales::creditmemo",
            "Magento_Sales::email",
            "Magento_Sales::emails",
            "Magento_Sales::hold",
            "Magento_Sales::invoice",
            "Magento_Sales::reorder",
            "Magento_Sales::review_payment",
            "Magento_Sales::sales",
            "Magento_Sales::sales_creditmemo",
            "Magento_Sales::sales_invoice",
            "Magento_Sales::sales_operation",
            "Magento_Sales::sales_order",
            "Magento_Sales::ship",
            "Magento_Sales::shipment",
            "Magento_Sales::transactions",
            "Magento_Sales::transactions_fetch",
            "Magento_Sales::unhold",
            "Magento_SalesArchive::add",
            "Magento_SalesArchive::archive",
            "Magento_SalesArchive::creditmemos",
            "Magento_SalesArchive::invoices",
            "Magento_SalesArchive::orders",
            "Magento_SalesArchive::remove",
            "Magento_SalesArchive::shipments",
            "MageWorx_OrderEditor::delete_order",
            "MageWorx_OrderEditor::edit_account",
            "MageWorx_OrderEditor::edit_address",
            "MageWorx_OrderEditor::edit_info",
            "MageWorx_OrderEditor::edit_items",
            "MageWorx_OrderEditor::edit_order",
            "MageWorx_OrderEditor::edit_payment",
            "MageWorx_OrderEditor::edit_shipping"
        ];
        $this->rulesFactory->create()
            ->setRoleId($roleCTCSales->getId())
            ->setResources($resourceCTCSales)
            ->saveRel()
        ;

        //Agency Role
        $roleAgencyRole = $this->roleFactory->create();
        $roleAgencyRole
            ->setName('Agency Role')
            ->setPid(0)
            ->setRoleType(RoleGroup::ROLE_TYPE)
            ->setUserType(UserContextInterface::USER_TYPE_ADMIN)
            ->setGwsIsAll(1)
            ->setGwsWebsites($websitesIdsImploded)
            ->setGwsStoreGroups($storeGroupsImploded)
        ;
        $roleAgencyRole->save();

        $resourceAgencyRole = [
            "Amasty_Mostviewed::mostviewed_sales",
            "Amasty_Mostviewed::pack_sales",
            "Amasty_Xsearch::activity",
            "Amasty_Xsearch::advanced_search",
            "Amasty_Xsearch::search_analytics",
            "Amasty_Xsearch::wanted",
            "Dotdigitalgroup_Email::abandoned",
            "Dotdigitalgroup_Email::automation_enrollment",
            "Dotdigitalgroup_Email::campaign",
            "Dotdigitalgroup_Email::catalog",
            "Dotdigitalgroup_Email::contact",
            "Dotdigitalgroup_Email::cron",
            "Dotdigitalgroup_Email::dashboard",
            "Dotdigitalgroup_Email::importer",
            "Dotdigitalgroup_Email::logviewer",
            "Dotdigitalgroup_Email::order",
            "Dotdigitalgroup_Email::reports",
            "Dotdigitalgroup_Email::review",
            "Dotdigitalgroup_Email::wishlist",
            "Dotdigitalgroup_Sms::report",
            "Dotdigitalgroup_Sms::reports",
            "Magento_Analytics::advanced_reporting",
            "Magento_Analytics::bi_essentials",
            "Magento_Analytics::business_intelligence",
            "Magento_Backend::admin",
            "Magento_CustomerSegment::segment",
            "Magento_Invitation::general",
            "Magento_Invitation::magento_invitation_customer",
            "Magento_Invitation::order",
            "Magento_Invitation::report_magento_invitation",
            "Magento_Newsletter::problem",
            "Magento_Paypal::fetch",
            "Magento_Paypal::paypal_settlement_reports",
            "Magento_Paypal::paypal_settlement_reports_view",
            "Magento_Reports::abandoned",
            "Magento_Reports::bestsellers",
            "Magento_Reports::coupons",
            "Magento_Reports::customers",
            "Magento_Reports::customers_orders",
            "Magento_Reports::downloads",
            "Magento_Reports::invoiced",
            "Magento_Reports::lowstock",
            "Magento_Reports::product",
            "Magento_Reports::refunded",
            "Magento_Reports::report",
            "Magento_Reports::report_marketing",
            "Magento_Reports::report_products",
            "Magento_Reports::report_search",
            "Magento_Reports::review",
            "Magento_Reports::review_customer",
            "Magento_Reports::review_product",
            "Magento_Reports::salesroot",
            "Magento_Reports::salesroot_sales",
            "Magento_Reports::shipping",
            "Magento_Reports::shopcart",
            "Magento_Reports::sold",
            "Magento_Reports::statistics",
            "Magento_Reports::statistics_refresh",
            "Magento_Reports::tax",
            "Magento_Reports::totals",
            "Magento_Reports::viewed",
            "Magento_Swat::swat",
            "Magento_Swat::system_insights",
            "PayPal_Braintree::settlement_report"

        ];
        $this->rulesFactory->create()
            ->setRoleId($roleAgencyRole->getId())
            ->setResources($resourceAgencyRole)
            ->saveRel()
        ;
    }

    /**
     * @param string $code
     * @return int|null
     */
    private function getWebsiteId(string $code): ?int
    {
        try {
            return $this->websiteRepository->get($code)->getId();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param string $storeCode
     * @return int|null
     */
    private function getGroupIdSByStoreCode(string $storeCode): ?int
    {
        try {
            return $this->storeRepository->get($storeCode)->getStoreGroupId();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [
            CreateCokerWebiste::class,
            CreateWVWebiste::class,
            SetShareCustomerAccountsToWebsite::class
        ];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
