function loadServices() {
    $.get("api/get_services.php")
    .done(function(data){
        isFirstElement = true
        data.forEach(element => {
            if (isFirstElement) {
                $('#durataServizio').html('Durata: ' + element.Durata + ' minuti');
                isFirstElement = false
            }
            $('#tipoServizio').append('<option value="'+element.id+'">'+element.Nome+'</option>') 
        });
    }).fail(function(){
        alert("C'è stato un errore contatta riprova più tardi o contatta l'assistenza")
    }); 

    // change duration paragraph
    $('#tipoServizio').on('change', function(){
        $.get("api/get_services.php", {service: $(this).val()})
        .done(function(data){
            $('#durataServizio').html('Durata: ' + data.Durata + ' minuti');
          });
    });
}

function removeBlur(){
    // remove blur once the day is selected
    if($("#orari").hasClass("active")){
        $("#orari").removeClass("active")
    }
}

// This function generates the slots
function getTimeSlots(date, serviceId) {
    $.get("api/get_slots.php", {date: date, serviceId: serviceId})
    .done(function(){
        //request done
    })
    .fail(function(){
        //request failed
    })
}

function setDayListener() {
    $(".enabled-date").on('click', function(){
        removeBlur()
        getTimeSlots($(this).attr('value'), $("#tipoServizio").val()) 
    })
}
// function to launch when the DOM is loaded
$(function(){
    startCalendar()
    loadServices()
    setDayListener()
})