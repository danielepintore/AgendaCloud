$.validator.addMethod("strong_password", function (value, element) {
    // password must contain at least one uppercase letter, one lowercase and one number
    // the minimum length must be 8 and the maximum length 20
    if (!(/^(?=.*[a-z])(?=.*[A-Z])(.{8,20}$)/.test(value))) {
        return this.optional(element) || false;
    }
    return true;
});

$.validator.addMethod("time", function(value, element) {
    if (value === ""){
        return this.optional(element) || false;
    }
    if (!(/^(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9])(:([0-5]?[0-9]))?$/i.test(value))) {
        return false;
    }
    return true;
}, "Inserisci un orario corretto");

function populateEditModal(employeeId) {
    // clean all the fields
    $("#name-edit").val("");
    $("#surname-edit").val("");
    $("#role-edit").val("");
    $("#username-edit").val("");
    $("#admin-edit").prop("checked", false);
    $("#isActive-edit").prop("checked", true);
    $("#confirmEditEmployeeBtn").prop('disabled', true);
    $.get("/admin/api/employee/get_employees.php", {id: employeeId})
        .done(function (data) {
            data = data[0]
            if (data.userType === 0) {
                data.userType = true;
            } else {
                data.userType = false;
            }
            $("#name-edit").val(data.name);
            $("#surname-edit").val(data.surname);
            $("#role-edit").val(data.role);
            $("#username-edit").val(data.username);
            $("#admin-edit").prop("checked", data.userType);
            $("#isActive-edit").prop("checked", data.isActive);
            $("#confirmEditEmployeeBtn").prop('disabled', false);
        })
        .fail(function () {
            // request failed close modal
            $("#editEmployeeModal").modal("show");
        });
}

function getEmployeesList() {
    $.get("/admin/api/employee/get_employees.php")
        .done(function (data) {
            $('#employeeList').empty();
            if (!data.error && data.length > 0) {
                data.forEach(element => {
                    if (element.userType == 0) {
                        element.userType = "Amministratore";
                    } else {
                        element.userType = "Dipendente";
                    }
                    if (element.isActive == 1) {
                        element.isActive = "Attivo";
                    } else {
                        element.isActive = "Non attivo";
                    }
                    $('#employeeList').append('<a href="#" class="list-group-item list-group-item-action flex-column align-items-start"> ' +
                        '<div class="d-flex w-100 justify-content-between"> ' +
                        '<div><span class="name mb-1 me-1">' + element.name + ' ' + element.surname + '</span><small class="status">(' + element.isActive + ')</small></div>' +
                        '<div class="pointer">' +
                        '<i class="fa-solid fa-pen edit-user ms-2" value="' + element.id + '"></i>' +
                        '<i class="fa-solid fa-clock working-times ms-2" value="' + element.id + '"></i>' +
                        '<i class="fa-solid fa-calendar-day holiday-user ms-2" value="' + element.id + '"></i>' +
                        '<i class="fa-solid fa-trash ms-2 delete-user" value="' + element.id + '"></i></div>' +
                        '</div><div class="d-flex w-100 justify-content-between"><h7 class="mb-1">' + element.role + '</h7></div>' +
                        '<div class="d-flex w-100 justify-content-between">  ' +
                        '<small>Username: ' + element.username + '</small>' +
                        '<small>' + element.userType + '</small>' +
                        '</div></a>');
                });
                /**
                 * Edit user modal
                 */
                $(".edit-user").on("click", function () {
                    let employeeId = $(this).attr("value");
                    $("#confirmEditEmployeeBtn").attr('value', employeeId);
                    populateEditModal(employeeId);
                    // open modal to confirm
                    $("#editEmployeeModal").modal("show");
                });
                /**
                 * Delete user modal
                 */
                $(".delete-user").on("click", function () {
                    let employeeId = $(this).attr("value");
                    $("#confirmDeleteEmployeeBtn").attr('value', employeeId);
                    // open modal to confirm
                    $("#deleteEmployeeModal").modal("show");
                });
                /**
                 * Holiday modal
                 */
                $(".holiday-user").on("click", function () {
                    let employeeId = $(this).attr("value");
                    getHolidaysForUser(employeeId, $("#daySearchHoliday").val())
                    $("#addHolidayButton").attr('value', employeeId);
                    // open modal to confirm
                    $("#viewHolidaysModal").modal("show");
                });
                /**
                 * Working times modal
                 */
                $(".working-times").on("click", function () {
                    let employeeId = $(this).attr("value");
                    generateWorkTimesTables(employeeId);
                    $("#showModalEditWorkingTimeBtn").attr('value', employeeId);
                    $("#showCustomWorkingTimeModal").attr('value', employeeId);
                    // open modal to confirm
                    $("#workTimesModal").modal("show");
                });
            } else if (data.length === 0) {
                // display no appointments message
                $('#employeeList').empty();
                $('#employeeList').append('<div class="card-body">' +
                    '<p class="card-text noEmployees">Non è presente alcun dipendente, aggiungine uno col pulsante qua sopra</p>' +
                    '</div>');
            } else {
                $('#employeeList').empty();
                $('#employeeList').append('<div class="card-body">' +
                    '<p class="card-text noEmployees">C\'è stato un errore, per favore riprova</p>' +
                    '</div>');
            }
        })
        .fail(function () {
            $('#employeeList').empty();
            $('#employeeList').append('<div class="card-body">' +
                '<p class="card-text noEmployees">C\'è stato un errore, per favore riprova</p>' +
                '</div>');
        });
}

/**
 * Function to generate the tables in the set worktimes modal in employee page
 */
function generateWorkTimesTables(employeeId) {
    $.get('/admin/api/employee/get_working_times.php', {employeeId: employeeId})
        .done(function (data) {
            // populate standard worktimes table
            let table = $('#defaultWorkTimesTable');
            table.empty();
            if (!data.error && data["standard"].length > 0) {
                // There aren't errors
                // Generate the table
                const days = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
                // generate the default worktime table
                let tbody;
                let counter = 0;
                table.append('<table class="table text-center"><thead><tr>' +
                    '<th scope="col">Giorno</th>' +
                    '<th scope="col">Ore lavorative</th>' +
                    '<th scope="col">Pausa pranzo</th>' +
                    '</tr></thead><tbody></tbody></table>');
                tbody = table.find("tbody");
                days.forEach(day => {
                    let dayInfo = data["standard"][counter];
                    tbody.append('<tr>' +
                        '<td>' + day + '</td>' +
                        '<td><span class="badge bg-secondary">' + dayInfo.workStartTime + ' - ' + dayInfo.workEndTime + '</span></td>' +
                        '<td><span class="badge bg-secondary">' + dayInfo.breakStartTime + ' - ' + dayInfo.breakEndTime + '</span></td>' +
                        '</tr>');
                    counter++;
                });
            }

            // populate custom worktimes table
            table = $('#customWorkTimesTable');
            table.empty();
            if (!data.error && data["custom"].length > 0) {
                // There aren't errors
                // generate the custom worktime table
                let tbody;
                let counter = 0;
                table.append('<table class="table text-center"><thead><tr>' +
                    '<th scope="col">Data inizio</th>' +
                    '<th scope="col">Data fine</th>' +
                    '<th scope="col">Ore lavorative</th>' +
                    '<th scope="col">Pausa pranzo</th>' +
                    '<th scope="col">Azione</th>' +
                    '</tr></thead><tbody></tbody></table>');
                tbody = table.find("tbody");
                data["custom"].forEach(customTime => {
                    tbody.append('<tr>' +
                        '<td>' + String(customTime.startDate) + '</td>' +
                        '<td>' + String(customTime.endDate) + '</td>' +
                        '<td><span class="badge bg-secondary">' + String(customTime.workStartTime) + ' - ' + String(customTime.workEndTime) + '</span></td>' +
                        '<td><span class="badge bg-secondary">' + String(customTime.breakStartTime) + ' - ' + String(customTime.breakEndTime) + '</span></td>' +
                        '<td><button type="button" value="' + customTime.timeId + '" class="deleteCustomTimeBtn btn btn-outline-danger btn-sm"><i class="fa-solid fa-xmark"></i></button></td>' +
                        '</tr>');
                    counter++;
                });
                $(".deleteCustomTimeBtn").on('click', function (){
                    $.get("/admin/api/employee/delete_custom_worktime.php", {id: $(this).attr('value')})
                        .done(function (data) {
                            if (!data.error) {
                                // Non c'è stato un errore
                                // Reload appointments
                                generateWorkTimesTables($("#showModalEditWorkingTimeBtn").attr("value"));
                            } else {
                                // c'è stato nessun errore non fare nulla
                            }
                        })
                        .fail(function () {
                            // non fare nulla in modo tale da permettere all'utente di riprovare
                        });
                });
            }

        })
        .fail(function () {

        });
}

function deleteHoliday(holidayId) {
    $.get('/admin/api/employee/delete_holiday.php', {holidayId: holidayId})
        .done(function (data) {
                if (!data.error) {
                    // There aren't errors
                    // reload all data
                    getHolidaysForUser($("#addHolidayButton").val(), $("#daySearchHoliday").val());
                }
            }
        )
        .fail(function () {

        });
}

function getHolidaysForUser(employeeId, date) {
    $.get("/admin/api/employee/get_holidays.php", {employeeId: employeeId, date: date})
        .done(function (data) {
            $("#userHolidayTableBody").empty();
            if (!data.error && data.length > 0) {
                $("#infoHolidayUser").addClass("d-none");
                $("#userHolidayTable").removeClass("d-none");
                data.forEach(element => {
                    $("#userHolidayTableBody").append("<tr><td>" + element.date + "</td><td>" + element.startTime +
                        "</td><td>" + element.endTime + '</td><td><button type="button" value="' + element.id + '" class="delete-holiday btn btn-outline-danger btn-sm"><i class="fa-solid fa-xmark"></i></button></td></tr>');
                });
                $(".delete-holiday").on('click', function () {
                    let holidayId = $(this).attr('value');
                    deleteHoliday(holidayId);
                });
            } else if (!data.error && data.length == 0) {
                $("#infoHolidayUser").removeClass("d-none");
                if (date === "") {
                    $("#infoHolidayUser").html("Non ci sono giorni di chiusura per questo servizio");
                } else {
                    $("#infoHolidayUser").html("Non ci sono giorni di chiusura per questo servizio con questa ricerca");
                }
                $("#userHolidayTable").addClass("d-none");
            } else {
                $("#infoHolidayUser").html("C'è stato un errore per favore riprova");
                $("#userHolidayTable").addClass("d-none");
            }
        })
        .fail(function () {
            $("#infoHolidayUser").html("C'è stato un errore per favore riprova");
            $("#infoHolidayUser").removeClass("d-none");
        });
}

$(function () {
    getEmployeesList();

    $("#addEmployeeBtn").on("click", function () {
        $("#addEmployeeModal").modal("show");
    });

    $("#confirmAddEmployeeBtn").on("click", function () {
        $("#addEmployeeForm").validate({
            rules: {
                name: {required: true, minlength: 3},
                surname: {required: true, minlength: 3},
                role: {required: true, minlength: 3},
                username: {required: true, minlength: 3},
                password: {required: true, minlength: 8, strong_password: true},
            },
            messages: {
                name: "Il campo nome deve essere lungo almeno 3 caratteri",
                surname: "Il campo cognome deve essere lungo almeno 3 caratteri",
                role: "Il campo ruolo deve essere lungo almeno 3 caratteri",
                username: "L'username deve essere lungo almeno 3 caratteri",
                password: "La password deve essere lunga tra 8 e 20 caratteri e contenere maiuscole, minuscole e numeri",
            }
        })
        if ($("#addEmployeeForm").valid()) {
            // show loading animation
            let buttonLoader = new ButtonLoader("#confirmAddEmployeeBtn");
            buttonLoader.makeRequest(function () {
                $.post("/admin/api/employee/add_employee.php", {
                    name: $("#name").val(),
                    surname: $("#surname").val(),
                    role: $("#role").val(),
                    username: $("#username").val(),
                    password: $("#password").val(),
                    admin: $("#admin").prop("checked"),
                    isActive: $("#isActive").prop("checked")
                })
                    .done(function (data) {
                        // hide loading animation
                        buttonLoader.hideLoadingAnimation();
                        // Hide add employee modal
                        $("#addEmployeeModal").modal("hide");
                        if (!data.error) {
                            // set success modal data
                            $("#successModalTitle").html("Dipendente aggiunto");
                            $("#successModalMessage").html("Il dipendente è stato aggiunto");
                            // show success modal
                            $("#successModal").modal("show");
                            getEmployeesList();
                            // clean all the fields
                            $("#name").val("");
                            $("#surname").val("");
                            $("#role").val("");
                            $("#username").val("");
                            $("#password").val("");
                            $("#admin").prop("checked", false);
                            $("#isActive").prop("checked", true);
                        } else {
                            // set error modal data
                            $("#errorModalTitle").html("Dipendente non aggiunto");
                            $("#errorModalMessage").html("Il dipendente non è stato aggiunto, per favore riprova, se l'errore persiste contatta l'assistenza");
                            // show confirmation modal
                            $("#errorModal").modal("show");
                        }
                    }).fail(function () {
                    // hide loading animation
                    buttonLoader.hideLoadingAnimation();
                    // Hide add employee modal
                    $("#addEmployeeModal").modal("hide");
                    // set error modal data
                    $("#errorModalTitle").html("Dipendente non aggiunto");
                    $("#errorModalMessage").html("Il dipendente non è stato aggiunto, per favore riprova, se l'errore persiste contatta l'assistenza");
                    // show error modal
                    $("#errorModal").modal("show");
                });
            });
        }
    })

    $("#confirmEditEmployeeBtn").on("click", function () {
        $("#editEmployeeForm").validate({
            rules: {
                name: {required: true, minlength: 3},
                surname: {required: true, minlength: 3},
                role: {required: true, minlength: 3},
                username: {required: true, minlength: 3},
                password: {required: false, strong_password: true}
            },
            messages: {
                name: "Il campo nome deve essere lungo almeno 3 caratteri",
                surname: "Il campo cognome deve essere lungo almeno 3 caratteri",
                role: "Il campo ruolo deve essere lungo almeno 3 caratteri",
                username: "L'username deve essere lungo almeno 3 caratteri",
                password: "La password deve essere lunga tra 8 e 20 caratteri e contenere maiuscole, minuscole e numeri",
            }
        })
        if ($("#editEmployeeForm").valid()) {
            let buttonLoader = new ButtonLoader("#confirmEditEmployeeBtn")
            buttonLoader.makeRequest(function () {
                $.post("/admin/api/employee/update_employee.php", {
                    id: $(this).val(),
                    name: $("#name-edit").val(),
                    surname: $("#surname-edit").val(),
                    role: $("#role-edit").val(),
                    username: $("#username-edit").val(),
                    password: $("#password-edit").val(),
                    admin: $("#admin-edit").prop("checked"),
                    isActive: $("#isActive-edit").prop("checked")
                })
                    .done(function (data) {
                        buttonLoader.hideLoadingAnimation();
                        // Hide edit employee modal
                        $("#editEmployeeModal").modal("hide");
                        if (!data.error) {
                            // set success modal data
                            $("#successModalTitle").html("Informazioni modificate");
                            $("#successModalMessage").html("Le informazioni del dipendente sono state modificate");
                            // show success modal
                            $("#successModal").modal("show");
                            getEmployeesList();
                            // clean all the fields
                        } else {
                            // set error modal data
                            $("#errorModalTitle").html("Informazioni non modificate");
                            $("#errorModalMessage").html("Le informazioni del dipendente non sono state modificate, per favore riprova, se l'errore persiste contatta l'assistenza");
                            // show confirmation modal
                            $("#errorModal").modal("show");
                            // clean all the fields
                        }
                    }).fail(function () {
                    buttonLoader.hideLoadingAnimation();
                    // set error modal data
                    $("#errorModalTitle").html("Informazioni non modificate");
                    $("#errorModalMessage").html("Le informazioni del dipendente non sono state modificate, per favore riprova, se l'errore persiste contatta l'assistenza");
                    // show confirmation modal
                    $("#errorModal").modal("show");
                });
            });
        }
    });

    $("#confirmDeleteEmployeeBtn").on("click", function () {
        $.get("/admin/api/employee/delete_employee.php", {id: $(this).val()})
            .done(function (data) {
                if (!data.error) {
                    getEmployeesList();
                } else {
                    // set error modal data
                    $("#errorModalTitle").html("Dipendente non eliminato");
                    $("#errorModalMessage").html("Non è stato possibile cancellare l'utente, per favore riprova, se il problema persiste contattare l'assistenza");
                    // show confirmation modal
                    $("#errorModal").modal("show");
                }
            })
            .fail(function () {
                // set error modal data
                $("#errorModalTitle").html("Dipendente non eliminato");
                $("#errorModalMessage").html("Non è stato possibile cancellare l'utente, per favore riprova, se il problema persiste contattare l'assistenza");
                // show confirmation modal
                $("#errorModal").modal("show");
            })
    });

    // handler for the add holidays button
    $("#addHolidayButton").on('click', function () {
        $("#addHolidayModal").modal('show');
        $("#confirmAddHolidayButton").attr('value', $(this).attr("value"));
    });

    // implementing the callback for the daySearchHoliday field
    $("#daySearchHoliday").on('change', function () {
        let date = new Date($("#daySearchHoliday").val());
        let today = new Date();
        if (date.getFullYear() >= today.getFullYear() && date.getFullYear() < 2099) {
            getHolidaysForUser($("#addHolidayButton").val(), $("#daySearchHoliday").val());
        } else if ($("#daySearchHoliday").val().length < 10) {
            getHolidaysForUser($("#addHolidayButton").val());
        } else {
            $("#infoHolidayService").removeClass('d-none');
            $("#serviceHolidayTable").addClass('d-none');
            $("#infoHolidayService").html("La data da te inserita non è valida");
        }
    });

    // set handler for the full-day checkbox in add holiday modal
    $("#holidayFullDayCheckBox").on('change', function () {
        if ($(this).prop('checked')) {
            $("#holidayStartTime").prop('disabled', true);
            $("#holidayStartTime").val('');
            $("#holidayEndTime").prop('disabled', true);
            $("#holidayEndTime").val('');
            $("#errorholidayStartTime").addClass('d-none');
            $("#errorholidayEndTime").addClass('d-none');
        } else {
            $("#holidayStartTime").prop('disabled', false);
            $("#holidayEndTime").prop('disabled', false);
            $("#errorholidayStartTime").removeClass('d-none');
            $("#errorholidayEndTime").removeClass('d-none');
        }
    });

    $("#confirmAddHolidayButton").on('click', function () {
        $("#addHolidayForm").validate({
            rules: {
                holidayDate: {required: true, maxlength: 10},
                holidayStartTime: {required: true},
                holidayEndTime: {required: true}
            },
            messages: {
                holidayDate: "È necessario inserire una data corretta",
                holidayStartTime: "È necessario inserire un orario di inizio valido",
                holidayEndTime: "È necessario inserire un orario di fine valido",
            },
            errorPlacement: function (error, element) {
                var placement = $(element).data('error');
                if (placement) {
                    $(placement).append(error);
                } else {
                    error.insertAfter(element);
                }
            }
        })
        if ($("#addHolidayForm").valid()) {
            let buttonLoader = new ButtonLoader("#confirmAddHolidayButton");
            buttonLoader.makeRequest(function () {
                // check if all day ceckbox is checked
                let startTime;
                let endTime;
                if ($("#holidayFullDayCheckBox").prop('checked')) {
                    startTime = "00:00";
                    endTime = "23:59";
                } else {
                    startTime = $("#holidayStartTime").val();
                    endTime = $("#holidayEndTime").val();
                }
                // make request
                $.get("/admin/api/employee/add_holiday.php", {
                    holidayDate: $("#holidayDate").val(),
                    holidayStartTime: startTime,
                    holidayEndTime: endTime,
                    employeeId: $("#confirmAddHolidayButton").val(),
                })
                    .done(function (data) {
                        buttonLoader.hideLoadingAnimation();
                        // Hide add employee modal
                        $("#addHolidayModal").modal("hide");
                        if (!data.error) {
                            // set success modal data
                            $("#successModalTitle").html("Giorno di ferie aggiunto");
                            $("#successModalMessage").html("Il giorno di ferie del dipendente è stato aggiunto");
                            // show success modal
                            $("#successModal").modal("show");
                            // clean all the fields
                            $("#holidayDate").val("");
                            $("#holidayStartTime").val("");
                            $("#holidayEndTime").val("");
                            $("#holidayStartTime").prop('disabled', false);
                            $("#holidayEndTime").prop('disabled', false);
                            $("#holidayFullDayCheckBox").prop('checked', false);
                        } else {
                            // set error modal data
                            $("#errorModalTitle").html("Giorno di ferie non aggiunto");
                            $("#errorModalMessage").html("Il giorno di ferie non è stato aggiunto, per favore riprova, se l'errore persiste contatta l'assistenza");
                            // show confirmation modal
                            $("#errorModal").modal("show");
                        }
                    }).fail(function () {
                    buttonLoader.hideLoadingAnimation();
                    // Hide add employee modal
                    $("#addHolidayModal").modal("hide");
                    // set error modal data
                    $("#errorModalTitle").html("Giorno di ferie non aggiunto");
                    $("#errorModalMessage").html("Il giorno di ferie non è stato aggiunto, per favore riprova, se l'errore persiste contatta l'assistenza");
                    // show confirmation modal
                    $("#errorModal").modal("show");
                });
            });
        }
    });

    $("#showModalEditWorkingTimeBtn").on("click", function () {
        $("#editWorkingTimeButton").attr('value', $(this).attr('value'));
        $("#workTimesModal").modal("hide");
        // open edit modal
        $("#editWorkTimesModal").modal("show");
    });

    $("#showCustomWorkingTimeModal").on("click", function () {
        $("#addCustomWorkingTimeButton").attr('value', $(this).attr('value'));
        $("#workTimesModal").modal("hide");
        // open edit modal
        $("#addCustomWorkTimesModal").modal("show");
    });

    $(".day-selector").on("click", function () {
        $(this).toggleClass("active");
        if (!$("#workTimeAlert").hasClass('d-none')){
            $("#workTimeAlert").addClass('d-none');
        }
    });

    $("#free-day-checkbox").on("click", function () {
        let inputFields = $(this).parent().parent().find('input[type="time"]');
        if ($(this).prop('checked')) {
            inputFields.prop('disabled', true);
            inputFields.val('');
        } else {
            inputFields.prop('disabled', false);
        }
    });

    $("#free-day-custom-checkbox").on("click", function () {
        let inputFields = $(this).parent().parent().find('input[type="time"]');
        if ($(this).prop('checked')) {
            inputFields.prop('disabled', true);
            inputFields.val('');
        } else {
            inputFields.prop('disabled', false);
        }
    });

    $("#editWorkingTimeButton").on("click", function () {
        let buttonLoader = new ButtonLoader("#editWorkingTimeButton", true);
        let daysSelected = $(".day-container .day-selector.active").map(function () {
            return parseInt($(this).attr('value'));
        }).get();
        $("#updateWorkTimeForm").validate({
            rules: {
                startTime: {required: true, time: true},
                endTime: {required: true, time: true},
                startBreak: {required:function () {
                        return $("#workTime-endBreak").val() != ""
                    }, time: true},
                endBreak: {required: function () {
                        return $("#workTime-startBreak").val() != ""
                    }, time: true},
                freeDayCheckbox: {required: false},
            },
            messages: {
                startTime: "Inserisci una data valida",
                endTime: "Inserisci una data valida",
            }
        });
        if (daysSelected.length === 0){
            $("#workTimeAlert").removeClass('d-none');
        }
        if ($("#updateWorkTimeForm").valid() && daysSelected.length > 0){
            let jsonObject = new Object();
            jsonObject.timeType = "standard";
            jsonObject.userId = $("#editWorkingTimeButton").val();
            jsonObject.days = daysSelected;
            jsonObject.freeDay = $("#free-day-checkbox").prop('checked');
            jsonObject.startTime = $("#workTime-startTime").val();
            jsonObject.endTime = $("#workTime-endTime").val();
            jsonObject.startBreak = $("#workTime-startBreak").val();
            jsonObject.endBreak = $("#workTime-endBreak").val();
            buttonLoader.makeRequest(function () {
                $.post("/admin/api/employee/update_working_time.php", {
                    data: JSON.stringify(jsonObject),
                })
                    .done(function (data) {
                        buttonLoader.hideLoadingAnimation();
                        if (!data.error) {
                            // set success modal data
                            $("#successModalTitle").html("Informazioni modificate");
                            $("#successModalMessage").html("L'orario di lavoro del dipendente è stato modificato");
                            // show success modal
                            $("#editWorkTimesModal").modal("hide");
                            $("#successModal").modal("show");
                            // clean all the fields
                            $(".day-container .day-selector.active").removeClass('active');
                        } else {
                            // set error modal data
                            $("#errorModalTitle").html("Informazioni non modificate");
                            $("#errorModalMessage").html("L'orario di lavoro del dipendente non è stato modificato, per favore riprova, se l'errore persiste contatta l'assistenza");
                            // show confirmation modal
                            $("#editWorkTimesModal").modal("hide");
                            $("#errorModal").modal("show");
                        }
                    }).fail(function () {
                    buttonLoader.hideLoadingAnimation();
                    // set error modal data
                    $("#errorModalTitle").html("Informazioni non modificate");
                    $("#errorModalMessage").html("L'orario di lavoro del dipendente non è stato modificato, per favore riprova, se l'errore persiste contatta l'assistenza");
                    $("#editWorkTimesModal").modal("hide");
                    // show confirmation modal
                    $("#errorModal").modal("show");
                });
            });
        }
    });

    $("#addCustomWorkingTimeButton").on("click", function () {
        let buttonLoader = new ButtonLoader("#addCustomWorkingTimeButton", true);
        $("#addCustomWorkTimeForm").validate({
            rules: {
                startCustomDay: {required: true, date: true},
                endCustomDay: {required: true, date: true},
                customStartTime: {time: true},
                customEndTime: {time: true},
                customStartBreak: {required: function () {
                        return $("#workTime-customEndBreak").val() != ""
                    } ,time: true},
                customEndBreak: {required: function () {
                        return $("#workTime-customStartBreak").val() != ""
                    }, time: true},
                freeDayCheckbox: {required: false},
            },
            messages: {
                startCustomDay: "Inserisci una data valida",
                endCustomDay: "Inserisci una data valida",
                customStartBreak: "Inserisci una data valida",
                customEndBreak: "Inserisci una data valida"
            }
        });
        let startDate = new Date($("#workTime-startCustomDay").val());
        startDate.setHours(0);
        startDate.setMinutes(0);
        startDate.setSeconds(0);
        startDate.setMilliseconds(0);
        let endDate = new Date($("#workTime-endCustomDay").val());
        endDate.setHours(0);
        endDate.setMinutes(0);
        endDate.setSeconds(0);
        endDate.setMilliseconds(0);
        let today = new Date();
        today.setHours(0);
        today.setMinutes(0);
        today.setSeconds(0);
        today.setMilliseconds(0);
        if ($("#addCustomWorkTimeForm").valid() && startDate >= today && startDate <= endDate){
            let jsonObject = new Object();
            jsonObject.timeType = "custom";
            jsonObject.userId = $("#addCustomWorkingTimeButton").val();
            jsonObject.startDay = $("#workTime-startCustomDay").val();
            jsonObject.endDay = $("#workTime-endCustomDay").val();
            jsonObject.freeDay = $("#free-day-custom-checkbox").prop('checked');
            jsonObject.startTime = $("#workTime-customStartTime").val();
            jsonObject.endTime = $("#workTime-customEndTime").val();
            jsonObject.startBreak = $("#workTime-customStartBreak").val();
            jsonObject.endBreak = $("#workTime-customEndBreak").val();
            console.log(JSON.stringify(jsonObject));
            buttonLoader.makeRequest(function () {
                $.post("/admin/api/employee/update_working_time.php", {
                    data: JSON.stringify(jsonObject),
                })
                    .done(function (data) {
                        buttonLoader.hideLoadingAnimation();
                        if (!data.error) {
                            // set success modal data
                            $("#successModalTitle").html("Informazioni aggiunte");
                            $("#successModalMessage").html("L'orario di lavoro del dipendente è stato aggiunto");
                            // show success modal
                            $("#addCustomWorkTimesModal").modal("hide");
                            $("#successModal").modal("show");
                            // clean all the fields
                            $(".day-container .day-selector.active").removeClass('active');
                        } else {
                            // set error modal data
                            $("#errorModalTitle").html("Informazioni non aggiunte");
                            $("#errorModalMessage").html("L'orario di lavoro del dipendente non è stato aggiunto, per favore riprova, se l'errore persiste contatta l'assistenza");
                            // show confirmation modal
                            $("#addCustomWorkTimesModal").modal("hide");
                            $("#errorModal").modal("show");
                        }
                    }).fail(function () {
                    buttonLoader.hideLoadingAnimation();
                    // set error modal data
                    $("#errorModalTitle").html("Informazioni non aggiunte");
                    $("#errorModalMessage").html("L'orario di lavoro del dipendente non è stato aggiunto, per favore riprova, se l'errore persiste contatta l'assistenza");
                    $("#addCustomWorkTimesModal").modal("hide");
                    // show confirmation modal
                    $("#errorModal").modal("show");
                });
            });
        }
    });
})