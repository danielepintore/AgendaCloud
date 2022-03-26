var timeout;

function getAppointments(date) {
    getPendingAppointments(date)
    $.get("api/get_appointments.php", {date: date})
        .done(function (data) {
            $('#appointmentList').empty();
            let isFirst = true;
            if (!data.error && data.length > 0) {
                data.forEach(element => {
                    if (isFirst) {
                        $('#appointmentList').append('<a href="#" class="list-group-item list-group-item-action flex-column align-items-start appointment-active"> ' +
                            '<div class="d-flex w-100 justify-content-between"> ' +
                            '<h5 class="mb-1">' + element.NomeServizio + ': ' + element.NominativoCliente + '</h5> ' +
                            '<small>' + element.OraInizio + '-' + element.OraFine + '</small> ' +
                            '</div><div class="d-flex w-100 justify-content-between">  ' +
                            '<small>' + element.NominativoDipendente + '</small>' +
                            '<small>Metodo di pagamento: ' + element.NomePagamento + '</small>' +
                            '</div></a>');
                        isFirst = false;
                    } else {
                        $('#appointmentList').append('<a href="#" class="list-group-item list-group-item-action flex-column align-items-start"> ' +
                            '<div class="d-flex w-100 justify-content-between"> ' +
                            '<h5 class="mb-1">' + element.NomeServizio + ': ' + element.NominativoCliente + '</h5> ' +
                            '<small>' + element.OraInizio + '-' + element.OraFine + '</small> ' +
                            '</div> <div class="d-flex w-100 justify-content-between">  ' +
                            '<small>' + element.NominativoDipendente + '</small>' +
                            '<small>Metodo di pagamento: ' + element.NomePagamento + '</small>' +
                            '</div></a>');
                    }
                });
            } else if (data.length === 0) {
                // display no appointments message
                $('#appointmentList').append('');
                $('#appointmentList').append('<div class="card-body">' +
                    '<p class="card-text noAppointments">Non ci sono appuntamenti per il giorno selezionato</p>' +
                    '</div>');
            } else {
                //TODO show error
                $('#appointmentList').empty();
            }
            timeout = setTimeout(getAppointments, 1000 * 60, date)
        })
        .fail(function () {
            //TODO show error
            $('#appointmentList').empty()
        });
}

function updateList() {
    clearTimeout(timeout);
    // aggiungo il gestore per dei click sulle giornate
    $(".enabled-date").on('click', function () {
        clearInterval(timeout);
        // load all bookings
        getAppointments($(this).attr('value'));
    })
}

function getPendingAppointments(date) {
    $.get("api/get_pending_appointments.php", {date: date})
        .done(function (data) {
            $('#pendingAppointmentsList').empty();
            if (!data.error && data.length > 0) {
                data.forEach(element => {
                    $('#pendingAppointmentsList').append('<div class="list-group-item list-group-item-action flex-column align-items-start">' +
                        '<div class="row"><div class="col">' +
                        '<div class="d-flex w-100 justify-content-between"> ' +
                        '<h5 class="mb-1">' + element.NomeServizio + ': ' + element.NominativoCliente + '</h5> ' +
                        '<small>' + element.Data + ' ' + element.OraInizio + '-' + element.OraFine + '</small> ' +
                        '</div> <div class="d-flex w-100 justify-content-between">  ' +
                        '<small>' + element.NominativoDipendente + '</small>' +
                        '<small>Metodo di pagamento: ' + element.NomePagamento + '</small>' +
                        '</div></div>' +
                        '<div class="col-auto">' +
                        '<a class="mini-buttons positive" value="' + element.appointmentId + '"><i class="fa-solid fa-circle-check"></i></a>\n' +
                        '<a class="mini-buttons negative" value="' + element.appointmentId + '"><i class="fa-solid fa-circle-xmark"></i></a>\n' +
                        '</div>' +
                        '</div>');
                });
                setPendingButtons();
            } else if (data.length === 0) {
                // display no appointments message
                $('#pendingAppointmentsList').append('');
                $('#pendingAppointmentsList').append('<div class="card-body">' +
                    '<p class="card-text noAppointments">Non ci sono appuntamenti da accettare</p>' +
                    '</div>');
            } else {
                //TODO show error
                $('#pendingAppointmentsList').empty();
            }
        })
        .fail(function () {
            //TODO show error
            $('#pendingAppointmentsList').empty()
        });
}

function setPendingButtons(){
    $('.mini-buttons.positive').on('click', function (){
        //make the appointment as confirmed
        $.get("api/set_appointment_status.php", {appointmentId: $(this).attr('value'), action: "confirm"})
            .done(function (data) {
                if (data.error){
                    // C'è stato un errore non cancellare nulla
                } else {
                    // non c'è stato nessun errore cancella
                    clearTimeout(timeout);
                    today = new Date;
                    getAppointments(today.getFullYear() + "-" + (today.getMonth() + 1) + "-" + today.getDate());
                }
            })
            .fail(function () {
                // non fare nulla in modo tale da permettere all'utente di riprovare
            });
    })
    $('.mini-buttons.negative').on('click', function (){
        //make the appointment as rejected
        $.get("api/set_appointment_status.php", {appointmentId: $(this).attr('value'), action: "reject"})
            .done(function (data) {
                if (data.error){
                    // C'è stato un errore non cancellare nulla
                } else {
                    // non c'è stato nessun errore cancella
                    clearTimeout(timeout);
                    today = new Date;
                    getAppointments(today.getFullYear() + "-" + (today.getMonth() + 1) + "-" + today.getDate());
                }
            })
            .fail(function () {
                // non fare nulla in modo tale da permettere all'utente di riprovare
            });
    })
}
// function to launch when the DOM is loaded
$(function () {
    let width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
    if (width < 380) {
        let calendar = new Calendar(width - width * 0.1, "#bookings-calendar", updateList, false)
        calendar.getHeader.find('i[class^="icon-chevron"]').on('click', function () {
            if ($(this).attr("class").indexOf("left") != -1) {
                calendar.changeMonth('previous');
            } else {
                calendar.changeMonth('next');
            }
        })
    } else {
        let calendar = new Calendar(380, "#bookings-calendar", updateList, false)
        calendar.getHeader.find('i[class^="icon-chevron"]').on('click', function () {
            if ($(this).attr("class").indexOf("left") != -1) {
                calendar.changeMonth('previous');
            } else {
                calendar.changeMonth('next');
            }
        })
    }
    // get appointmets
    today = new Date;
    getAppointments(today.getFullYear() + "-" + (today.getMonth() + 1) + "-" + today.getDate());
})