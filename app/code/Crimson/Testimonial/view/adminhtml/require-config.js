/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
var config = {
    map: {
        "*": {
            lofallOwlCarousel: "Crimson_Testimonial/lib/owl.carousel/owl.carousel.min",
            lofallBootstrap: "Crimson_Testimonial/lib/bootstrap/js/bootstrap.min",
            lofallColorbox: "Crimson_Testimonial/lib/colorbox/jquery.colorbox.min",
            lofallFancybox: "Crimson_Testimonial/lib/fancybox/jquery.fancybox.pack",
            lofallFancyboxMouseWheel: "Crimson_Testimonial/lib/fancybox/jquery.mousewheel-3.0.6.pack"
        }
    },
    shim: {
        'Crimson_Testimonial/lib/bootstrap/js/bootstrap.min': {
            'deps': ['jquery']
        },
        'Crimson_Testimonial/lib/bootstrap/js/bootstrap': {
            'deps': ['jquery']
        },
        'Crimson_Testimonial/lib/owl.carousel/owl.carousel': {
            'deps': ['jquery']
        },
        'Crimson_Testimonial/lib/owl.carousel/owl.carousel.min': {
            'deps': ['jquery']
        },
        'Crimson_Testimonial/lib/fancybox/jquery.fancybox': {
            'deps': ['jquery']
        },
        'Crimson_Testimonial/lib/fancybox/jquery.fancybox.pack': {
            'deps': ['jquery']
        },
        'Crimson_Testimonial/lib/colorbox/jquery.colorbox': {
            'deps': ['jquery']
        },
        'Crimson_Testimonial/lib/colorbox/jquery.colorbox.min': {
            'deps': ['jquery']
        },
        'Crimson_Testimonial/lib/fancybox/jquery.mousewheel-3.0.6.pack': {
            'deps': ['jquery']
        }
    }
};
