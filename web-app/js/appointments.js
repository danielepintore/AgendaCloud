function loadServices() {

    $('#tipoServizio').on('change', function(){
        // disabilita lista dipendenti
        $('#lista_dipendenti').prop('disabled', true);
        // rimuovi giorno calendario se gia selezionato
        $('.day-selected').removeClass('day-selected');
        // rimuovo gli orari selezionati
        $('#lista-orari').empty()
        // disabilita il pulsante
        $('#prenota_btn').prop('disabled', true);
        // selezionato il primo elemento che non ha valori
        if ($(this).val() == -1){
            addBlur("#scelta_dipendente")
            addBlur("#calendar")
            addBlur("#orari")
            addBlur("#prenota_btn")
            addBlur("#dati_personali")
            return
        }
        $.get("api/get_dipendenti.php", {service: $(this).val()})
            .done(function(data){
                $('#lista_dipendenti').empty()
                if (!data.error && data.length > 0){
                    data.forEach(element => {
                        $('#lista_dipendenti').append('<option value="'+element.id+'">'+element.Nominativo+'</option>')
                    });
                    $('#lista_dipendenti').prop('disabled', false);
                    removeBlur("#scelta_dipendente")
                    removeBlur("#calendar")
                } else {
                    addBlur("#scelta_dipendente")
                    addBlur("#calendar")
                    addBlur("#orari")
                    addBlur("#prenota_btn")
                    addBlur("#dati_personali")
                }
            })
            .fail(function (){
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
            $.post("api/book.php", {date: $(".day-selected").attr("value"), serviceId: $("#tipoServizio").val(), workerId: $("#lista_dipendenti").val(), slot: $("#lista-orari").val(), client:{nome: $("#nomeInput").val(), cognome: $("#cognomeInput").val(), email: $("#emailInput").val(), phone: $("#phoneInput").val()}})
                .done(function(data){
                    if (!data.error){
                        $("#modalEsito").html("Prenotazione completata")
                        $("#modalBodyResultParagraph").html("Prenotazione completata con sucesso!<br>Riceverai a breve una mail di conferma.")
                        $('#resultModal').modal('show');
                    } else {
                        // output an error
                        $("#modalEsito").html("Prenotazione non completata")
                        $("#modalBodyResultParagraph").html("C'è stato un errore 😓, per favore riprova.")
                        $('#resultModal').modal('show');
                    }
                })
                .fail(function (){
                    // output an error
                });
        }
    })
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
function getTimeSlots(date, serviceId, workerId) {
    $('#lista-orari').prop('disabled', true);
    $.get("api/get_slots.php", {date: date, serviceId: serviceId, workerId: workerId})
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