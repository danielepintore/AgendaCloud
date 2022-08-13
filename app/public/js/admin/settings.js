/**
 * Given a payment methodId, this function updates its status and then warns the user if the operation is successful or not
 * @param status
 * @param paymentMethodId
 */
function updatePaymentMethod(status, paymentMethodId) {
    $.get("/admin/api/payment/update_payment_method.php", {paymentMethodId: paymentMethodId, status: status})
        .done(function (data) {
            if (data.error) {
                $("#errorAlert").html("Non è stato possibile effettuare l'operazione, contatta l'assistenza");
                $("#errorAlert").removeClass('d-none');
            } else {
                $("#errorAlert").addClass('d-none');
            }
            getPaymentMethods();
        })
        .fail(function () {
            $("#errorAlert").html("Non è stato possibile effettuare l'operazione, contatta l'assistenza");
            $("#errorAlert").removeClass('d-none');
            getPaymentMethods();
        });
}

/**
 * Gets the lists of all available payment methods
 */
function getPaymentMethods() {
    $.get("/admin/api/payment/get_payment_methods.php")
        .done(function (data) {
            $('#paymentMethodsList').empty();
            if (!data.error && data.length > 0) {
                data.forEach(element => {
                    let checkbox;
                    if (element.isActive == 1) {
                        element.isActive = "Attivo";
                        checkbox = '<input type="checkbox" checked value="' + element.id + '" class="togglePayment">';
                    } else {
                        element.isActive = "Non attivo";
                        checkbox = '<input type="checkbox" value="' + element.id + '" class="togglePayment">';
                    }
                    $('#paymentMethodsList').append('<a href="#" class="list-group-item list-group-item-action flex-column align-items-start"> ' +
                        '<div class="d-flex w-100 justify-content-between"> ' +
                        '<div><span class="name mb-1 me-1">' + element.name + '</span><small class="status">(' + element.isActive + ')</small></div>' +
                        '<div><label class="switch" style="font-size: .75rem">' + checkbox + '<span class="slider round"></span></label></div>' +
                        '</div></a>');
                });
                $(".togglePayment").on('click', function () {
                    let paymentMethodId = $(this).attr("value");
                    // hide the switch button
                    // make the request to change the status of payment method
                    $(this).prop('disabled', true)
                    updatePaymentMethod($(this).prop('checked'), paymentMethodId)
                })
            } else {
                $('#paymentMethodsList').empty();
                $('#paymentMethodsList').append('<div class="card-body">' +
                    '<p class="card-text noPaymentMethods">C\'è stato un errore, per favore riprova</p>' +
                    '</div>');
            }
        })
        .fail(function () {
            $('#paymentMethodsList').empty()
            $('#paymentMethodsList').append('<div class="card-body">' +
                '<p class="card-text noPaymentMethods">C\'è stato un errore, per favore riprova</p>' +
                '</div>');
        });
}

/**
 * Main function, it's executed when the DOM is loaded
 */
$(function () {
    getPaymentMethods()
})