require(
    [
        'jquery',
        'mage/mage'
    ],

    function ($) {
    var dataForm = $('#login-form');
    dataForm.mage('validation', {});

    $('.action-login').on('click', function () {
        if (dataForm.validation('isValid')) {
            var formData = new FormData();
            formData.append('username', $('.form-customer-login input[name="username"]').val());
            formData.append('password', $('.form-customer-login input[name="password"]').val());
            $.ajax({
                url: window.BASE_URL + 'loginregistermodal/customer/ajaxlogin',
                data: formData,
                processData: false,
                contentType: false,
                showLoader: true,
                type: 'POST',
                dataType: 'json',
                success: function (response) {

                if (!response.errors) {
                    if (response.redirectUrl) {
                        location.href = response.redirectUrl;
                    }
                } else {
                    $(' .form-customer-login .messages .message div').text(response.message);
                    $(' .form-customer-login .messages .message').show();
                    setTimeout(function () {
                        $('.form-customer-login .messages .message').hide();
                    }, 5000);
                }
            }
        });
            return false;
        }
    })
});