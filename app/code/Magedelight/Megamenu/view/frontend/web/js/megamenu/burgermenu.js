/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
require(['jquery'], function($) {
    $(document).ready(function() {
        jQuery('.md-menu-close-btn').on('click',function(){
           jQuery('html').removeClass('nav-open');

        });

        /* Fix issue for Mobile when click on the menu it is close every time. */
        /*$('body').click(function(e) {
            if ($('html').hasClass('nav-open')){
                jQuery('html').removeClass('nav-open');
            }
        });*/
    });
});
