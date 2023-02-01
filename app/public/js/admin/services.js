let lastCustomWorkTimeRequest;
// donetyping method that triggers every time the user stop to type in a textbox
$.fn.extend({
    donetyping: function (callback, timeout) {
        timeout = timeout || 1e3; // 1 second default timeout
        var timeoutReference,
            doneTyping = function (el) {
                if (!timeoutReference) return;
                timeoutReference = null;
                callback.call(el);
            };
        return this.each(function (i, el) {
            var $el = $(el);
            // Chrome Fix (Use keyup over keypress to detect backspace)
            $el.is(':input') && $el.on('keyup keydown paste', function (e) {
                // This catches the backspace button in chrome, but also prevents
                // the event from triggering too preemptively. Without this line,
                // using tab/shift+tab will make the focused element fire the callback
                if (e.type == 'keyup' && e.keyCode != 8 || (e.metaKey && e.keyCode != 8) || (e.ctrlKey && e.keyCode != 8)) return;
                // Check if timeout has been set. If it has, "reset" the clock and
                // start over again.
                if (timeoutReference) clearTimeout(timeoutReference);
                timeoutReference = setTimeout(function () {
                    // if we made it here, our timeout has elapsed. Fire the
                    // callback
                    doneTyping(el);
                }, timeout);
            }).on('blur', function () {
                // If we can, fire the event since we're leaving the field
                doneTyping(el);
            });
        });
    }
});
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

$.validator.addMethod("timeLessThan", function (value, element, params) {
    if (value === "") {
        return this.optional(element)
    }
    if (!/Invalid|NaN/.test(new Date("2000T" + value))) {
        let isLess = false;
        for (let i = 0; i < params.length; i++) {
            if ($(params[i]).val() === "") {
                isLess = true;
                continue;
            }
            isLess = new Date("2000T" + value) < new Date("2000T" + $(params[i]).val());
            if (!isLess) {
                return isLess;
            }
        }
        return isLess;
    } else {
        return this.optional(element);
    }
});

$.validator.addMethod("dateEqualOrGreaterThan", function (value, element, params) {
    if (value === "") {
        return this.optional(element)
    }
    if (!/Invalid|NaN/.test(new Date(value + "T00:00:00.000Z"))) {
        let isGreater = false;
        for (let i = 0; i < params.length; i++) {
            if ($(params[i]).val() === "") {
                isGreater = true;
                continue;
            }
            isGreater = new Date(value + "T00:00:00.000Z") >= new Date($(params[i]).val() + "T00:00:00.000Z");
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
 * Gets the list of employees with a specific field that says if he/she can be added or removed, also it allows to
 * pass a name to perform a search
 * @param serviceId
 * @param name
 */
function getEmployeesToAdd(serviceId, name) {
    $.get("/admin/api/service/get_employees_to_add.php", {id: serviceId, name: name})
        .done(function (data) {
            if (!data.error && data.length > 0) {
                $("#editEmployeesTableContent").empty();
                $("#editEmployeesTable").removeClass("d-none");
                // generate the table
                data.forEach(element => {
                    if (element.available_action == "delete") {
                        $("#editEmployeesTableContent").append('<tr value="' + element.id + '"><td>' + element.name + '</td><td>' + element.surname + '</td><td><button type="button" value="' + element.id + '" class="employeeBtnRemove btn btn-outline-danger btn-sm"><i class="fa-solid fa-xmark"></i></button></td></tr>');
                    } else {
                        $("#editEmployeesTableContent").append('<tr value="' + element.id + '"><td>' + element.name + '</td><td>' + element.surname + '</td><td><button type="button" value="' + element.id + '" class="employeeBtnAdd btn btn-outline-success btn-sm"><i class="fa-solid fa-plus"></i></button></td></tr>');
                    }
                });
                // add listeners
                /**
                 * Remove employee lister
                 */
                $(".employeeBtnRemove").on('click', function () {
                    $.get('/admin/api/service/remove_employee_to_service.php', {
                        serviceId: $('#confirmAddEmployeeBtn').val(),
                        employeeId: $(this).val()
                    })
                        .done(function (data) {
                            if (!data.error) {
                                getEmployeesToAdd($('#confirmAddEmployeeBtn').val(), $('#employeeNameSearch').val());
                                getServicesList();
                            } else {
                                // if there is an error do nothing
                            }
                        })
                        .fail(function (data) {
                            // if there is an error do nothing
                        })
                });

                /**
                 * Add employee listener
                 */
                $(".employeeBtnAdd").on('click', function () {
                    $.get('/admin/api/service/add_employee_to_service.php', {
                        serviceId: $('#confirmAddEmployeeBtn').val(),
                        employeeId: $(this).val()
                    })
                        .done(function (data) {
                            if (!data.error) {
                                getEmployeesToAdd($('#confirmAddEmployeeBtn').val(), $('#employeeNameSearch').val());
                                getServicesList();
                            } else {
                                // if there is an error do nothing
                            }
                        })
                        .fail(function (data) {
                            // if there is an error do nothing
                        })
                });
            } else if (data.length == 0) {
                $("#editEmployeesTable").addClass("d-none");
                $("#employeesToAddInfo").html("Non ci sono dipendenti con questo nome");
            }
        })
        .fail(function (data) {
            $("#editEmployeesTable").addClass("d-none");
            $("#employeesToAddInfo").html("Si è verificato un errore");
        });
}

/**
 * Gets the list of employees for a service
 * @param serviceId
 */
function getEmployeesList(serviceId) {
    $.get("/admin/api/service/get_employees.php", {id: serviceId})
        .done(function (data) {
            if (!data.error && data.length > 0) {
                $("#employeesTableContent").empty();
                $("#employeesTable").removeClass("d-none");
                $("#employeesInfo").addClass("d-none");
                data.forEach(element => {
                    $("#employeesTableContent").append('<tr><td>' + element.name + '</td><td>' + element.surname + '</td></tr>');
                });
            } else if (data.length === 0) {
                $("#employeesTable").addClass("d-none");
                $("#employeesInfo").removeClass("d-none");
                $("#employeesInfo").html("Non ci sono dipendenti che offrono questo servizio")
            }

        })
        .fail(function () {

        });
}

/**
 * @param serviceId
 * Fetch service info and populate the edit modal
 */
function populateEditModal(serviceId) {
    $("#service-name-edit").val("");
    $("#service-duration-edit").val("");
    $("#service-cost-edit").val("");
    $("#service-waitTime-edit").val("");
    $("#service-bookableUntilTime-edit").val("");
    $("#service-description-edit").val("");
    $("#service-active-edit").prop("checked", true);
    $("#service-slot-supervisor-edit").prop("checked", false);
    $("#editServiceBtn").attr('disabled', true);
    $.get("/admin/api/service/get_services.php", {serviceId: serviceId})
        .done(function (data) {
            data = data[0]
            $("#service-name-edit").val(data.name);
            $("#service-duration-edit").val(data.duration);
            $("#service-cost-edit").val(data.cost);
            $("#service-waitTime-edit").val(data.waitTime);
            $("#service-bookableUntilTime-edit").val(data.bookableUntil);
            $("#service-description-edit").val(data.description);
            $("#service-active-edit").prop("checked", data.isActive);
            $("#service-slot-supervisor-edit").prop("checked", data.needTimeSupervision);
            $("#editServiceBtn").attr('disabled', false);
        })
        .fail(function () {
            // set error modal data
            $("#errorModalTitle").html("Servizio non caricato");
            $("#errorModalMessage").html("Non è stato possibile recuperare le informazioni del servizio, per favore riprova");
            // show confirmation modal
            $("#errorModal").modal("show");
        });
}

/**
 * Gets the list of all services and diplay them
 */
function getServicesList() {
    $.get("/admin/api/service/get_services.php")
        .done(function (data) {
            $('#servicesList').empty();
            if (!data.error && data.length > 0) {
                data.forEach(element => {
                    if (element.isActive == 1) {
                        element.isActive = "Attivo";
                    } else {
                        element.isActive = "Non attivo";
                    }
                    $('#servicesList').append('<a href="#" class="list-group-item list-group-item-action flex-column align-items-start"> ' +
                        '<div class="d-flex w-100 justify-content-between"> ' +
                        '<div><span class="name mb-1 me-1">' + element.name + '</span><small class="status">(' + element.isActive + ')</small></div><div class="pointer">' +
                        '<i class="fa-solid fa-pen edit-service ms-2" value="' + element.id + '"></i>' +
                        '<i class="fa-solid fa-clock working-times-service ms-2" value="' + element.id + '"></i>' +
                        '<i class="fa-solid fa-user-group view-employees ms-2" value="' + element.id + '"></i>' +
                        '<i class="fa-solid fa-trash delete-service ms-2" value="' + element.id + '"></i></div>' +
                        '</div><div class="d-flex w-100 justify-content-between"><h7 class="mb-1">' + element.cost + '€</h7></div>' +
                        '<div class="d-flex w-100 justify-content-between">  ' +
                        '<small>Durata: ' + element.duration + ' minuti</small>' +
                        '<small>Numero dipendenti: ' + element.employeesNumber + '</small>' +
                        '</div></a>');
                });

                // Service listeners
                /**
                 * Edit service modal
                 */
                $(".edit-service").on("click", function () {
                    let serviceId = $(this).attr("value");
                    populateEditModal(serviceId);
                    $("#editServiceBtn").attr('value', serviceId);
                    // open modal to confirm
                    $("#editServiceModal").modal("show");
                });
                /**
                 * View employee modal
                 */
                $(".view-employees").on("click", function () {
                    let serviceId = $(this).attr("value");
                    getEmployeesList(serviceId);
                    $("#editEmployeesBtn").attr('value', serviceId);
                    // open modal to confirm
                    $("#showEmployeesModal").modal("show");
                });
                /**
                 * Delete service modal
                 */
                $(".delete-service").on("click", function () {
                    let serviceId = $(this).attr("value");
                    $("#confirmDeleteServiceBtn").attr('value', serviceId);
                    // open modal to confirm
                    $("#deleteServiceModal").modal("show");
                });
                /**
                 * WorkingTime modal
                 */
                $(".working-times-service").on("click", function () {
                    let serviceId = $(this).attr("value");
                    generateServiceWorkTimesTable(serviceId);
                    $("#showModalEditServiceWorkingTimeBtn").attr('value', serviceId);
                    $("#showModalCustomAddServiceWorkingTimeBtn").attr('value', serviceId);
                    // open modal to confirm
                    $("#workingTimesServiceModal").modal("show");
                });
            } else if (data.length === 0) {
                // display no appointments message
                $('#servicesList').append('');
                $('#servicesList').append('<div class="card-body">' +
                    '<p class="card-text noServices">Non sono presenti dei servizi, creane uno con il pulsante qua sopra</p>' +
                    '</div>');
            } else {
                $('#servicesList').empty();
                $('#servicesList').append('<div class="card-body">' +
                    '<p class="card-text noServices">C\'è stato un errore, per favore riprova</p>' +
                    '</div>');
            }
        })
        .fail(function () {
            $('#servicesList').empty();
            $('#servicesList').append('<div class="card-body">' +
                '<p class="card-text noServices">C\'è stato un errore, per favore riprova</p>' +
                '</div>');
        });
}

/**
 * This function generates the worktime table, the table that contains all the information of the working times of a service
 * @param serviceId
 */
function generateServiceWorkTimesTable(serviceId) {
    $.get('/admin/api/service/get_working_times.php', {serviceId: serviceId})
        .done(function (data) {
            // populate standard worktimes table
            let table = $('#defaultServiceWorkTimesTable');
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
            table = $('#customServiceWorkTimesTable');
            table.empty();
            if (!data.error && data["custom"].length > 0) {
                // There aren't errors
                // generate the custom worktime table
                let tbody;
                let counter = 0;
                table.append('<table class="table text-center hover" id="customServiceWorkTimeDataTable"><thead><tr>' +
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
                        '<td><button type="button" value="' + customTime.timeId + '" class="deleteCustomServiceWorkTimeBtn btn btn-outline-danger btn-sm"><i class="fa-solid fa-xmark"></i></button></td>' +
                        '</tr>');
                    counter++;
                });
                $(".deleteCustomServiceWorkTimeBtn").on('click', function () {
                    $.get("/admin/api/service/delete_custom_worktime.php", {id: $(this).attr('value')})
                        .done(function (data) {
                            if (!data.error) {
                                // Non c'è stato un errore
                                // Reload appointments
                                generateServiceWorkTimesTable($("#showModalEditServiceWorkingTimeBtn").attr("value"));
                            } else {
                                // c'è stato nessun errore non fare nulla
                            }
                        })
                        .fail(function () {
                            // non fare nulla in modo tale da permettere all'utente di riprovare
                        });
                });
                $("#customServiceWorkTimeDataTable").DataTable({
                    language: {
                        url: "/datatables/lang/ita.json"
                    },
                    columnDefs: [
                        {
                            targets: "_all",
                            className: 'dt-center'
                        }
                    ],
                    "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Tutti"]]
                });
            }

        })
        .fail(function () {

        });
}

/**
 * Main function, it's executed when the DOM is loaded, it includes all the on-xxx event type
 */
$(function () {
    getServicesList();

    $("#addServiceBtn").on("click", function () {
        $("#addServiceModal").modal("show");
    });

    // Set up validation scheme with Jquery validate plugin
    $("#confirmAddServiceBtn").on("click", function () {
        $("#addServiceForm").validate({
            rules: {
                name: {required: true, minlength: 3},
                duration: {required: true, min: 1, step: 1},
                startTime: {required: true},
                endTime: {required: true},
                cost: {required: true, min: 1, step: 1},
                waitTime: {required: true, min: 0, step: 1},
                bookableUntil: {required: true, min: 0, step: 1},
                description: {required: false},
            },
            messages: {
                name: "Il campo nome deve essere lungo almeno 3 caratteri",
                duration: "È necessario inserire una durata del servizio valida",
                startTime: "È necessario inserire un orario di apertura valido",
                endTime: "È necessario inserire un orario di chiusura valido",
                cost: "È necessario inserire un prezzo del servizio valido, deve essere maggiore di 1",
                waitTime: "Tempo di attesa tra appuntamenti non valido",
                bookableUntil: "Tempo chiusura prenotazioni dall'orario dello slot non valido",
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
        if ($("#addServiceForm").valid()) {
            let buttonLoader = new ButtonLoader("#confirmAddServiceBtn");
            buttonLoader.makeRequest(function () {
                $.post("/admin/api/service/add_service.php", {
                    serviceName: $("#service-name").val(),
                    serviceDuration: $("#service-duration").val(),
                    serviceStartTime: $("#service-startTime").val(),
                    serviceEndTime: $("#service-endTime").val(),
                    serviceCost: $("#service-cost").val(),
                    serviceWaitTime: $("#service-waitTime").val(),
                    bookableUntil: $("#service-bookableUntilTime").val(),
                    serviceDescription: $("#service-description").val(),
                    serviceActive: $("#service-active").prop("checked")
                })
                    .done(function (data) {
                        buttonLoader.hideLoadingAnimation();
                        // Hide add employee modal
                        $("#addServiceModal").modal("hide");
                        if (!data.error) {
                            // set success modal data
                            $("#successModalTitle").html("Servizio aggiunto");
                            $("#successModalMessage").html("Il servizio è stato aggiunto");
                            // show success modal
                            $("#successModal").modal("show");
                            getServicesList();
                            // clean all the fields
                            $("#service-name").val("");
                            $("#service-duration").val("");
                            $("#service-startTime").val("");
                            $("#service-endTime").val("");
                            $("#service-cost").val("");
                            $("#service-waitTime").val("");
                            $("#service-bookableUntilTime").val("");
                            $("#service-description").val("");
                            $("#service-active").prop("checked", true);
                        } else {
                            // set error modal data
                            $("#errorModalTitle").html("Servizio non aggiunto");
                            $("#errorModalMessage").html("Il servizio non è stato aggiunto, per favore riprova, se l'errore persiste contatta l'assistenza");
                            // show confirmation modal
                            $("#errorModal").modal("show");
                        }
                    }).fail(function () {
                    buttonLoader.hideLoadingAnimation();
                    // Hide add employee modal
                    $("#addServiceModal").modal("hide");
                    // set error modal data
                    $("#errorModalTitle").html("Servizio non aggiunto");
                    $("#errorModalMessage").html("Il servizio non è stato aggiunto, per favore riprova, se l'errore persiste contatta l'assistenza");
                    // show confirmation modal
                    $("#errorModal").modal("show");
                });
            });
        }
    });

    // Set up validation scheme with Jquery validate plugin
    $("#editServiceBtn").on("click", function () {
        $("#editServiceForm").validate({
            rules: {
                name: {required: true, minlength: 3},
                duration: {required: true, min: 1, step: 1},
                cost: {required: true, min: 1, step: 1},
                waitTime: {required: true, min: 0, step: 1},
                bookableUntil: {required: true, min: 0, step: 1},
                description: {required: false},
            },
            messages: {
                name: "Il campo nome deve essere lungo almeno 3 caratteri",
                duration: "È necessario inserire una durata del servizio valida",
                cost: "È necessario inserire un prezzo del servizio valido, deve essere maggiore di 1",
                waitTime: "Tempo di attesa tra appuntamenti non valido",
                bookableUntil: "Tempo chiusura prenotazioni dall'orario dello slot non valido",
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
        if ($("#editServiceForm").valid()) {
            let buttonLoader = new ButtonLoader("#editServiceBtn");
            let id = $(this).val();
            buttonLoader.makeRequest(function () {
                $.post("/admin/api/service/update_service.php", {
                    id: id,
                    serviceName: $("#service-name-edit").val(),
                    serviceDuration: $("#service-duration-edit").val(),
                    serviceCost: $("#service-cost-edit").val(),
                    serviceWaitTime: $("#service-waitTime-edit").val(),
                    bookableUntil: $("#service-bookableUntilTime-edit").val(),
                    serviceDescription: $("#service-description-edit").val(),
                    serviceActive: $("#service-active-edit").prop("checked")
                })
                    .done(function (data) {
                        buttonLoader.hideLoadingAnimation();
                        // Hide add employee modal
                        $("#editServiceModal").modal("hide");
                        if (!data.error) {
                            // set success modal data
                            $("#successModalTitle").html("Servizio modificato");
                            $("#successModalMessage").html("Il servizio è stato modificato");
                            // show success modal
                            $("#successModal").modal("show");
                            getServicesList()
                        } else {
                            // set error modal data
                            $("#errorModalTitle").html("Servizio non modificato");
                            $("#errorModalMessage").html("Il servizio non è stato modificato, per favore riprova, se l'errore persiste contatta l'assistenza");
                            // show confirmation modal
                            $("#errorModal").modal("show");
                        }
                    }).fail(function () {
                    buttonLoader.hideLoadingAnimation();
                    // Hide add employee modal
                    $("#editServiceModal").modal("hide");
                    // set error modal data
                    $("#errorModalTitle").html("Servizio non modificato");
                    $("#errorModalMessage").html("Il servizio non è stato modificato, per favore riprova, se l'errore persiste contatta l'assistenza");
                    // show confirmation modal
                    $("#errorModal").modal("show");
                });
            });
        }
    });

    $("#confirmDeleteServiceBtn").on("click", function () {
        $.get("/admin/api/service/delete_service.php", {id: $(this).val()})
            .done(function (data) {
                if (!data.error) {
                    getServicesList();
                } else {
                    // set error modal data
                    $("#errorModalTitle").html("Servizio non cancellato");
                    $("#errorModalMessage").html("Il servizio non è stato cancellato, per favore riprova, se l'errore persiste contatta l'assistenza");
                    // show confirmation modal
                    $("#errorModal").modal("show");
                }
            })
            .fail(function () {
                // set error modal data
                $("#errorModalTitle").html("Servizio non cancellato");
                $("#errorModalMessage").html("Il servizio non è stato cancellato, per favore riprova, se l'errore persiste contatta l'assistenza");
                // show confirmation modal
                $("#errorModal").modal("show");
            })
    });

    // handler for the add employee button
    $("#editEmployeesBtn").on('click', function () {
        $("#editEmployeesModal").modal('show');
        $("#confirmAddEmployeeBtn").attr('value', $(this).attr("value"));
        getEmployeesToAdd($("#confirmAddEmployeeBtn").val(), $("#employeeNameSearch").val());
    });

    // implementing the callback for the donetyping function
    $("#employeeNameSearch").donetyping(function () {
        getEmployeesToAdd($("#confirmAddEmployeeBtn").val(), $("#employeeNameSearch").val());
    });


    $("#showModalEditServiceWorkingTimeBtn").on("click", function () {
        $("#editServiceWorkingTimeButton").attr('value', $(this).attr('value'));
        $("#workingTimesServiceModal").modal("hide");
        // open edit modal
        $("#editServiceWorkTimesModal").modal("show");
    });

    $("#showModalCustomAddServiceWorkingTimeBtn").on("click", function () {
        $("#addCustomServiceWorkingTimeButton").attr('value', $(this).attr('value'));
        $("#workingTimesServiceModal").modal("hide");
        // open edit modal
        $("#addCustomServiceWorkTimesModal").modal("show");
    });

    $(".day-selector").on("click", function () {
        $(this).toggleClass("active");
        if (!$("#workTimeServiceAlert").hasClass('d-none')) {
            $("#workTimeServiceAlert").addClass('d-none');
        }
    });

    $("#close-day-checkbox").on("click", function () {
        let inputFields = $(this).parent().parent().find('input[type="time"]');
        if ($(this).prop('checked')) {
            inputFields.prop('disabled', true);
            inputFields.val('');
        } else {
            inputFields.prop('disabled', false);
        }
    });

    $("#close-day-custom-checkbox").on("click", function () {
        let inputFields = $(this).parent().parent().find('input[type="time"]');
        if ($(this).prop('checked')) {
            inputFields.prop('disabled', true);
            inputFields.val('');
        } else {
            inputFields.prop('disabled', false);
        }
    });

    $("#editServiceWorkingTimeButton").on("click", function () {
        let buttonLoader = new ButtonLoader("#editServiceWorkingTimeButton", true);
        let daysSelected = $(".day-container .day-selector.active").map(function () {
            return parseInt($(this).attr('value'));
        }).get();
        $("#updateServiceWorkTimeForm").validate({
            rules: {
                serviceStartTime: {required: true, time: true},
                serviceEndTime: {required: true, time: true, timeGreaterThan: ["#workTime-serviceStartTime"]},
                serviceStartBreak: {
                    required: function () {
                        return $("#workTime-serviceEndBreak").val() != ""
                    },
                    time: true,
                    timeGreaterThan: ["#workTime-serviceStartTime"],
                    timeLessThan: ["#workTime-serviceEndTime", "#workTime-serviceEndBreak"]
                },
                serviceEndBreak: {
                    required: function () {
                        return $("#workTime-serviceStartBreak").val() != ""
                    },
                    time: true,
                    timeGreaterThan: ["#workTime-serviceStartBreak"],
                    timeLessThan: ["#workTime-serviceEndTime"]
                },
                closeDayCheckbox: {required: false},
            },
            messages: {
                serviceStartTime: "Inserisci un'ora valida",
                serviceEndTime: "Inserisci un'ora valida",
                serviceStartBreak: "Inserisci un'ora valida",
                serviceEndBreak: "Inserisci un'ora valida",
            }
        });
        if (daysSelected.length === 0) {
            $("#workTimeServiceAlert").removeClass('d-none');
        }

        if ($("#updateServiceWorkTimeForm").valid() && daysSelected.length > 0) {
            let jsonObject = new Object();
            jsonObject.timeType = "standard";
            jsonObject.serviceId = $("#editServiceWorkingTimeButton").val();
            jsonObject.days = daysSelected;
            jsonObject.freeDay = $("#close-day-checkbox").prop('checked');
            jsonObject.startTime = $("#workTime-serviceStartTime").val();
            jsonObject.endTime = $("#workTime-serviceEndTime").val();
            jsonObject.startBreak = $("#workTime-serviceStartBreak").val();
            jsonObject.endBreak = $("#workTime-serviceEndBreak").val();
            buttonLoader.makeRequest(function () {
                $.post("/admin/api/service/update_working_time.php", {
                    data: JSON.stringify(jsonObject),
                })
                    .done(function (data) {
                        buttonLoader.hideLoadingAnimation();
                        if (!data.error) {
                            // set success modal data
                            $("#successModalTitle").html("Informazioni modificate");
                            $("#successModalMessage").html("L'orario di lavoro del servizio è stato modificato");
                            // show success modal
                            $("#editServiceWorkTimesModal").modal("hide");
                            $("#successModal").modal("show");
                            // clean all the fields
                            $(".day-container .day-selector.active").removeClass('active');
                        } else {
                            // set error modal data
                            $("#errorModalTitle").html("Informazioni non modificate");
                            $("#errorModalMessage").html("L'orario di lavoro del servizio non è stato modificato, per favore riprova, se l'errore persiste contatta l'assistenza");
                            // show confirmation modal
                            $("#editServiceWorkTimesModal").modal("hide");
                            $("#errorModal").modal("show");
                        }
                    }).fail(function () {
                    buttonLoader.hideLoadingAnimation();
                    // set error modal data
                    $("#errorModalTitle").html("Informazioni non modificate");
                    $("#errorModalMessage").html("L'orario di lavoro del servizio non è stato modificato, per favore riprova, se l'errore persiste contatta l'assistenza");
                    $("#editServiceWorkTimesModal").modal("hide");
                    // show confirmation modal
                    $("#errorModal").modal("show");
                });
            });
        }
    });

    $("#addCustomServiceWorkingTimeButton").on("click", function () {
        let buttonLoader = new ButtonLoader("#addCustomServiceWorkingTimeButton", true);
        $("#addCustomServiceWorkTimeForm").validate({
            rules: {
                startServiceCustomDay: {required: true, date: true},
                endServiceCustomDay: {
                    required: true,
                    date: true,
                    dateEqualOrGreaterThan: ["#workTime-startServiceCustomDay"]
                },
                serviceCustomStartTime: {time: true},
                serviceCustomEndTime: {time: true, timeGreaterThan: ["#workTime-customServiceStartTime"]},
                serviceCustomStartBreak: {
                    required: function () {
                        return $("#workTime-customServiceEndBreak").val() != ""
                    },
                    time: true,
                    timeGreaterThan: ["#workTime-customServiceStartTime"],
                    timeLessThan: ["#workTime-customServiceEndTime", "#workTime-customServiceEndBreak"]
                },
                serviceCustomEndBreak: {
                    required: function () {
                        return $("#workTime-customServiceStartBreak").val() != ""
                    },
                    time: true,
                    timeGreaterThan: ["#workTime-customServiceStartBreak"],
                    timeLessThan: ["#workTime-customServiceEndTime"]
                },
                closeDayCustomCheckbox: {required: false},
            },
            messages: {
                startServiceCustomDay: "Inserisci una data valida",
                endServiceCustomDay: "Inserisci una data valida",
                serviceCustomStartTime: "Inserisci un'ora valida",
                serviceCustomEndTime: "Inserisci un'ora valida",
                serviceCustomStartBreak: "Inserisci un'ora valida",
                serviceCustomEndBreak: "Inserisci un'ora valida"
            }
        });
        let startDate = new Date($("#workTime-startServiceCustomDay").val() + "T00:00:00.000Z");
        let today = new Date();
        today.setHours(0);
        today.setMinutes(0);
        today.setSeconds(0);
        today.setMilliseconds(0);
        if ($("#addCustomServiceWorkTimeForm").valid() && startDate >= today) {
            let jsonObject = new Object();
            jsonObject.timeType = "custom";
            jsonObject.serviceId = $("#addCustomServiceWorkingTimeButton").val();
            jsonObject.startDay = $("#workTime-startServiceCustomDay").val();
            jsonObject.endDay = $("#workTime-endServiceCustomDay").val();
            jsonObject.freeDay = $("#close-day-custom-checkbox").prop('checked');
            jsonObject.startTime = $("#workTime-customServiceStartTime").val();
            jsonObject.endTime = $("#workTime-customServiceEndTime").val();
            jsonObject.startBreak = $("#workTime-customServiceStartBreak").val();
            jsonObject.endBreak = $("#workTime-customServiceEndBreak").val();
            buttonLoader.makeRequest(function () {
                $.post("/admin/api/service/update_working_time.php", {
                    data: JSON.stringify(jsonObject),
                })
                    .done(function (data) {
                        buttonLoader.hideLoadingAnimation();
                        if (data.warning === "conflict") {
                            lastCustomWorkTimeRequest = jsonObject;
                            // show success modal to ask if the user wants to overwrite the current rule
                            $("#conflictWorkTimesModalTitle").html("È stato rilevato un conflitto");
                            $("#conflictWorkTimesModalMessage").html("La giornata da te inserita crea un conflitto con quelle già presenti nel sistema, vuoi cancellare le altre ed inserire questa?");
                            // show success modal
                            $("#addCustomServiceWorkTimesModal").modal("hide");
                            $("#conflictWorkTimesModal").modal("show");
                        } else if (!data.error) {
                            // set success modal data
                            $("#successModalTitle").html("Informazioni aggiunte");
                            $("#successModalMessage").html("L'orario di lavoro del servizio è stato aggiunto");
                            // show success modal
                            $("#addCustomServiceWorkTimesModal").modal("hide");
                            $("#successModal").modal("show");
                            // clean all the fields
                        } else {
                            // set error modal data
                            $("#errorModalTitle").html("Informazioni non aggiunte");
                            $("#errorModalMessage").html("L'orario di lavoro del servizio non è stato aggiunto, per favore riprova, se l'errore persiste contatta l'assistenza");
                            // show confirmation modal
                            $("#addCustomServiceWorkTimesModal").modal("hide");
                            $("#errorModal").modal("show");
                        }
                    }).fail(function () {
                    buttonLoader.hideLoadingAnimation();
                    // set error modal data
                    $("#errorModalTitle").html("Informazioni non aggiunte");
                    $("#errorModalMessage").html("L'orario di lavoro del servizio non è stato aggiunto, per favore riprova, se l'errore persiste contatta l'assistenza");
                    $("#addCustomServiceWorkTimesModal").modal("hide");
                    // show confirmation modal
                    $("#errorModal").modal("show");
                });
            });
        }
    });
    $("#workTime-startServiceCustomDay").on('change', function () {
        let startDate = new Date($("#workTime-startServiceCustomDay").val() + "T00:00:00.000Z");
        let today = new Date();
        today.setHours(0);
        today.setMinutes(0);
        today.setSeconds(0);
        today.setMilliseconds(0);
        if (!$("#customServiceWorkTimeAlert").hasClass('d-none') && startDate >= today) {
            $("#customServiceWorkTimeAlert").addClass('d-none');
        }
        if ($("#customServiceWorkTimeAlert").hasClass('d-none') && startDate < today) {
            $("#customServiceWorkTimeAlert").removeClass('d-none');
        }
    });
    $("#confirmOvverideServiceWorkTimesBtn").on('click', function () {
        let buttonLoader = new ButtonLoader("#confirmOvverideServiceWorkTimesBtn", true);
        buttonLoader.makeRequest(function () {
            $.post("/admin/api/service/update_working_time.php", {
                data: JSON.stringify(lastCustomWorkTimeRequest),
                method: "OVERRIDE",
            })
                .done(function (data) {
                    buttonLoader.hideLoadingAnimation();
                    if (!data.error) {
                        // set success modal data
                        $("#successModalTitle").html("Informazioni aggiunte");
                        $("#successModalMessage").html("L'orario di lavoro del dipendente è stato aggiunto");
                        // show success modal
                        $("#conflictWorkTimesModal").modal("hide");
                        $("#successModal").modal("show");
                        // clean all the fields
                    } else {
                        // set error modal data
                        $("#errorModalTitle").html("Informazioni non aggiunte");
                        $("#errorModalMessage").html("L'orario di lavoro del dipendente non è stato aggiunto, per favore riprova, se l'errore persiste contatta l'assistenza");
                        // show confirmation modal
                        $("#conflictWorkTimesModal").modal("hide");
                        $("#errorModal").modal("show");
                    }
                }).fail(function () {
                buttonLoader.hideLoadingAnimation();
                // set error modal data
                $("#errorModalTitle").html("Informazioni non aggiunte");
                $("#errorModalMessage").html("L'orario di lavoro del dipendente non è stato aggiunto, per favore riprova, se l'errore persiste contatta l'assistenza");
                $("#conflictWorkTimesModal").modal("hide");
                // show confirmation modal
                $("#errorModal").modal("show");
            });
        });
    });
})