$(function() {
    function printCalendar() {
        printDays();
        var daysOfMonth = getDaysOfMonth();
        var counter = 0;
        var isFirstDay = false;
        calendar_content.empty();
        // ottengo il primo giorno del mese e aggiungo celle vuote per i giorni della settimana, dato che 
        // non tutti i mesi inziano con il lunedi
        while (!isFirstDay) {
            if (days[counter] == daysOfMonth[0].weekday) {
                // abbiamo trovato il primo giorno del mese, possiamo uscire dal ciclo
                isFirstDay = true
            } else {
                // non abbiamo trovato il primo giorno del mese aggiungiamo una casella vuota e proseguiamo
                calendar_content.append('<div class="blank"></div>');
                counter++
            }
        }
        var stop = false;
        for (var day_number = 0;!stop; day_number++) {
            if (day_number >= daysOfMonth.length && (counter+day_number) % 7 == 0){
                stop = true;
                break;
            }
            if (day_number >= daysOfMonth.length) {
                calendar_content.append('<div class="blank"></div>')
            } else {
                var day = daysOfMonth[day_number].day;
                var date_div = isSameDate(new Date(year, month - 1, day)) ? '<div class="today">' : "<div>";
                calendar_content.append(date_div + "" + day + "</div>")
            }
        }
        var color = color_palette[month - 1];
        header.css("background-color", color).find("h1").text(months[month - 1] + " " + year);
        week_days.find("div").css("color", color);
        calendar_content.find(".today").css("background-color", color);
        d()
    }

    function getDaysOfMonth() {
        var dayOfMonth = [];
        for (var i = 1; i < getLastMonthDay(year, month) + 1; i++) {
            dayOfMonth.push({
                day: i,
                weekday: days[getDayOfWeek(year, month, i)]
            })
        }
        return dayOfMonth
    }

    function printDays() {
        // svuota i giorni della settimana
        week_days.empty();
        // aggiunge i giorni della settimana
        for (var i = 0; i < 7; i++) {
            // aggiunge un div per ogni giorno
            week_days.append("<div>" + days[i].substring(0, 3) + "</div>")
        }
    }

    function d() {
        var t;
        var n = $("#calendar").css("width", calendar_width + "px");
        n.find(t = "#calendar_weekdays, #calendar_content").css("width", calendar_width + "px").find("div").css({
            width: calendar_width / 7 + "px",
            height: calendar_width / 7 + "px",
            "line-height": calendar_width / 7 + "px"
        });
        n.find("#calendar_header").css({
            height: calendar_width * (1 / 7) + "px"
        }).find('i[class^="icon-chevron"]').css("line-height", calendar_width * (1 / 7) + "px")
    }

    function getLastMonthDay(year, month) {
        return (new Date(year, month, 0)).getDate()
    }

    function getDayOfWeek(year, month, day) {
        // we need -1 because the week starts from monday
        var day = (new Date(year, month - 1, day)).getDay() - 1
        if (day == -1){
            day = 6
        }
        return day
    }

    function isSameDate(date) {
        return getFormattedDate(new Date) == getFormattedDate(date)
    }

    function getFormattedDate(date) {
        return date.getFullYear() + "/" + (date.getMonth() + 1) + "/" + date.getDate()
    }

    function getCurrentDate() {
        var date = new Date(year, month);
        year = date.getFullYear();
        month = date.getMonth() + 1
    }
    var calendar_width = 480;
    var year = 2023;
    var month = 10;
    var r = [];
    var months = ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"];
    var days = ["Lunedì", "Martedì", "Mercoledì", "Giovedì", "Venerdì", "Sabato", "Domenica"];
    var color_palette = ["#16a085", "#1abc9c", "#c0392b", "#27ae60", "#FF6860", "#f39c12", "#f1c40f", "#e67e22", "#2ecc71", "#e74c3c", "#d35400", "#2c3e50"];
    var calendar = $("#calendar");
    var header = calendar.find("#calendar_header");
    var week_days = calendar.find("#calendar_weekdays");
    var calendar_content = calendar.find("#calendar_content");
    getCurrentDate();
    printCalendar();
    header.find('i[class^="icon-chevron"]').on("click", function() {
        var e = $(this);
        var r = function(e) {
            month = e == "next" ? month + 1 : month - 1;
            if (month < 1) {
                month = 12;
                year--
            } else if (month > 12) {
                month = 1;
                year++
            }
            printCalendar()
        };
        if (e.attr("class").indexOf("left") != -1) {
            r("previous")
        } else {
            r("next")
        }
    })
})