Date.prototype.addDays = function (days) {
    let date = new Date(this.valueOf());
    date.setDate(date.getDate() + days);
    return date;
}

class Calendar {
    //var calendar_width = 480; // original value
    //#calendar_width = 336;
    //var color_palette = ["#16a085", "#1abc9c", "#c0392b", "#27ae60", "#FF6860", "#f39c12", "#f1c40f", "#e67e22", "#2ecc71", "#e74c3c", "#d35400", "#2c3e50"];
    #calendar_width;
    #year;
    #month;
    #months = ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"];
    #days = ["Lunedì", "Martedì", "Mercoledì", "Giovedì", "Venerdì", "Sabato", "Domenica"];
    #calendar;
    #header;
    #week_days;
    #calendar_content;
    #daysListener;
    #isLimited;

    constructor(calendar_width, calendar, dayListener, isLimited) {
        this.#calendar_width = calendar_width;
        this.#calendar = $(calendar);
        this.#header = this.#calendar.find(calendar + ", .calendar-header");
        this.#week_days = this.#calendar.find(calendar + ", .calendar-weekdays");
        this.#calendar_content = this.#calendar.find(calendar + ", .calendar-content");
        this.#daysListener = dayListener;
        this.#isLimited = isLimited;
        this.#getCurrentDate();
        this.#printCalendar();
        this.#daysListener();
    }

    get getHeader() {
        return this.#header;
    }

    changeMonth(action){
        if (action == "next"){
            this.#month = this.#month + 1;
            if (this.#month > 12){
                this.#month = 1;
                this.#year = this.#year + 1;
            }
        } else {
            this.#month = this.#month - 1;
            if (this.#month < 1){
                this.#month = 12;
                this.#year = this.#year - 1;
            }
        }
        this.#printCalendar();
        this.#daysListener();
    }

    #printCalendar() {
        this.#printDays();
        let daysOfMonth = this.#getDaysOfMonth();
        let counter = 0;
        let isFirstDay = false;
        this.#calendar_content.empty();
        // ottengo il primo giorno del mese e aggiungo celle vuote per i giorni della settimana, dato che
        // non tutti i mesi inziano con il lunedi
        while (!isFirstDay) {
            if (this.#days[counter] == daysOfMonth[0].weekday) {
                // abbiamo trovato il primo giorno del mese, possiamo uscire dal ciclo
                isFirstDay = true
            } else {
                // non abbiamo trovato il primo giorno del mese aggiungiamo una casella vuota e proseguiamo
                this.#calendar_content.append('<div class="blank"></div>');
                counter++;
            }
        }
        let stop = false;
        // aggiungo celle fino a quando non riempo ogni riga
        for (let day_number = 0; !stop; day_number++) {
            if (day_number >= daysOfMonth.length && (counter + day_number) % 7 == 0) {
                // Riga riempita devo uscire
                stop = true;
                break;
            } else if (day_number >= daysOfMonth.length) {
                // riempo con cella vuota
                this.#calendar_content.append('<div class="blank"></div>')
            } else {
                // aggiungo la cella contenete la data
                let day = daysOfMonth[day_number].day;
                // se è la data di oggi cambia colore di sfondo grazie alla classe today
                let date_div = '<div class="old-date">';
                // check if the date is today
                if (this.#isSameDate(new Date(this.#year, this.#month - 1, day))) {
                    date_div = '<div class="today enabled-date" value="' + this.#getFormattedDate(new Date(this.#year, this.#month - 1, day)) + '">';
                } else if (this.#isTooFar(new Date(this.#year, this.#month - 1, day)) && this.#isLimited) { // check if the date is too far in the future
                    date_div = '<div class="disabled-date">';
                } else if (!this.#isOldDate(new Date(this.#year, this.#month - 1, day)) || !this.#isLimited) { // check if the date isn't old
                    date_div = '<div class="enabled-date" value="' + this.#getFormattedDate(new Date(this.#year, this.#month - 1, day)) + '">';
                }
                // otherwise, the date is an old date
                this.#calendar_content.append(date_div + "" + day + "</div>")
            }
        }
        //var color = color_palette[month - 1];
        let color = "#f7f7f7"
        let textColor = "#000000"
        this.#header.css("background-color", color).find("h1").text(this.#months[this.#month - 1] + " " + this.#year);
        this.#week_days.find("div").css("color", textColor);
        //calendar_content.find(".today").css("background-color", textColor);
        this.#setCalendarStyle()
        // set click listener for each day
        $(".calendar-content > div").on("click", function () {
            var cell = $(this);
            if (!cell.hasClass("blank") && cell.hasClass("enabled-date")) {
                $('.day-selected').removeClass('day-selected');
                cell.addClass('day-selected');
            }
            /* else if(!cell.hasClass("blank") && !cell.hasClass("enabled-date")){
                           alert("Non puoi selezionare una data già passata.");
                       } */
        })
    }

    #getDaysOfMonth() {
        let dayOfMonth = [];
        for (let i = 1; i < this.#getLastMonthDay(this.#year, this.#month) + 1; i++) {
            dayOfMonth.push({
                day: i,
                weekday: this.#days[this.#getDayOfWeek(this.#year, this.#month, i)]
            })
        }
        return dayOfMonth
    }

    #printDays() {
        // svuota i giorni della settimana
        this.#week_days.empty();
        // aggiunge i giorni della settimana
        for (var i = 0; i < 7; i++) {
            // aggiunge un div per ogni giorno
            this.#week_days.append("<div>" + this.#days[i].substring(0, 3) + "</div>")
        }
    }

    #setCalendarStyle() {
        let calendar = $(this.#calendar).css("width", this.#calendar_width + "px");
        calendar.find(".calendar-weekdays, .calendar-content").css("width", this.#calendar_width + "px").find("div").css({
            width: this.#calendar_width / 7 + "px",
            height: this.#calendar_width / 7 + "px",
            "line-height": this.#calendar_width / 7 + "px"
        });
        this.#header.css({
            height: this.#calendar_width / 7 + "px"
        }).find('i[class^="icon-chevron"]').css("line-height", this.#calendar_width / 7 + "px");

        this.#header.css({
            height: this.#calendar_width / 7 + "px"
        }).find('h1').css("line-height", this.#calendar_width / 7 + "px")
    }

    #getLastMonthDay(year, month) {
        return (new Date(year, month, 0)).getDate()
    }

    #getDayOfWeek(year, month, day) {
        // we need -1 because the week starts from monday
        let week_day = (new Date(year, month - 1, day)).getDay() - 1
        if (week_day == -1) {
            week_day = 6
        }
        return week_day
    }

    #isSameDate(date) {
        return this.#getFormattedDate(new Date) == this.#getFormattedDate(date)
    }

    #getFormattedDate(date) {
        return date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate()
    }

    #isOldDate(date) {
        return new Date > date
    }

    #isTooFar(date) {
        return date > new Date().addDays(window.maxFutureDays);
    }

    #getCurrentDate() {
        let date = new Date;
        this.#year = date.getFullYear();
        this.#month = date.getMonth() + 1
    }
}