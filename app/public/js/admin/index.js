/**
 * Main function, it's executed when the DOM is loaded
 */
$(function () {
    $("#login-btn").on("click", function () {
        $("#form_login").validate({
            rules: {
                username: {required: true, minlength: 3},
                pwd: {required: true, minlength: 8},
            },
            messages: {
                username: "Per favore inserisci il tuo username",
                pwd: "Per favore inserisci la tua password",
            },
            errorPlacement: function (error, element) {
                var placement = $(element).data('error');
                if (placement) {
                    $(placement).append(error);
                } else {
                    error.insertAfter(element);
                }
            }

        })
        if ($("#form_login").valid()) {
            // start captcha
            hcaptcha.execute();
        }
    })
})

/**
 * This function is called after the captcha verification
 */
function submitForm(){
    $("#form_login").trigger("submit");
}