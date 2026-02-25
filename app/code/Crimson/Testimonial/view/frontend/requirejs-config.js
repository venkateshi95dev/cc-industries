var config = {
	map: {
		'*': {
			testimonial_fancybox: 'Crimson_Testimonial/js/jquery.fancybox.pack',
			rating : 'Crimson_Testimonial/js/rating',
			testimonial_rating : 'Crimson_Testimonial/js/testimonial_rating',
			testimonial_carousel : 'Crimson_Testimonial/js/testimonial-carousel',
			testimonialtowlcarousel: "Crimson_Testimonial/js/owl.carousel.min"
		}
	},
	shim: {
        'testimonial_carousel': {deps: ['jquery', 'Crimson_Testimonial/lib/owl.carousel/owl.carousel.min']},
        'Crimson_Testimonial/js/carouFredSel': {deps: ['jquery']},
        'Crimson_Testimonial/js/testimonial-carousel': {deps: ['jquery', 'Crimson_Testimonial/lib/owl.carousel/owl.carousel.min']},
        'testimonialtowlcarousel': {deps: ['jquery']}
    }
};
