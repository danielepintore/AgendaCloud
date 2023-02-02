$.validator.addMethod("time", function (value, element) {
    if (value === "") {
        return this.optional(element) || false;
    }
    if (!(/^(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9])(:([0-5]?[0-9]))?$/i.test(value))) {
        return false;
    }
    return true;
}, "Inserisci un orario corretto");

$.validator.addMethod("timeGreaterThan", function (value, element, params) {
    if (value === "") {
        return this.optional(element)
    }
    if (!/Invalid|NaN/.test(new Date("2000T" + value))) {
        let isGreater = false;
        for (let i = 0; i < params.length; i++) {
            if ($(params[i]).val() === "") {
                isGreater = true;
                continue;
            }
            isGreater = new Date("2000T" + value) > new Date("2000T" + $(params[i]).val());
            if (!isGreater) {
                return isGreater;
            }
        }
        return isGreater;
    } else {
        return this.optional(element);
    }
});

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
        // check if custom time slot selection is enabled
        if ($("#add-custom-timeslot-switch").prop('checked')){
            validateCustomTimeslotForm();
            if (!isCustomTimeslotFormValid()){
                // if the form isn't valid quit the function
                return;
            }
        }
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
 *
 * @param {int} minutes
 * @return {String} durationStr
 * This function returns a string which indicate the duration of a service, it needs an integer representing the minutes
 */
function getDurationStr(minutes) {
    if (minutes < 0) {
        minutes = Math.abs(minutes);
    }
    let durationStr;
    let minuteStr;
    if (minutes >= 60) {
        if (minutes % 60 !== 0) {
            let ore = Math.floor(minutes / 60);
            let remainingMinutes = minutes - (ore * 60);
            if (remainingMinutes > 1) {
                minuteStr = " minuti";
            } else {
                minuteStr = " minuto";
            }
            if (ore > 1) {
                durationStr = ore + " ore e " + remainingMinutes + minuteStr;
            } else if (ore === 1 && remainingMinutes !== 0) {
                durationStr = ore + " ora e " + remainingMinutes + minuteStr;
            }
        } else {
            let ore = minutes / 60
            if (ore > 1) {
                durationStr = String(ore) + " ore";
            } else {
                durationStr = String(ore) + " ora";
            }
        }
    } else {
        if (minutes > 1) {
            durationStr = String(minutes) + " minuti";
        } else {
            durationStr = String(minutes) + " minuto";
        }
    }
    return durationStr;
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
                let durationStr = getDurationStr(durata)
                $("#time-lenght").text(durationStr + ",")
                // set cost
                $("#prezzo-servizio").text(data.Costo + "€")
                // set div visible if it isn't
                if ($("#info-servizio").hasClass("d-none")) {
                    $("#info-servizio").removeClass("d-none")
                }
                // if needTimeSupervision is true show the custom time picker
                if (data.needTimeSupervision){
                    $("#custom-timeslot-selector-div").removeClass("d-none");
                    if (!$("#add-custom-timeslot-switch").prop('disabled')){
                        $("#add-custom-timeslot-switch").prop('disabled', true);
                        $("#customTimeslot-serviceStartTime").prop('disabled', true);
                        $("#customTimeslot-serviceEndTime").prop('disabled', true);
                    }
                } else {
                    $("#custom-timeslot-selector-div").addClass("d-none");
                    $("#add-custom-timeslot-switch").prop('disabled', true);
                    $("#customTimeslot-serviceStartTime").prop('disabled', true);
                    $("#customTimeslot-serviceEndTime").prop('disabled', true);
                    $("#add-custom-timeslot-switch").prop('checked', false);
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
            $("#add-custom-timeslot-switch").prop('disabled', false);
        })
        .fail(function () {
            $('#lista-orari').empty();
            $("#add-custom-timeslot-switch").prop('disabled', false);
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
    //disabilita il pulsante per aggiungere orari custom
    $("#add-custom-timeslot-switch").prop('disabled', true);
    $("#customTimeslot-serviceStartTime").prop('disabled', true);
    $("#customTimeslot-serviceEndTime").prop('disabled', true);

    // aggiungo il gestore per dei click sulle giornate
    $(".enabled-date").on('click', function () {
        if (!$("#lista_dipendenti").prop('disabled')) {
            // riabilita il pulsante per gli orari custom
            if ($("#add-custom-timeslot-switch").prop('disabled')){
                $("#add-custom-timeslot-switch").prop('disabled', false);
                $("#customTimeslot-serviceStartTime").prop('disabled', false);
                $("#customTimeslot-serviceEndTime").prop('disabled', false);
            }
            // se usa custom orario non è attivo
            if (!$("#add-custom-timeslot-switch").prop('checked')){
                getTimeSlots($(this).attr('value'), $("#tipoServizio").val(), $("#lista_dipendenti").val())
            } else {
                // mostra informazioni
                $('#lista-orari').empty();
                $('#lista-orari').append('<option selected disabled hidden>Stai selezionando un orario a piacere</option>');
                $('#lista-orari').prop('disabled', true);
            }
        } else {
            $('.day-selected').removeClass('day-selected');
            $("#errorModalMessage").text("Devi selezionare un servizio");
            $("#errorModal").modal("show");
        }
    })
}

function validateCustomTimeslotForm(){
    $("#customTimeslot-form").validate({
        rules: {
            customServiceStartTime: {required: true, time: true},
            customServiceEndTime: {required: true, time: true, timeGreaterThan: ["#customTimeslot-serviceStartTime"]},
        },
        messages: {
            customServiceStartTime: "L'orario non è valido",
            customServiceEndTime: "L'orario non è valido"
        },
        errorLabelContainer: '.custom-timeslot-error-span'
        //TODO add error management code because now it gives 2 errors
    })
}

function isCustomTimeslotFormValid(){
    return $("#customTimeslot-form").valid();
}

/**
 * checks if the times inserted in custom timeslot are valid, then it calculate the time string and display the time string
 */
function calculateCustomTimeslotDuration(){
    validateCustomTimeslotForm();
    if (isCustomTimeslotFormValid()) {
        // get time delta
        let startTime;
        let endTime;
        startTime = $("#customTimeslot-serviceStartTime").val();
        endTime = $("#customTimeslot-serviceEndTime").val();
        let startTimeDate = new Date("2000T" + startTime);
        let endTimeDate = new Date("2000T" + endTime);
        // milliseconds to minutes
        // delta should be < 0
        let delta = (startTimeDate - endTimeDate) / (1000 * 60)
        let durationStr = getDurationStr(delta);
        $("#custom-timeslot-duration").text("Durata: " + durationStr);
        $("#custom-timeslot-duration").removeClass('d-none');
    } else {
        $("#custom-timeslot-duration").addClass('d-none');
    }
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

    $("#add-custom-timeslot-switch").on('change', function () {
        $('#custom-timeslot').collapse('toggle');
        $(this).prop('disabled', true);
        var isChecked = $(this).prop('checked');
        if (isChecked) {
            $('#lista-orari').empty();
            $('#lista-orari').append('<option selected disabled hidden>Stai selezionando un orario a piacere</option>');
            $('#lista-orari').prop('disabled', true);
        }
    });
    // custom timeslot switch base logic
    $('#custom-timeslot').on('shown.bs.collapse', function () {
        $("#add-custom-timeslot-switch").prop('disabled', false);
    });
    $('#custom-timeslot').on('hidden.bs.collapse', function () {
        getTimeSlots($(".day-selected").attr('value'), $("#tipoServizio").val(), $("#lista_dipendenti").val());
    });
    // on custom timeslot changes time event
    $(".customTimeslotFields").on('change', function () {
        calculateCustomTimeslotDuration();
    });
    // loads service info data
    loadServices()
})