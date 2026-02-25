define([
  "jquery",
],
function($) {
  "use strict";
    jQuery(function(){
       var selected = jQuery('.update_specifications').find('option:selected');
       var sku = selected.data('id');

       jQuery('.update_specifications').change(function(){
           var selected = jQuery(this).find('option:selected');
           var sku = selected.data('id');
           var prodid = selected.data('product');
           jQuery('a[href="#specifications"]').tab('show');
           jQuery('a[href="#specifications"] span.grey_arrow').addClass('open_arrow');
           jQuery('a[href="#description"] span.grey_arrow').removeClass('open_arrow');

           //update the qty name to the correct product
           jQuery('#selectamount').attr("name", 'super_group['+prodid+']');

           jQuery('input[name="product"]').val(prodid);

           jQuery('#specifications').html('<img src="/media/ajaxcartpro/default/ajax-loader.gif" />');
           var imgbox = jQuery('.product-img-box').children().clone();
           jQuery('.product-img-box').html('<div class="center-ajax-loader"><div class="inner-ajax"><div class="row text-center"><img src="/media/ajaxcartpro/default/ajax-loader.gif" /></div></div></div>');
           jQuery.ajaxSetup({async:true});
           jQuery.ajax({
               url : url + '/groupedajax/ajax/getsimpleinfo',
               type: "POST",
               data: {sku: sku},
               success: function(result)
               {
                   // var parsed = JSON.parse(result);

                   jQuery('#specifications').html(result.specs);
                   jQuery('.block-content.related_items').html(result.related);

                   jQuery('.product-img-box').html(imgbox);
                   jQuery('.product-image').html(result.big_image);
                   jQuery('.small_images').html(result.small_images);
                  // jQuery.getScript(url + '/skin/frontend/default/coker_responsive/js/products/product_images.js');

               }
           });
       });
    });
});
