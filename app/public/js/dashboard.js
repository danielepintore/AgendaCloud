var timeout;

function getAppointments(date){
    $.get("get_appointments.php", {date: date})
        .done(function (data) {
            $('#listaAppuntamenti').empty();
            let isFirst = true;
            if (!data.error && data.length > 0) {
                data.forEach(element => {
                    if (isFirst){
                        $('#listaAppuntamenti').append('<a href="#" class="list-group-item list-group-item-action flex-column align-items-start active"> ' +
                            '<div class="d-flex w-100 justify-content-between"> ' +
                            '<h5 class="mb-1">' + element.NomeServizio + ': ' + element.NominativoCliente + '</h5> ' +
                            '<small>' + element.OraInizio + '-' + element.OraFine + '</small> ' +
                            '</div><div class="d-flex w-100 justify-content-between">  ' +
                            '<small>'+element.NominativoDipendente+'</small>' +
                            '<small>Metodo di pagamento: '+element.TipoPagamento+'</small>' +
                            '</div></a>');
                        isFirst = false;
                    } else {
                        $('#listaAppuntamenti').append('<a href="#" class="list-group-item list-group-item-action flex-column align-items-start"> ' +
                            '<div class="d-flex w-100 justify-content-between"> ' +
                            '<h5 class="mb-1">' + element.NomeServizio + ': ' + element.NominativoCliente + '</h5> ' +
                            '<small>' + element.OraInizio + '-' + element.OraFine + '</small> ' +
                            '</div><div class="d-flex w-100 justify-content-between">  ' +
                            '<small>'+element.NominativoDipendente+'</small>' +
                            '<small>Metodo di pagamento: '+element.TipoPagamento+'</small>' +
                            '</div></a>');
                    }
                });
            } else {
                //TODO show error
                $('#listaAppuntamenti').empty();
            }
            timeout = setTimeout(getAppointments, 1000 * 60, date)
        })
        .fail(function () {
            //TODO show error
            $('#lista-orari').empty()
        });
}
function updateList(){
    clearTimeout(timeout);
    // aggiungo il gestore per dei click sulle giornate
    $(".enabled-date").on('click', function () {
        clearInterval(timeout);
        // load all bookings
        getAppointments($(this).attr('value'));
    })
}
// function to launch when the DOM is loaded
$(function () {
    let width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
    if (width < 380){
        let calendar = new Calendar(width-width*0.1, "#bookings-calendar", updateList, false)
        calendar.getHeader.find('i[class^="icon-chevron"]').on('click', function (){
            if ($(this).attr("class").indexOf("left") != -1) {
                calendar.changeMonth('previous');
            } else {
                calendar.changeMonth('next');
            }
        })
    } else {
        let calendar = new Calendar(380, "#bookings-calendar", updateList, false)
        calendar.getHeader.find('i[class^="icon-chevron"]').on('click', function (){
            if ($(this).attr("class").indexOf("left") != -1) {
                calendar.changeMonth('previous');
            } else {
                calendar.changeMonth('next');
            }
        })
    }
})