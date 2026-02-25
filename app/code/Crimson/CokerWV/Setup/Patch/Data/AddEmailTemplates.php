<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Magento\Framework\App\Area as AppArea;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\State as AppState;

class AddEmailTemplates implements DataPatchInterface
{

    //Email templates content
    protected array $_templatesData = [
        [
            'template_code' => 'Abandoned Cart Coker_WV',
            'template_text' => '{{template config_path="design/email/header_template"}}

<table>
    <tr class="email-intro">
        <td>
{{var customer.getName()|escape}}
{{store url="customer/account/"}}<br/><br/>
            <p>We noticed you left some products in your cart. If you have a question about your order, in regard to the product, pricing or shipping quote, please give us a call! </p>
<P>We’d love the opportunity to earn your business!</p>
<p>Please Call <strong>877-439-6426</strong></p>
 <p><i>Monday through Friday 8:00am - 8:00pm Eastern</i></p>
<p>{{trans \'Access or Create a Customer account here by <a href="%account_url">logging into your account</a>.\' account_url=$this.getUrl($store,\'customer/account/\',[_nosid:1]) |raw}}</p>
<p>When you create an account, you can save and manage your cart, wish list, addresses, \'My Garage\' vehicles and more. We can even help you manage a saved cart in your registered account. Just let us know and we\'ll help you!</p>


        </td>
    </tr>
    </table>

{{template config_path="design/email/footer_template"}}',
            'template_styles' => null,
            'template_type' => 2,
            'template_subject' => 'Need Help or Have Questions On the Products in your Cart?',
            'template_sender_name' => null,
            'template_sender_email' => null,
            'added_at' => '2018-08-16 15:30:27',
            'modified_at' => '2022-01-19 21:20:07',
            'orig_template_code' => null,
            'orig_template_variables' => null,
            'is_legacy' => 1,
        ],
        [
            'template_code' => 'Coker New Credit Memo Refund Coker_WV',
            'template_text' => '{{template config_path="design/email/header_template"}}

<table>
    <tr class="email-intro">
        <td>
            <p class="greeting">{{trans "%name," name=$order.getCustomerName()}}</p>
            <p>
                {{trans "Thank you for your order from %store_name." store_name=$store.getFrontendName() }}
                {{trans \'A refund has been created for this order. You can check the status of your order by <a href="%account_url">logging into your account</a>.\' account_url=$this.getUrl($store,\'customer/account/\',[_nosid:1]) |raw}}
                {{trans \'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>\' store_email=$store_email |raw}}{{depend store_phone}} {{trans \'or call us at <a href="tel:%store_phone">%store_phone</a>\' store_phone=$store_phone |raw}}{{/depend}}.
                {{depend store_hours}}
                    {{trans \'Our hours are <span class="no-link">%store_hours</span>.\' store_hours=$store_hours |raw}}
                {{/depend}}
            </p>
        </td>
    </tr>
    <tr class="email-summary">
        <td>
             <h1>{{trans "Your Refund for Order #%order_id" order_id=$order.increment_id}}</h1>
        </td>
    </tr>
    <tr class="email-information">
        <td>
            {{depend comment}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var comment|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
            <table class="order-details">
                <tr>
                    <td class="address-details">
                        <h3>{{trans "Billing Info"}}</h3>
                        <p>{{var formattedBillingAddress|raw}}</p>
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="address-details">
                        <h3>{{trans "Shipping Info"}}</h3>
                        <p>{{var formattedShippingAddress|raw}}</p>
                    </td>
                    {{/depend}}
                </tr>
                <tr>
                    <td class="method-info">
                        <h3>{{trans "Payment Method"}}</h3>
                        {{var payment_html|raw}}
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="method-info">
                        <h3>{{trans "Shipping Method"}}</h3>
                        <p>{{var order.getShippingDescription()}}</p>
                    </td>
                    {{/depend}}
                </tr>
            </table>
            {{layout handle="sales_email_order_creditmemo_items" creditmemo=$creditmemo order=$order}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}',
            'template_styles' => null,
            'template_type' => 2,
            'template_subject' => '{{trans "Refund for your %store_name order" store_name=$store.getFrontendName()}}',
            'template_sender_name' => null,
            'template_sender_email' => null,
            'added_at' => '2018-09-06 14:56:59',
            'modified_at' => '2021-02-01 06:17:49',
            'orig_template_code' => 'sales_email_creditmemo_template',
            'orig_template_variables' => '{"var formattedBillingAddress|raw":"Billing Address","var comment":"Credit Memo Comment","var creditmemo.increment_id":"Credit Memo Id","layout handle=\"sales_email_order_creditmemo_items\" creditmemo=$creditmemo order=$order":"Credit Memo Items Grid","var this.getUrl($store, \'customer/account/\')":"Customer Account URL","var order.getCustomerName()":"Customer Name","var order.increment_id":"Order Id","var payment_html|raw":"Payment Details","var formattedShippingAddress|raw":"Shipping Address","var order.getShippingDescription()":"Shipping Description","var order.shipping_description":"Shipping Description"}',
            'is_legacy' => 1,
        ],
        [
            'template_code' => 'Coker New Credit Memo Refund for Guest Coker_WV',
            'template_text' => '{{template config_path="design/email/header_template"}}

<table>
    <tr class="email-intro">
        <td>
            <p class="greeting">{{trans "%name," name=$billing.getName()}}</p>
            <p>
                {{trans "Thank you for your order from %store_name. A new Refund has been created for this order." store_name=$store.getFrontendName()}}
                {{trans \'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>\' store_email=$store_email |raw}}{{depend store_phone}} {{trans \'or call us at <a href="tel:%store_phone">%store_phone</a>\' store_phone=$store_phone |raw}}{{/depend}}.
                {{depend store_hours}}
                    {{trans \'Our hours are <span class="no-link">%store_hours</span>.\' store_hours=$store_hours |raw}}
                {{/depend}}
            </p>
        </td>
    </tr>
    <tr class="email-summary">
        <td>
            <h1>{{trans "Your Refund for Order #%order_id" order_id=$order.increment_id}}</h1>
        </td>
    </tr>
    <tr class="email-information">
        <td>
            {{depend comment}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var comment|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
            <table class="order-details">
                <tr>
                    <td class="address-details">
                        <h3>{{trans "Billing Info"}}</h3>
                        <p>{{var formattedBillingAddress|raw}}</p>
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="address-details">
                        <h3>{{trans "Shipping Info"}}</h3>
                        <p>{{var formattedShippingAddress|raw}}</p>
                    </td>
                    {{/depend}}
                </tr>
                <tr>
                    <td class="method-info">
                        <h3>{{trans "Payment Method"}}</h3>
                        {{var payment_html|raw}}
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="method-info">
                        <h3>{{trans "Shipping Method"}}</h3>
                        <p>{{var order.getShippingDescription()}}</p>
                    </td>
                    {{/depend}}
                </tr>
            </table>
            {{layout handle="sales_email_order_creditmemo_items" creditmemo=$creditmemo order=$order}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}',
            'template_styles' => null,
            'template_type' => 2,
            'template_subject' => '{{trans "Refund for your %store_name order" store_name=$store.getFrontendName()}}',
            'template_sender_name' => null,
            'template_sender_email' => null,
            'added_at' => '2018-09-06 14:59:18',
            'modified_at' => '2021-02-01 06:17:49',
            'orig_template_code' => 'sales_email_creditmemo_guest_template',
            'orig_template_variables' => '{"var formattedBillingAddress|raw":"Billing Address","var comment":"Credit Memo Comment","var creditmemo.increment_id":"Credit Memo Id","layout handle=\"sales_email_order_creditmemo_items\" creditmemo=$creditmemo order=$order":"Credit Memo Items Grid","var billing.getName()":"Guest Customer Name (Billing)","var order.increment_id":"Order Id","var payment_html|raw":"Payment Details","var formattedShippingAddress|raw":"Shipping Address","var order.getShippingDescription()":"Shipping Description","var order.shipping_description":"Shipping Description"}',
            'is_legacy' => 1,
        ],
        [
            'template_code' => 'New RMA Coker_WV',
            'template_text' => '{{template config_path="design/email/header_template"}}

<p class="greeting">{{trans "%name," name=$order.getCustomerName()}}</p>
<p>
    {{trans "We received your return request. You will be notified when your request is reviewed."}}
    {{trans
        \'If you have any questions about your return, please contact us at <a href="mailto:%support_email">%support_email</a>.\'

        support_email=$store.getConfig(\'trans_email/ident_support/email\')
    |raw}}
</p>

<h1>
    {{trans
        "Your Return #%increment_id - %status_label"

        increment_id=$rma.getIncrementId()
        status_label=$rma.getStatusLabel().format(\'html\')
    }}
</h1>
<p>{{trans "Placed on %created_at" created_at=$rma.getCreatedAtFormated(1)}}</p>

<table class="order-details" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td class="address-details">
            <h3>{{trans "Shipping Address"}}</h3>
            <p>{{var formattedShippingAddress|raw}}</p>
        </td>
        <td class="address-details">
            <h3>{{trans "Return Address"}}</h3>
            <p>{{var return_address|raw}}</p>
        </td>
    </tr>
</table>
<br/>

{{layout handle="magento_rma_email_rma_items" collection=$item_collection}}

{{template config_path="design/email/footer_template"}}',
            'template_styles' => null,
            'template_type' => 2,
            'template_subject' => '{{trans "%store_name : New Return #%increment_id" store_name=$store.getFrontendName() increment_id=$rma.getIncrementId()}}',
            'template_sender_name' => null,
            'template_sender_email' => null,
            'added_at' => '2018-09-06 15:00:38',
            'modified_at' => '2021-02-01 06:17:49',
            'orig_template_code' => 'sales_email_magento_rma_template',
            'orig_template_variables' => '{"store url=\"customer\/account\/\"":"Customer Account URL","template config_path=\"design\/email\/footer_template\"":"Email Footer Template","template config_path=\"design\/email\/header_template\"":"Email Header Template","var logo_alt":"Email Logo Image Alt","var logo_url":"Email Logo Image URL","var item_collection":"Items Collection","var return_address|raw":"Return Address","var rma.getCreatedAtFormated(1)":"Return Created At (datetime)","var rma.getIncrementId()":"Return Id","layout handle=\"magento_rma_email_rma_items\" collection=$item_collection":"Return Items Collection","var rma.getStatusLabel().format(\'html\')":"Return Status","var formattedShippingAddress|raw":"Shipping Address","var store.getFrontendName()":"Store Name","store url=\"\"":"Store URL"}',
            'is_legacy' => 1,
        ],
        [
            'template_code' => 'New RMA for Guest Coker_WV',
            'template_text' => '<!--@subject {{trans "%store_name: New Return # %increment_id"
    store_name=$store.getFrontendName() increment_id=$rma.getIncrementId()}} @-->


{{template config_path="design/email/header_template"}}

<p class="greeting">{{trans "%name," name=$order.getCustomerName()}}</p>
<p>
    {{trans "We received your return request. You will be notified when your request is reviewed."}}
    {{trans
    \'If you have any questions about your return, please contact us at <a href="mailto:%support_email">%support_email</a>.\'

    support_email=$store.getConfig(\'trans_email/ident_support/email\')
    |raw}}
</p>

<h1>
    {{trans
    "Your Return #%increment_id - %status_label"

    increment_id=$rma.getIncrementId()
    status_label=$rma.getStatusLabel().format(\'html\')
    }}
</h1>
<p>{{trans "Placed on %created_at" created_at=$rma.getCreatedAtFormated(1)}}</p>

<table class="order-details" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td class="address-details">
            <h3>{{trans "Shipping Address"}}</h3>
            <p>{{var formattedShippingAddress|raw}}</p>
        </td>
        <td class="address-details">
            <h3>{{trans "Return Address"}}</h3>
            <p>{{var return_address|raw}}</p>
        </td>
    </tr>
</table>
<br/>

{{layout handle="magento_rma_email_rma_items" collection=$item_collection}}

{{template config_path="design/email/footer_template"}}',
            'template_styles' => null,
            'template_type' => 2,
            'template_subject' => 'We received your return request!',
            'template_sender_name' => null,
            'template_sender_email' => null,
            'added_at' => '2018-09-06 15:01:26',
            'modified_at' => '2021-02-01 06:17:49',
            'orig_template_code' => 'sales_email_magento_rma_guest_template',
            'orig_template_variables' => '{"store url=\"customer\/account\/\"":"Customer Account URL","template config_path=\"design\/email\/footer_template\"":"Email Footer Template","template config_path=\"design\/email\/header_template\"":"Email Header Template","var logo_alt":"Email Logo Image Alt","var logo_url":"Email Logo Image URL","var item_collection":"Items Collection","var return_address|raw":"Return Address","var rma.getCreatedAtFormated(1)":"Return Created At (datetime)","var rma.getIncrementId()":"Return Id","layout handle=\"magento_rma_email_rma_items\" collection=$item_collection":"Return Items Collection","var rma.getStatusLabel().format(\'html\')":"Return Status","var formattedShippingAddress|raw":"Shipping Address","var store.getFrontendName()":"Store Name","store url=\"\"":"Store URL"}',
            'is_legacy' => 1,
        ],
        [
            'template_code' => 'New Shipment Coker_WV',
            'template_text' => '{{template config_path="design/email/header_template"}}

<table>
    <tr class="email-intro">
        <td>
            <p class="greeting">{{trans "%name," name=$order.getCustomerName()}}</p>
            <p>
                {{trans "Thank you for your order from %store_name." store_name=$store.getFrontendName()}}
                {{trans \'You can check the status of your order by <a href="%account_url">logging into your account</a>.\' account_url=$this.getUrl($store,\'customer/account/\',[_nosid:1]) |raw}}
                {{trans \'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>\' store_email=$store_email |raw}}{{depend store_phone}} {{trans \'or call us at <a href="tel:%store_phone">%store_phone</a>\' store_phone=$store_phone |raw}}{{/depend}}.
                {{depend store_hours}}
                    {{trans \'Our hours are <span class="no-link">%store_hours</span>.\' store_hours=$store_hours |raw}}
                {{/depend}}
            </p>
        </td>
    </tr>
    <tr class="email-summary">
        <td>
            <p>{{trans "Your shipping confirmation is below. Thank you again for your business."}}</p>

            <h1>{{trans "Your Shipment #%shipment_id for Order #%order_id" shipment_id=$shipment.increment_id order_id=$order.increment_id}}</h1>
        </td>
    </tr>
    <tr class="email-information">
        <td>
            {{depend comment}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var comment|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
            {{block class=\'Magento\\Framework\\View\\Element\\Template\' area=\'frontend\' template=\'Magento_Sales::email/shipment/track.phtml\' shipment=$shipment order=$order}}
            <table class="order-details">
                <tr>
                    <td class="address-details">
                        <h3>{{trans "Billing Info"}}</h3>
                        <p>{{var formattedBillingAddress|raw}}</p>
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="address-details">
                        <h3>{{trans "Shipping Info"}}</h3>
                        <p>{{var formattedShippingAddress|raw}}</p>
                    </td>
                    {{/depend}}
                </tr>
                <tr>
                    <td class="method-info">
                        <h3>{{trans "Payment Method"}}</h3>
                        {{var payment_html|raw}}
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="method-info">
                        <h3>{{trans "Shipping Method"}}</h3>
                        <p>{{var order.getShippingDescription()}}</p>
                    </td>
                    {{/depend}}
                </tr>
            </table>
            {{layout handle="sales_email_order_shipment_items" shipment=$shipment order=$order}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}',
            'template_styles' => null,
            'template_type' => 2,
            'template_subject' => '{{trans "Your %store_name order has shipped" store_name=$store.getFrontendName()}}',
            'template_sender_name' => null,
            'template_sender_email' => null,
            'added_at' => '2018-09-06 15:03:57',
            'modified_at' => '2021-02-01 06:17:49',
            'orig_template_code' => 'sales_email_shipment_template',
            'orig_template_variables' => '{"var formattedBillingAddress|raw":"Billing Address","var this.getUrl($store, \'customer/account/\')":"Customer Account URL","var order.getCustomerName()":"Customer Name","var order.increment_id":"Order Id","var payment_html|raw":"Payment Details","var comment":"Shipment Comment","var shipment.increment_id":"Shipment Id","layout handle=\"sales_email_order_shipment_items\" shipment=$shipment order=$order":"Shipment Items Grid","block class=\'Magento\\\\Framework\\\\View\\\\Element\\\\Template\' area=\'frontend\' template=\'Magento_Sales::email\/shipment\/track.phtml\' shipment=$shipment order=$order":"Shipment Track Details","var formattedShippingAddress|raw":"Shipping Address","var order.shipping_description":"Shipping Description","var order.getShippingDescription()":"Shipping Description"}',
            'is_legacy' => 1,
        ],
        [
            'template_code' => 'Coker New Order Coker_WV',
            'template_text' => '{{template config_path="design/email/header_template"}}

<table>
    <tr class="email-intro">
        <td>
            <p class="greeting">{{trans "%customer_name," customer_name=$order.getCustomerName()}}</p>
            <p>
                {{trans "Thank you for your order from %store_name." store_name=$store.getFrontendName()}}
                {{trans "Once your package ships we will send you a tracking number."}}
                {{trans \'You can check the status of your order by <a href="%account_url">logging into your account</a>.\' account_url=$this.getUrl($store,\'customer/account/\',[_nosid:1]) |raw}}
            </p>
            <p>
                {{trans \'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>\' store_email=$store_email |raw}}{{depend store_phone}} {{trans \'or call us at <a href="tel:%store_phone">%store_phone</a>\' store_phone=$store_phone |raw}}{{/depend}}.
                {{depend store_hours}}
                    {{trans \'Our hours are <span class="no-link">%store_hours</span>.\' store_hours=$store_hours |raw}}
                {{/depend}}
            </p>
        </td>
    </tr>
    <tr class="email-summary">
        <td>
            <h1>{{trans \'Your Order <span class="no-link">#%increment_id</span>\' increment_id=$order.increment_id |raw}}</h1>
            <p>{{trans \'Placed on <span class="no-link">%created_at</span>\' created_at=$order.getCreatedAtFormatted(2) |raw}}</p>
        </td>
    </tr>
    <tr class="email-information">
        <td>
            {{depend order.getEmailCustomerNote()}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var order.getEmailCustomerNote()|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
            <table class="order-details">
            <p>{{var order.orderstatus}}</p>
                <tr>
                    <td class="address-details">
                        <h3>{{trans "Billing Info"}}</h3>
                        <p>{{var formattedBillingAddress|raw}}</p>
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="address-details">
                        <h3>{{trans "Shipping Info"}}</h3>
                        <p>{{var formattedShippingAddress|raw}}</p>
                    </td>
                    {{/depend}}
                </tr>
                <tr>
                    <td class="method-info">
                        <h3>{{trans "Payment Method"}}</h3>
                        {{var payment_html|raw}}
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="method-info">
                        <h3>{{trans "Shipping Method"}}</h3>
                        <p>{{var order.getShippingDescription()}}</p>
                        {{if shipping_msg}}
                        <p>{{var shipping_msg}}</p>
                        {{/if}}
                    </td>
                    {{/depend}}
                </tr>
            </table>
            {{layout handle="sales_email_order_items" order=$order area="frontend"}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}',
            'template_styles' => null,
            'template_type' => 2,
            'template_subject' => '{{trans "Your %store_name order confirmation" store_name=$store.getFrontendName()}}',
            'template_sender_name' => null,
            'template_sender_email' => null,
            'added_at' => '2019-03-03 09:14:30',
            'modified_at' => '2021-02-01 06:17:49',
            'orig_template_code' => 'sales_email_order_template',
            'orig_template_variables' => '{"var formattedBillingAddress|raw":"Billing Address","var order.getEmailCustomerNote()":"Email Order Note","var order.increment_id":"Order Id","layout handle=\"sales_email_order_items\" order=$order area=\"frontend\"":"Order Items Grid","var payment_html|raw":"Payment Details","var formattedShippingAddress|raw":"Shipping Address","var order.getShippingDescription()":"Shipping Description","var shipping_msg":"Shipping message"}',
            'is_legacy' => 1,
        ],
        [
            'template_code' => 'Coker New Order For Guest Coker_WV',
            'template_text' => '{{template config_path="design/email/header_template"}}

<table>
    <tr class="email-intro">
        <td>
            <p class="greeting">{{trans "%name," name=$order.getBillingAddress().getName()}}</p>
            <p>
                {{trans "Thank you for your order from %store_name." store_name=$store.getFrontendName()}}
                {{trans "Once your package ships we will send an email with a link to track your order."}}
                {{trans \'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>\' store_email=$store_email |raw}}{{depend store_phone}} {{trans \'or call us at <a href="tel:%store_phone">%store_phone</a>\' store_phone=$store_phone |raw}}{{/depend}}.
                {{depend store_hours}}
                    {{trans \'Our hours are <span class="no-link">%store_hours</span>.\' store_hours=$store_hours |raw}}
                {{/depend}}
            </p>
        </td>
    </tr>
    <tr class="email-summary">
        <td>
            <h1>{{trans \'Your Order <span class="no-link">#%increment_id</span>\' increment_id=$order.increment_id |raw}}</h1>
            <p>{{trans \'Placed on <span class="no-link">%created_at</span>\' created_at=$order.getCreatedAtFormatted(2) |raw}}</p>
        </td>
    </tr>
    <tr class="email-information">
        <td>
            {{depend order.getEmailCustomerNote()}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var order.getEmailCustomerNote()|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
            <table class="order-details">
            <p>{{var order.orderstatus}}</p>
                <tr>
                    <td class="address-details">
                        <h3>{{trans "Billing Info"}}</h3>
                        <p>{{var formattedBillingAddress|raw}}</p>
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="address-details">
                        <h3>{{trans "Shipping Info"}}</h3>
                        <p>{{var formattedShippingAddress|raw}}</p>
                    </td>
                    {{/depend}}
                </tr>
                <tr>
                    <td class="method-info">
                        <h3>{{trans "Payment Method"}}</h3>
                        {{var payment_html|raw}}
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="method-info">
                        <h3>{{trans "Shipping Method"}}</h3>
                        <p>{{var order.getShippingDescription()}}</p>
                        {{if shipping_msg}}
                        <p>{{var shipping_msg}}</p>
                        {{/if}}
                    </td>
                    {{/depend}}
                </tr>
            </table>
            {{layout handle="sales_email_order_items" order=$order}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}',
            'template_styles' => null,
            'template_type' => 2,
            'template_subject' => '{{trans "Your %store_name order confirmation" store_name=$store.getFrontendName()}}',
            'template_sender_name' => null,
            'template_sender_email' => null,
            'added_at' => '2019-03-03 09:15:03',
            'modified_at' => '2021-02-01 06:17:49',
            'orig_template_code' => 'sales_email_order_guest_template',
            'orig_template_variables' => '{"var formattedBillingAddress|raw":"Billing Address","var order.getEmailCustomerNote()":"Email Order Note","var order.getBillingAddress().getName()":"Guest Customer Name","var order.getCreatedAtFormatted(2)":"Order Created At (datetime)","var order.increment_id":"Order Id","layout handle=\"sales_email_order_items\" order=$order":"Order Items Grid","var payment_html|raw":"Payment Details","var formattedShippingAddress|raw":"Shipping Address","var order.getShippingDescription()":"Shipping Description","var shipping_msg":"Shipping message"}',
            'is_legacy' => 1,
        ],
        [
            'template_code' => 'Order Status Update Coker_WV',
            'template_text' => '{{template config_path="design/email/header_template"}}

<table>
    <tr class="email-intro">
        <td>
            <p class="greeting">{{trans "%name," name=$order.getCustomerName()}}</p>
            <p>
                {{trans
                    "Your order #%increment_id has been updated with a status of <strong>%order_status</strong>."

                    increment_id=$order.increment_id
                    order_status=$order.getStatusLabel()
                |raw}}
            </p>
            <p>{{trans \'You can check the status of your order by <a href="%account_url">logging into your account</a>.\' account_url=$this.getUrl($store,\'customer/account/\',[_nosid:1]) |raw}}</p>
            <p>
                {{trans \'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>\' store_email=$store_email |raw}}{{depend store_phone}} {{trans \'or call us at <a href="tel:%store_phone">%store_phone</a>\' store_phone=$store_phone |raw}}{{/depend}}.
                {{depend store_hours}}
                    {{trans \'Our hours are <span class="no-link">%store_hours</span>.\' store_hours=$store_hours |raw}}
                {{/depend}}
            </p>
        </td>
    </tr>
    <tr class="email-information">
        <td>
            {{depend comment}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var comment|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}',
            'template_styles' => null,
            'template_type' => 2,
            'template_subject' => '{{trans "Update to your %store_name order" store_name=$store.getFrontendName()}}',
            'template_sender_name' => null,
            'template_sender_email' => null,
            'added_at' => '2019-03-21 14:45:16',
            'modified_at' => '2021-02-01 06:17:49',
            'orig_template_code' => 'sales_email_order_comment_template',
            'orig_template_variables' => '{"var this.getUrl($store, \'customer/account/\')":"Customer Account URL","var order.getCustomerName()":"Customer Name","var comment":"Order Comment","var order.increment_id":"Order Id","var order.getStatusLabel()":"Order Status"}',
            'is_legacy' => 1,
        ],
        [
            'template_code' => 'New Account Without Password Coker_WV',
            'template_text' => '{{template config_path="design/email/header_template"}}

<p class="greeting">{{trans "%name," name=$customer.name}}</p>
<p>{{trans "Welcome to %store_name." store_name=$store.getFrontendName()}}</p>
<p>
    {{trans
        \'To sign in to our site and set a password, <a href="%create_password_url">click here</a>:\'

        create_password_url="$this.getUrl($store,\'customer/account/createPassword/\',[_query:[id:$customer.id,token:$customer.rp_token],_nosid:1])"
    |raw}}
</p>
<ul>
    <li><strong>{{trans "Email:"}}</strong> {{var customer.email}}</li>
</ul>
<p>{{trans "When you sign in to your account, you will be able to:"}}</p>
<ul>
    <li>• {{trans "Proceed through checkout faster"}}</li>
    <li>• {{trans "Check the status of orders"}}</li>
    <li>• {{trans "View past orders"}}</li>
    <li>• {{trans "Store alternative addresses (for shipping to multiple family members and friends)"}}</li>
</ul>

{{template config_path="design/email/footer_template"}}',
            'template_styles' => null,
            'template_type' => 2,
            'template_subject' => '{{trans "Welcome to %store_name" store_name=$store.getFrontendName()}}',
            'template_sender_name' => null,
            'template_sender_email' => null,
            'added_at' => '2019-05-22 18:46:00',
            'modified_at' => '2022-01-19 21:40:33',
            'orig_template_code' => 'customer_create_account_email_no_password_template',
            'orig_template_variables' => '{ "var this.getUrl($store, \'customer/account/\')":"Customer Account URL", "var customer.email":"Customer Email", "var customer.name":"Customer Name" }',
            'is_legacy' => 1,
        ],
        [
            'template_code' => 'New Pickup Order Coker_WV',
            'template_text' => '<!--@subject {{trans "Your %store_name order confirmation" store_name=$store.getFrontendName()}} @-->
<!--@vars {
"var order.getCustomerName()":"Customer Name",
"var store.getFrontendName()":"Customer Name",
"var is_pickup_order":"Check if order is a pickup order",
"var $this.getUrl($store,\'customer/account/\',[_nosid:1]) |raw":"Gets the link to the Customer Account",
"var store_email |raw":"Defined Email Address for this type of mails",
"var store_phone |raw":"Defined Phone Number in the Store Information",
"var store_hours |raw":"Defined Opening Hours Phone Number in the Store Information",
"var order.increment_id |raw":"Order Id",
"var order.getCreatedAtFormatted(2) |raw":"Get the date of order creation",
"var order.getEmailCustomerNote()":"Email Order Note",
"var formattedBillingAddress|raw":"Billing Address",
"var order.getIsNotVirtual()":"Check if shipment exists",
"var pickupAddress|raw":"Pickup Location Address",
"var formattedShippingAddress|raw":"Customer Shipping Address",
"var payment_html|raw":"Payment Details",
"var order.getShippingDescription()":"Shipping Description",
"var shipping_msg":"Shipping message",
"layout handle=\"sales_email_order_items\" order=$order area=\"frontend\"":"Order Items Grid"
} @-->

{{template config_path="design/email/header_template"}}

<table>
    <tr class="email-intro">
        <td>
            <p class="greeting">{{trans "%customer_name," customer_name=$order.getCustomerName()}}</p>
            <p>
                {{trans "Thank you for your order from %store_name." store_name=$store.getFrontendName()}}
                {{if is_pickup_order}}
                    {{trans "We will send you a notification once your items are ready for pickup."}}
                {{else}}
                    {{trans "Once your package ships we will send you a tracking number."}}
                {{/if}}
                {{trans \'You can check the status of your order by <a href="%account_url">logging into your account</a>.\' account_url=$this.getUrl($store,\'customer/account/\',[_nosid:1]) |raw}}
            </p>
            <p>
                {{trans \'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>\' store_email=$store_email |raw}}{{depend store_phone}} {{trans \' or call us at <a href="tel:%store_phone">%store_phone</a>\' store_phone=$store_phone |raw}}{{/depend}}.
                {{depend store_hours}}
                {{trans \'Our hours are <span class="no-link">%store_hours</span>.\' store_hours=$store_hours |raw}}
                {{/depend}}
            </p>
        </td>
    </tr>
    <tr class="email-summary">
        <td>
            <h1>{{trans \'Your Order <span class="no-link">#%increment_id</span>\' increment_id=$order.increment_id |raw}}</h1>
            <p>{{trans \'Placed on <span class="no-link">%created_at</span>\' created_at=$order.getCreatedAtFormatted(2) |raw}}</p>
        </td>
    </tr>
    <tr class="email-information">
        <td>
            {{depend order.getEmailCustomerNote()}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var order.getEmailCustomerNote()|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
            <table class="order-details">
                <tr>
                    <td class="address-details">
                        <h3>{{trans "Billing Info"}}</h3>
                        <p>{{var formattedBillingAddress|raw}}</p>
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="address-details">
                        <h3>{{trans "Shipping Info"}}</h3>
                        {{if is_pickup_order}}
                            <h4>{{trans "Pickup Location"}}</h4>
                            <p>{{var pickupAddress|raw}}</p>
                            <h4>{{trans "Recipient Address"}}</h4>
                        {{/if}}
                        <p>{{var formattedShippingAddress|raw}}</p>
                    </td>
                    {{/depend}}
                </tr>
                <tr>
                    <td class="method-info">
                        <h3>{{trans "Payment Method"}}</h3>
                        {{var payment_html|raw}}
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="method-info">
                        <h3>{{trans "Shipping Method"}}</h3>
                        <p>{{var order.getShippingDescription()}}</p>
                        {{if shipping_msg}}
                        <p>{{var shipping_msg}}</p>
                        {{/if}}
                    </td>
                    {{/depend}}
                </tr>
            </table>
            {{layout handle="sales_email_order_items" order=$order area="frontend"}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}',
            'template_styles' => null,
            'template_type' => 2,
            'template_subject' => '{{trans "Your %store_name order confirmation" store_name=$store.getFrontendName()}}',
            'template_sender_name' => null,
            'template_sender_email' => null,
            'added_at' => '2020-02-18 02:17:04',
            'modified_at' => '2021-02-01 06:17:49',
            'orig_template_code' => 'sales_email_order_template',
            'orig_template_variables' => '{"var formattedBillingAddress|raw":"Billing Address","var order.getEmailCustomerNote()":"Email Order Note","var order.increment_id":"Order Id","layout handle=\"sales_email_order_items\" order=$order area=\"frontend\"":"Order Items Grid","var payment_html|raw":"Payment Details","var formattedShippingAddress|raw":"Shipping Address","var order.getShippingDescription()":"Shipping Description","var shipping_msg":"Shipping message"}',
            'is_legacy' => 1,
        ],
        [
            'template_code' => 'New Pickup Order For Guest Coker_WV',
            'template_text' => '<!--@subject {{trans "Your %store_name order confirmation" store_name=$store.getFrontendName()}} @-->
<!--@vars {
"var order.getBillingAddress().getName()":"Guest Customer Name",
"var store.getFrontendName()":"Customer Name",
"var is_pickup_order":"Check if order is a pickup order",
"var store_email |raw":"Defined Email Address for this type of mails",
"var store_phone |raw":"Defined Phone Number in the Store Information",
"var store_hours |raw":"Defined Opening Hours Phone Number in the Store Information",
"var order.increment_id |raw":"Order Id",
"var order.getCreatedAtFormatted(2) |raw":"Get the date of order creation",
"var order.getEmailCustomerNote()":"Email Order Note",
"var formattedBillingAddress|raw":"Billing Address",
"var order.getIsNotVirtual()":"Check if shipment exists",
"var pickupAddress|raw":"Pickup Location Address",
"var formattedShippingAddress|raw":"Customer Shipping Address",
"var payment_html|raw":"Payment Details",
"var order.getShippingDescription()":"Shipping Description",
"var shipping_msg":"Shipping message",
"layout handle=\"sales_email_order_items\" order=$order area=\"frontend\"":"Order Items Grid"
} @-->

{{template config_path="design/email/header_template"}}

<table>
    <tr class="email-intro">
        <td>
            <p class="greeting">{{trans "%name," name=$order.getBillingAddress().getName()}}</p>
            <p>
                {{trans "Thank you for your order from %store_name." store_name=$store.getFrontendName()}}
                {{if is_pickup_order}}
                    {{trans "We will send you a notification once your items are ready for pickup."}}
                {{else}}
                    {{trans "Once your package ships we will send an email with a link to track your order."}}
                {{/if}}
                {{trans \'If you have questions about your order, you can email us at <a href="mailto:%store_email">%store_email</a>\' store_email=$store_email |raw}}{{depend store_phone}} {{trans \' or call us at <a href="tel:%store_phone">%store_phone</a>\' store_phone=$store_phone |raw}}{{/depend}}.
                {{depend store_hours}}
                    {{trans \'Our hours are <span class="no-link">%store_hours</span>.\' store_hours=$store_hours |raw}}
                {{/depend}}
            </p>
        </td>
    </tr>
    <tr class="email-summary">
        <td>
            <h1>{{trans \'Your Order <span class="no-link">#%increment_id</span>\' increment_id=$order.increment_id |raw}}</h1>
            <p>{{trans \'Placed on <span class="no-link">%created_at</span>\' created_at=$order.getCreatedAtFormatted(2) |raw}}</p>
        </td>
    </tr>
    <tr class="email-information">
        <td>
            {{depend order.getEmailCustomerNote()}}
            <table class="message-info">
                <tr>
                    <td>
                        {{var order.getEmailCustomerNote()|escape|nl2br}}
                    </td>
                </tr>
            </table>
            {{/depend}}
            <table class="order-details">
                <tr>
                    <td class="address-details">
                        <h3>{{trans "Billing Info"}}</h3>
                        <p>{{var formattedBillingAddress|raw}}</p>
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="address-details">
                        <h3>{{trans "Shipping Info"}}</h3>
                        {{if is_pickup_order}}
                            <h4>{{trans "Pickup Location"}}</h4>
                            <p>{{var pickupAddress|raw}}</p>
                            <h4>{{trans "Recipient Address"}}</h4>
                        {{/if}}
                        <p>{{var formattedShippingAddress|raw}}</p>
                    </td>
                    {{/depend}}
                </tr>
                <tr>
                    <td class="method-info">
                        <h3>{{trans "Payment Method"}}</h3>
                        {{var payment_html|raw}}
                    </td>
                    {{depend order.getIsNotVirtual()}}
                    <td class="method-info">
                        <h3>{{trans "Shipping Method"}}</h3>
                        <p>{{var order.getShippingDescription()}}</p>
                        {{if shipping_msg}}
                        <p>{{var shipping_msg}}</p>
                        {{/if}}
                    </td>
                    {{/depend}}
                </tr>
            </table>
            {{layout handle="sales_email_order_items" order=$order}}
        </td>
    </tr>
</table>

{{template config_path="design/email/footer_template"}}',
            'template_styles' => null,
            'template_type' => 2,
            'template_subject' => '{{trans "Your %store_name order confirmation" store_name=$store.getFrontendName()}}',
            'template_sender_name' => null,
            'template_sender_email' => null,
            'added_at' => '2020-02-18 02:17:05',
            'modified_at' => '2021-02-01 06:17:49',
            'orig_template_code' => 'sales_email_order_guest_template',
            'orig_template_variables' => '{"var formattedBillingAddress|raw":"Billing Address","var order.getEmailCustomerNote()":"Email Order Note","var order.getBillingAddress().getName()":"Guest Customer Name","var order.getCreatedAtFormatted(2)":"Order Created At (datetime)","var order.increment_id":"Order Id","layout handle=\"sales_email_order_items\" order=$order":"Order Items Grid","var payment_html|raw":"Payment Details","var formattedShippingAddress|raw":"Shipping Address","var order.getShippingDescription()":"Shipping Description","var shipping_msg":"Shipping message"}',
            'is_legacy' => 1,
        ],
        [
            'template_code' => 'Abandoned Cart Setup Coker_WV',
            'template_text' => '{{template config_path="design/email/header_template"}}

<table>
    <tr class="email-intro">
        <td>

            <p>We noticed you left some products in your cart. If you have a question about your order, in regard to the product, pricing or shipping quote, please give us a call! </p>
<P>We’d love the opportunity to earn your business!</p>
<p>Please Call <strong>866-513-2759</strong></p>
            </p>
        </td>
    </tr>
    </table>

{{template config_path="design/email/footer_template"}}',
            'template_styles' => null,
            'template_type' => 2,
            'template_subject' => 'Need Help or Have Questions On the Products in your Cart?',
            'template_sender_name' => null,
            'template_sender_email' => null,
            'added_at' => '2020-03-13 08:40:37',
            'modified_at' => '2022-01-19 21:35:40',
            'orig_template_code' => null,
            'orig_template_variables' => null,
            'is_legacy' => 1,
        ],
        [
            'template_code' => 'cokertire export report of all abandoned carts Coker_WV',
            'template_text' => '{{template config_path="design/email/header_template"}}
{{layout handle="email_cart_list" items=$items area="frontend"}}',
            'template_styles' => null,
            'template_type' => 2,
            'template_subject' => 'cokertire export report of all abandoned carts',
            'template_sender_name' => null,
            'template_sender_email' => null,
            'added_at' => '2020-05-18 05:21:06',
            'modified_at' => '2021-02-01 06:17:49',
            'orig_template_code' => null,
            'orig_template_variables' => null,
            'is_legacy' => 1,
        ],
    ];

    public function __construct(
        private readonly TemplateFactory $templateFactory,
        private readonly AppState $appState,
    )
    {}

    public function apply(): void
    {
        $this->appState->emulateAreaCode(
            AppArea::AREA_ADMINHTML,
            function () {
                foreach ($this->_templatesData as $templateData) {
                    $this->_createEmailTemplate($templateData);
                }
            }
        );
    }

    /**
     * @param array $templateData
     * @return void
     * @throws \Exception
     */
    private function _createEmailTemplate(array $templateData): void
    {
        $this->templateFactory
            ->create()
            ->addData($templateData)
            ->save();
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
