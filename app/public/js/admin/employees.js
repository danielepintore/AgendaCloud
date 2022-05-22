$.validator.addMethod("strong_password", function (value, element) {
    // password must contain at least one uppercase letter, one lowercase and one number
    // the minimum length must be 8 and the maximum length 20
    if (!(/^(?=.*[a-z])(?=.*[A-Z])(.{8,20}$)/.test(value))) {
        return this.optional(element) || false;
    }
    return true;
});

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
                    $("#EditWorkingTimeBtn").attr('value', employeeId);
                    // open modal to confirm
                    $("#editWorkTimesModal").modal("show");
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
            $("#loadingCircleAddEmployee").removeClass("d-none");
            // make request
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
                    $("#loadingCircleAddEmployee").addClass("d-none");
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
                $("#loadingCircleAddEmployee").addClass("d-none");
                // Hide add employee modal
                $("#addEmployeeModal").modal("hide");
                // set error modal data
                $("#errorModalTitle").html("Dipendente non aggiunto");
                $("#errorModalMessage").html("Il dipendente non è stato aggiunto, per favore riprova, se l'errore persiste contatta l'assistenza");
                // show error modal
                $("#errorModal").modal("show");
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
            // show loading animation
            $("#loadingCircleEditEmployee").removeClass("d-none");
            // make request
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
                    // hide loading animation
                    $("#loadingCircleEditEmployee").addClass("d-none");
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
                // hide loading animation
                $("#loadingCircleEditEmployee").addClass("d-none");
                // set error modal data
                $("#errorModalTitle").html("Informazioni non modificate");
                $("#errorModalMessage").html("Le informazioni del dipendente non sono state modificate, per favore riprova, se l'errore persiste contatta l'assistenza");
                // show confirmation modal
                $("#errorModal").modal("show");
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
            // show loading animation
            $("#loadingCircleAddHoliday").removeClass("d-none");
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
                    // hide loading animation
                    $("#loadingCircleAddHoliday").addClass("d-none");
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
                // hide loading animation
                $("#loadingCircleAddHoliday").addClass("d-none");
                // Hide add employee modal
                $("#addHolidayModal").modal("hide");
                // set error modal data
                $("#errorModalTitle").html("Giorno di ferie non aggiunto");
                $("#errorModalMessage").html("Il giorno di ferie non è stato aggiunto, per favore riprova, se l'errore persiste contatta l'assistenza");
                // show confirmation modal
                $("#errorModal").modal("show");
            });
        }
    });
})