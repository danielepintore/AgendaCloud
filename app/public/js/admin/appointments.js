/**
 * This function loads all the service available for the user
 */
function loadServices() {
    // set the default selected service
    $("#tipoServizio").val(-1)
    $("#tipoPagamento").val($("#tipoPagamento option:eq(1)").val());
    $("#lista_dipendenti").val(-1)
    $("#lista_dipendenti").prop('disabled', true)
    let serviceId;
    $('#tipoServizio').on('change', function () {
        serviceId = $(this).val()
        // disabilita lista dipendenti
        $('#lista_dipendenti').prop('disabled', true);
        // rimuovi giorno calendario se gia selezionato
        $('.day-selected').removeClass('day-selected');
        // rimuovo gli orari selezionati
        $('#lista-orari').empty()
        $('#lista-orari').append('<option selected disabled hidden>Seleziona una data</option>')
        // disabilito la box per la scelta degli orari
        $('#lista-orari').prop('disabled', true);
        // disabilita il pulsante
        $('#prenota_btn').prop('disabled', true);
        // selezionato il primo elemento che non ha valori
        $.get("/api/get_employees.php", {serviceId: serviceId})
            .done(function (data) {
                $('#lista_dipendenti').empty()
                if (!data.error && data.length > 0) {
                    $('#lista_dipendenti').append('<option value="-1" disabled hidden>Devi selezionare un servizio</option>')
                    data.forEach(element => {
                        $('#lista_dipendenti').append('<option value="' + element.id + '">' + element.Nominativo + '</option>');
                    });
                    getSelectedServiceInfo(serviceId);
                    $('#lista_dipendenti').prop('disabled', false);
                } else {
                    $('#lista_dipendenti').append('<option value="-1" selected disabled hidden>Devi selezionare un servizio</option>')
                    $('#lista_dipendenti').prop('disabled', true);
                }
            })
            .fail(function () {
                $("#info-servizio").addClass("d-none")
                $('#lista_dipendenti').empty()
            });
    });
    $('#scelta_dipendente').on('change', function () {
        // rimuovi giorno calendario se gia selezionato
        $('.day-selected').removeClass('day-selected');
        // disabilita il pulsante
        $('#prenota_btn').prop('disabled', true);
        // rimuovo gli orari selezionati
        $('#lista-orari').empty()
        $('#lista-orari').append('<option selected disabled hidden>Seleziona una data</option>')
        // disabilito la lista degli orari
        $('#lista-orari').prop('disabled', true);
    })

    $("#prenota_btn").on("click", function () {
        $("#form_dati_personali").validate({
            rules: {
                nomeInput: {required: true, minlength: 3},
                cognomeInput: {required: true, minlength: 3},
                emailInput: {required: false, email: true, minlength: 3},
                phoneInput: {required: false, phoneUS: true}
            },
            messages: {
                nomeInput: "Per favore inserisci il nome del cliente",
                cognomeInput: "Per favore inserisci il cognome del cliente",
                emailInput: "Per favore inserisci una email valida",
                phoneInput: "Per favore inserisci numero di cellulare valido"

            }
        })
        if ($("#form_dati_personali").valid()) {
            //start form
            $.post("/admin/api/book.php", {
                serviceId: $("#tipoServizio").val(),
                date: $(".day-selected").attr("value"),
                employeeId: $("#lista_dipendenti").val(),
                slot: $("#lista-orari").val(),
                clientNome: $("#nomeInput").val(),
                clientCognome: $("#cognomeInput").val(),
                clientEmail: $("#emailInput").val(),
                clientPhone: $("#phoneInput").val()
            })
                .done(function (data) {
                    if (!data.error) {
                        // show confirmation modal
                        $("#successModal").modal("show");
                        // clean all the fields
                        $("#tipoServizio").val(-1)
                        $("#lista_dipendenti").val(-1)
                        $('#lista_dipendenti').prop('disabled', true);
                        $('#info-servizio').addClass('d-none');
                        $('#lista-orari').empty()
                        $('#lista-orari').append('<option selected disabled hidden>Seleziona una data</option>')
                        $('#lista-orari').prop('disabled', true);
                        // disabilita il pulsante
                        $('#prenota_btn').prop('disabled', true);
                        $('.day-selected').removeClass('day-selected');
                        $("#nomeInput").val("");
                        $("#cognomeInput").val("");
                        $("#emailInput").val("");
                        $("#phoneInput").val("");
                    } else {
                        // show error modal
                        $("#errorModalMessage").text("C'è stato un errore con la prenotazione, riprova");
                        $("#errorModal").modal("show");
                        // clean all the fields
                        $('#lista-orari').empty()
                        $('#lista-orari').append('<option selected disabled hidden>Seleziona una data</option>')
                        $('#lista-orari').prop('disabled', true);
                        // disabilita il pulsante
                        $('#prenota_btn').prop('disabled', true);
                        $('.day-selected').removeClass('day-selected');
                        $("#nomeInput").val("");
                        $("#cognomeInput").val("");
                        $("#emailInput").val("");
                        $("#phoneInput").val("");
                    }
                }).fail(function () {
                // show confimation modal
                $("#errorModalMessage").text("C'è stato un errore con la prenotazione, riprova");
                $("#errorModal").modal("show");
                // clean all the fields
                $('#lista-orari').empty()
                $('#lista-orari').append('<option selected disabled hidden>Seleziona una data</option>')
                $('#lista-orari').prop('disabled', true);
                // disabilita il pulsante
                $('#prenota_btn').prop('disabled', true);
                $('.day-selected').removeClass('day-selected');
                $("#nomeInput").val("");
                $("#cognomeInput").val("");
                $("#emailInput").val("");
                $("#phoneInput").val("");
            });
        }
    })
}

/**
 * Retrieves from the api the service info using it's id
 * @param serviceId
 */
function getSelectedServiceInfo(serviceId) {
    $.get("/api/get_service_info.php", {serviceId: serviceId})
        .done(function (data) {
            if (!data.error) {
                // set durata
                // we need to convert it if it's bigger than an hour
                var durata = data.Durata
                var minutiStr;
                if (durata >= 60) {
                    if (durata % 60 !== 0) {
                        var ore = parseInt(durata / 60)
                        var minuti = durata - (ore * 60)
                        if (minuti > 1) {
                            minutiStr = " minuti,"
                        } else {
                            minutiStr = " minuto,"
                        }
                        if (ore > 1) {
                            $("#time-lenght").text(ore + " ore e " + minuti + minutiStr)
                        } else if (ore === 1 && minuti !== 0) {
                            $("#time-lenght").text(ore + " ora e " + minuti + minutiStr)
                        }
                    } else {
                        $("#time-lenght").text(durata / 60 + " ora,")
                    }
                } else {
                    if (durata > 1) {
                        $("#time-lenght").text(durata + " minuti,")
                    } else {
                        $("#time-lenght").text(durata + " minuto,")
                    }
                }
                // set cost
                $("#prezzo-servizio").text(data.Costo + "€")
                // set div visible if it isn't
                if ($("#info-servizio").hasClass("d-none")) {
                    $("#info-servizio").removeClass("d-none")
                }
            } else {
                // hide the info
                $("#info-servizio").addClass("d-none")
            }
        })
        .fail(function () {
            $("#info-servizio").addClass("d-none")
        });
}

/**
 * Gets the available slots of the employee identified by the id, for a specific service on a specific day
 * @param date
 * @param serviceId
 * @param employeeId
 */
function getTimeSlots(date, serviceId, employeeId) {
    $('#lista-orari').prop('disabled', true);
    $.get("/admin/api/get_slots.php", {date: date, serviceId: serviceId, employeeId: employeeId})
        .done(function (data) {
            $('#lista-orari').empty()
            if (!data.error && data.length > 0) {
                data.forEach(element => {
                    $('#lista-orari').append('<option value="' + element.startTime + '-' + element.endTime + '">' + element.startTime + '-' + element.endTime + '</option>')
                });
                $('#lista-orari').prop('disabled', false);
                $('#prenota_btn').prop('disabled', false);
            } else {
                // nessuno slot libero oppure un errore nel caricamento degli slot
                $('#lista-orari').append('<option selected disabled hidden>Nessuno slot libero</option>')
                // disattivo il pulsante per prenotarsi
                $('#prenota_btn').prop('disabled', true);

            }
        })
        .fail(function () {
            $('#lista-orari').empty()
        });
}

/**
 * Function called when the user change the page of the calendar
 */
function onCalendarChange() {
    // rimuovo gli orari selezionati
    $('#lista-orari').empty()
    $('#lista-orari').append('<option selected disabled hidden>Seleziona una data</option>')
    $('#lista-orari').prop('disabled', true);
    // disabilita il pulsante
    $('#prenota_btn').prop('disabled', true);
    // aggiungo il gestore per dei click sulle giornate
    $(".enabled-date").on('click', function () {
        if (!$("#lista_dipendenti").prop('disabled')) {
            getTimeSlots($(this).attr('value'), $("#tipoServizio").val(), $("#lista_dipendenti").val())
        } else {
            $('.day-selected').removeClass('day-selected');
            $("#errorModalMessage").text("Devi selezionare un servizio");
            $("#errorModal").modal("show");
        }
    })
}

/**
 * Main function, it's executed when the DOM is loaded
 */
$(function () {
    let calendar = new Calendar(336, "#bookings-calendar", onCalendarChange, true, false)
    calendar.getHeader.find('i[class^="icon-chevron"]').on('click', function () {
        if ($(this).attr("class").indexOf("left") != -1) {
            calendar.changeMonth('previous');
        } else {
            calendar.changeMonth('next');
        }
    })
    loadServices()
})