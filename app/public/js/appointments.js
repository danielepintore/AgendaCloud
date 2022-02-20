function loadServices() {
    // set the default selected service
    $("#tipoServizio").val(-1)
    $("#tipoPagamento").val($("#tipoPagamento option:eq(1)").val());
    var serviceId;
    $('#tipoServizio').on('change', function(){
        serviceId = $(this).val()
        // disabilita lista dipendenti
        $('#lista_dipendenti').prop('disabled', true);
        // rimuovi giorno calendario se gia selezionato
        $('.day-selected').removeClass('day-selected');
        // rimuovo gli orari selezionati
        $('#lista-orari').empty()
        // disabilito la box per la scelta degli orari
        $('#lista-orari').prop('disabled', true);
        // disabilita il pulsante
        $('#prenota_btn').prop('disabled', true);
        // selezionato il primo elemento che non ha valori
        if (serviceId == -1){
            addBlur("#scelta_dipendente")
            addBlur("#calendar")
            addBlur("#orari")
            addBlur("#prenota_btn")
            addBlur("#dati_personali")
            addBlur("#info-servizio")
            addBlur("#scelta_metodo_pagamento")
            return
        }
        $.get("api/get_employees.php", {serviceId: serviceId})
            .done(function(data){
                $('#lista_dipendenti').empty()
                if (!data.error && data.length > 0){
                    data.forEach(element => {
                        $('#lista_dipendenti').append('<option value="'+element.id+'">'+element.Nominativo+'</option>')
                    });
                    getSelectedServiceInfo(serviceId)
                    $('#lista_dipendenti').prop('disabled', false);
                    removeBlur("#scelta_dipendente")
                    removeBlur("#calendar")
                    removeBlur("#info-servizio")
                    removeBlur("#scelta_metodo_pagamento")
                } else {
                    addBlur("#scelta_dipendente")
                    addBlur("#calendar")
                    addBlur("#orari")
                    addBlur("#prenota_btn")
                    addBlur("#dati_personali")
                    addBlur("#info-servizio")
                    addBlur("#scelta_metodo_pagamento")
                }
            })
            .fail(function (){
                $("#info-servizio").addClass("d-none")
                $('#lista_dipendenti').empty()
            });
    });
    $('#scelta_dipendente').on('change', function(){
        // rimuovi giorno calendario se gia selezionato
        $('.day-selected').removeClass('day-selected');
        // rimuovo gli orari selezionati
        $('#lista-orari').empty()
        // disabilita il pulsante
        $('#prenota_btn').prop('disabled', true);
        // disabilito la lista degli orari
        $('#lista-orari').prop('disabled', true);
    })
    /*
    // change duration paragraph
    $('#tipoServizio').on('change', function(){
        $.get("api/get_services.php", {service: $(this).val()})
        .done(function(data){
            $('#durataServizio').html('Durata: ' + data.Durata + ' minuti');
          });
    });   ,
     */
    $("#prenota_btn").on("click", function() {
        $("#form_dati_personali").validate({
            rules: {
                nomeInput: {required: true, minlength: 3},
                cognomeInput: {required: true, minlength: 3},
                emailInput: {required: true, email: true, minlength: 3},
                phoneInput: {required: true, phoneUS: true}
            },
            messages: {
                nomeInput: "Per favore inserisci il tuo nome",
                cognomeInput: "Per favore inserisci il tuo cognome",
                emailInput: "Per favore inserisci una email valida",
                phoneInput: "Per favore inserisci numero di cellulare valido"

            }
        })
        if($("#form_dati_personali").valid()){
            //set hidden form value
            $("#dayPOST").val($(".day-selected").attr("value"))
            $("#idServicePOST").val($("#tipoServizio").val())
            $("#employeeIdPOST").val($("#lista_dipendenti").val())
            $("#slotPOST").val($("#lista-orari").val())
            $("#clientNomePOST").val($("#nomeInput").val())
            $("#clientCognomePOST").val($("#cognomeInput").val())
            $("#clientEmailPOST").val($("#emailInput").val())
            $("#clientPhonePOST").val($("#phoneInput").val())
            //start form
            $("#paymentForm").trigger('submit');
            return
        }
    })
}

function getSelectedServiceInfo(serviceId){
    $.get("api/get_services.php", {serviceId: serviceId})
        .done(function(data){
            if (!data.error){
                // set durata
                // we need to convert it if it's bigger than an hour
                var durata = data.Durata
                var minutiStr;
                if (durata >= 60){
                    if (durata % 60 !== 0){
                        var ore = parseInt(durata / 60)
                        var minuti = durata - (ore * 60)
                        if (minuti > 1){
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
                    if (durata > 1){
                        $("#time-lenght").text(durata + " minuti,")
                    } else {
                        $("#time-lenght").text(durata + " minuto,")
                    }
                }
                // set cost
                $("#prezzo-servizio").text(data.Costo + "€")
                // set div visible if it isn't
                if ($("#info-servizio").hasClass("d-none")){
                    $("#info-servizio").removeClass("d-none")
                }
            } else {
                // hide the info
                $("#info-servizio").addClass("d-none")
            }
        })
        .fail(function (){
            $("#info-servizio").addClass("d-none")
        });
}

function removeBlur(element){
    // remove blur once the day is selected
    if($(element).hasClass("active")){
        $(element).removeClass("active")
        $(element).removeClass("no-click")
    }
}

function addBlur(element){
    // remove blur once the day is selected
    if($(element).hasClass("blur") && $(element).hasClass("active")){
    } else if($(element).hasClass("blur")) {
        $(element).addClass("active")
        $(element).addClass("no-click")
    }
}

// This function generates the slots
function getTimeSlots(date, serviceId, employeeId) {
    $('#lista-orari').prop('disabled', true);
    $.get("api/get_slots.php", {date: date, serviceId: serviceId, employeeId: employeeId})
        .done(function(data){
            $('#lista-orari').empty()
            if (!data.error && data.length > 0){
                data.forEach(element => {
                    $('#lista-orari').append('<option value="'+element.start_time+'-'+element.end_time + '">'+ element.start_time + '-' + element.end_time + '</option>')
                });
                $('#lista-orari').prop('disabled', false);
            } else {
                // nessuno slot libero oppure un errore nel caricamento degli slot
                $('#lista-orari').append('<option selected disabled hidden>Nessuno slot libero</option>')
                // disattivo il pulsante per prenotarsi
                $('#prenota_btn').prop('disabled', true);

            }
        })
        .fail(function (){
            $('#lista-orari').empty()
        });
}
// function to launch when the DOM is loaded
$(function(){
    startCalendar()
    loadServices()
})