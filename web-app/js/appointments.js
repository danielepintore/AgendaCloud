function loadServices() {

    $('#tipoServizio').on('change', function(){
        // disabilita lista dipendenti
        $('#lista_dipendenti').prop('disabled', true);
        // rimuovi giorno calendario se gia selezionato
        $('.day-selected').removeClass('day-selected');
        // selezionato il primo elemento che non ha valori
        if ($(this).val() == -1){
            addBlur("#scelta_dipendente")
            addBlur("#calendar")
            addBlur("#orari")
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
                }
            })
            .fail(function (){
                $('#lista_dipendenti').empty()
            });
    });
    /*
    // change duration paragraph
    $('#tipoServizio').on('change', function(){
        $.get("api/get_services.php", {service: $(this).val()})
        .done(function(data){
            $('#durataServizio').html('Durata: ' + data.Durata + ' minuti');
          });
    });
     */
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
    $.get("api/get_slots.php", {date: date, serviceId: serviceId, workerId: workerId})
    .done(function(){
        //request done
    })
    .fail(function(){
        //request failed
    })
}
// function to launch when the DOM is loaded
$(function(){
    startCalendar()
    loadServices()
})