

define([
        'jquery',
        'underscore',
        'Magento_Ui/js/modal/modal'
    ],
    function($, _, modal) {
        "use strict";

        $.widget('faq.helpfulness', {
            _create: function() {
                if (this.options.url) {
                    var url = this.options.url;
                    var articleId = $('#articleId').val();

                    $('#like').click(function() {
                        $.ajax({
                            url: url,
                            type: 'post',
                            dataType: 'json',
                            data: { like: "like", articleId: articleId },
                            showLoader: true,
                            success: function(data){
                                if (data['like']) {
                                    $('#aw-faq__helpfulness>span:first-child').removeClass('aw_bold');
                                    $('#aw__helpfulness-voting').addClass('aw-no-display');
                                    $('#aw__helpfulness-vote-yes').removeClass('aw-no-display');

                                    if (data['show_rate_after_voting']) {
                                        $('#ratingMessage').removeClass('aw-no-display');
                                    } else {
                                        $('#ratingMessage').addClass('aw-no-display');
                                    }
                                }
                            }
                        });
                    });
                    $('#dislike').click(function(){
                        $.ajax({
                            url: url,
                            type: 'post',
                            dataType: 'json',
                            data: { dislike: "dislike", articleId: articleId },
                            showLoader:true,
                            success: function(data){
                                if (data['dislike']) {
                                    $('#aw-faq__helpfulness >span:first-child').removeClass('aw_bold');
                                    $('#aw__helpfulness-voting, #ratingMessage').addClass('aw-no-display');
                                    $('#aw__helpfulness-vote-no').removeClass('aw-no-display');

                                    if (data['show_rate_after_voting']) {
                                        $('#ratingMessage').removeClass('aw-no-display');
                                    } else {
                                        $('#ratingMessage').addClass('aw-no-display');
                                    }
                                }
                            }
                        });
                    });
                }
            }
        });

        return $.faq.helpfulness;
    }
);
