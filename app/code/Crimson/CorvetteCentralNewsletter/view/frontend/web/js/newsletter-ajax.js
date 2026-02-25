define([
    'jquery',
    'mage/url',
    'Magento_Ui/js/modal/alert'
], function($, urlBuilder, alert) {
    'use strict';
    return function(config, element) {
        var $form = $(element);

        $form.on('submit', function(e) {
            e.preventDefault();
            if (!$form.valid()) { return; }
            var $btn = $form.find('[type=submit]').prop('disabled', true);

            $.ajax({
                url: urlBuilder.build('newsletter/subscriber/ajax'),
                type: 'POST',
                dataType: 'json',
                showLoader: true,
                data: $form.serialize()
            }).done(function(res) {
                $('.newsletter-fields').find('.title').html('Success!');
                $('.newsletter-fields').find('.content').html('<strong>Thank you for joining our Corvette Family. We\'ll be sending you special offers and information that we hope you\'ll enjoy. If you think we\'re emailing too much? You can unsubscribe anytime.</strong>')
            }).fail(function() {
                alert({ title: 'Error', content: 'Server error. Try again later.' });
                $btn.prop('disabled', false);
            }).always(function() {
            });
        });
    };
});
