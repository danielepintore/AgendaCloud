$.fn.extend({
    donetyping: function(callback,timeout){
        timeout = timeout || 1e3; // 1 second default timeout
        var timeoutReference,
            doneTyping = function(el){
                if (!timeoutReference) return;
                timeoutReference = null;
                callback.call(el);
            };
        return this.each(function(i,el){
            var $el = $(el);
            // Chrome Fix (Use keyup over keypress to detect backspace)
            $el.is(':input') && $el.on('keyup keydown paste',function(e){
                // This catches the backspace button in chrome, but also prevents
                // the event from triggering too preemptively. Without this line,
                // using tab/shift+tab will make the focused element fire the callback
                if (e.type=='keyup' && e.keyCode!=8 || (e.metaKey && e.keyCode != 8) || (e.ctrlKey && e.keyCode != 8)) return;
                // Check if timeout has been set. If it has, "reset" the clock and
                // start over again.
                if (timeoutReference) clearTimeout(timeoutReference);
                timeoutReference = setTimeout(function(){
                    // if we made it here, our timeout has elapsed. Fire the
                    // callback
                    doneTyping(el);
                }, timeout);
            }).on('blur',function(){
                // If we can, fire the event since we're leaving the field
                doneTyping(el);
            });
        });
    }
});

function getEmployeesToAdd(serviceId, name){
    $.get("/admin/api/service/get_employees_to_add.php", {id: serviceId, name: name})
        .done(function (data) {
            if (!data.error && data.length > 0) {
                $("#editEmployeesTableContent").empty();
                $("#editEmployeesTable").removeClass("d-none");
                // generate the table
                data.forEach(element => {
                    if (element.available_action == "delete"){
                        $("#editEmployeesTableContent").append('<tr value="' + element.id + '"><td>' + element.name + '</td><td>' + element.surname + '</td><td><button type="button" value="' + element.id + '" class="employeeBtnRemove btn btn-outline-danger btn-sm"><i class="fa-solid fa-xmark"></i></button></td></tr>');
                    } else {
                        $("#editEmployeesTableContent").append('<tr value="' + element.id + '"><td>' + element.name + '</td><td>' + element.surname + '</td><td><button type="button" value="' + element.id + '" class="employeeBtnAdd btn btn-outline-success btn-sm"><i class="fa-solid fa-plus"></i></button></td></tr>');
                    }
                });
                // add listeners
                $(".employeeBtnRemove").on('click', function (){
                    $.get('/admin/api/service/remove_employee_to_service.php', {serviceId: $('#confirmAddEmployeeBtn').val(), employeeId: $(this).val()})
                        .done(function (data) {
                            if (!data.error) {
                                getEmployeesToAdd($('#confirmAddEmployeeBtn').val(), $('#employeeNameSearch').val());
                                getServicesList();
                            } else {
                                // todo add some alert
                                // if there is an error do nothing
                            }
                        })
                        .fail(function (data) {
                            // todo add some alert
                        })
                });

                $(".employeeBtnAdd").on('click', function (){
                    $.get('/admin/api/service/add_employee_to_service.php', {serviceId: $('#confirmAddEmployeeBtn').val(), employeeId: $(this).val()})
                        .done(function (data) {
                            if (!data.error) {
                                getEmployeesToAdd($('#confirmAddEmployeeBtn').val(), $('#employeeNameSearch').val());
                                getServicesList();
                            } else {
                                // todo add some alert
                                // if there is an error do nothing
                            }
                        })
                        .fail(function (data) {
                            // todo add some alert
                        })
                });
            } else if (data.length == 0){
                $("#editEmployeesTable").addClass("d-none");
                $("#employeesToAddInfo").html("Non ci sono dipendenti con questo nome");
            }
        })
        .fail(function (data) {
            $("#editEmployeesTable").addClass("d-none");
            $("#employeesToAddInfo").html("Si è verificato un errore");
        });
}

function getEmployeesList(serviceId){
    $.get("/admin/api/service/get_employees.php", {id: serviceId})
        .done(function (data){
            if (!data.error && data.length > 0){
                $("#employeesTableContent").empty();
                $("#employeesTable").removeClass("d-none");
                $("#employeesInfo").addClass("d-none");
                data.forEach(element => {
                    $("#employeesTableContent").append('<tr><td>' + element.name + '</td><td>' + element.surname + '</td></tr>');
                });
            } else if (data.length === 0){
                $("#employeesTable").addClass("d-none");
                $("#employeesInfo").removeClass("d-none");
                $("#employeesInfo").html("Non ci sono dipendenti che offrono questo servizio")
            }

        })
        .fail(function (){

        });
}
function populateEditModal(serviceId){
    $.get("/admin/api/service/get_services.php", {id: serviceId})
        .done(function (data){
            data = data[0]
            $("#service-name-edit").val(data.name),
            $("#service-duration-edit").val(data.duration),
            $("#service-startTime-edit").val(data.startTime),
            $("#service-endTime-edit").val(data.endTime),
            $("#service-cost-edit").val(data.cost),
            $("#service-waitTime-edit").val(data.waitTime),
            $("#service-bookableUntilTime-edit").val(data.bookableUntil),
            $("#service-description-edit").val(data.description),
            $("#service-active-edit").prop("checked", data.isActive)
        })
        .fail(function (){

        });
}
function getServicesList() {
    $.get("/admin/api/service/get_services.php")
        .done(function (data) {
            $('#servicesList').empty();
            if (!data.error && data.length > 0) {
                data.forEach(element => {
                    $('#servicesList').append('<a href="#" class="list-group-item list-group-item-action flex-column align-items-start"> ' +
                        '<div class="d-flex w-100 justify-content-between"> ' +
                        '<h5 class="mb-1">' + element.name + '</h5> ' +
                        '<div class="pointer"><small>' + element.startTime + '-' + element.endTime + '</small>' +
                        '<i class="fa-solid fa-pen edit-service ms-2" value="' + element.id + '"></i><i class="fa-solid fa-user-group view-employees ms-2" value="' + element.id + '"></i><i class="fa-solid fa-trash ms-2 delete-service" value="' + element.id + '"></i></div>' +
                        '</div><div class="d-flex w-100 justify-content-between"><h7 class="mb-1">' + element.cost + '€</h7></div>' +
                        '<div class="d-flex w-100 justify-content-between">  ' +
                        '<small>Durata: ' + element.duration + ' minuti</small>' +
                        '<small>Numero dipendenti: ' + element.employeesNumber + '</small>' +
                        '</div></a>');
                });
                $(".edit-service").on("click", function () {
                    serviceId = $(this).attr("value");
                    populateEditModal(serviceId);
                    $("#editServiceBtn").attr('value', serviceId);
                    // open modal to confirm
                    $("#editServiceModal").modal("show");
                });
                $(".view-employees").on("click", function () {
                    serviceId = $(this).attr("value");
                    getEmployeesList(serviceId);
                    $("#addEmployeesBtn").attr('value', serviceId);
                    // open modal to confirm
                    $("#showEmployeesModal").modal("show");
                });
                $(".delete-service").on("click", function () {
                    serviceId = $(this).attr("value");
                    $("#confirmDeleteServiceBtn").attr('value', serviceId);
                    // open modal to confirm
                    $("#deleteServiceModal").modal("show");
                });
            } else if (data.length === 0) {
                // display no appointments message
                $('#servicesList').append('');
                $('#servicesList').append('<div class="card-body">' +
                    '<p class="card-text noServices">Non sono presenti dei servizi, creane uno col pulsante qua sopra</p>' +
                    '</div>');
            } else {
                //TODO show error
                $('#servicesList').empty();
            }
        })
        .fail(function () {
            //TODO show error
            $('#servicesList').empty()
        });
}

// function to launch when the DOM is loaded
$(function () {
    getServicesList();

    $("#addServiceBtn").on("click", function () {
        $("#addServiceModal").modal("show");
    });

    $("#confirmAddServiceBtn").on("click", function () {
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
                if (!data.error) {
                    // show confirmation modal
                    $("#successModal").modal("show");
                    getServicesList();
                    // clean all the fields
                } else {
                    // show confirmation modal
                    $("#errorModal").modal("show");
                    // clean all the fields
                }
            }).fail(function () {
            // show confimation modal
            $("#errorModal").modal("show");
        });
    });

    $("#editServiceBtn").on("click", function (){
        $.post("/admin/api/service/update_service.php", {
            id: $(this).val(),
            serviceName: $("#service-name-edit").val(),
            serviceDuration: $("#service-duration-edit").val(),
            serviceStartTime: $("#service-startTime-edit").val(),
            serviceEndTime: $("#service-endTime-edit").val(),
            serviceCost: $("#service-cost-edit").val(),
            serviceWaitTime: $("#service-waitTime-edit").val(),
            bookableUntil: $("#service-bookableUntilTime-edit").val(),
            serviceDescription: $("#service-description-edit").val(),
            serviceActive: $("#service-active-edit").prop("checked")
        })
            .done(function (data) {
                if (!data.error) {
                    // show confirmation modal
                    getServicesList()
                    $("#successModal").modal("show");
                    // clean all the fields
                } else {
                    // show confirmation modal
                    $("#errorModal").modal("show");
                    // clean all the fields
                }
            }).fail(function () {
            // show confimation modal
            $("#errorModal").modal("show");
        });
    });

    $("#confirmDeleteServiceBtn").on("click", function (){
        $.get("/admin/api/service/delete_service.php", {id: $(this).val()})
            .done(function (data){
                getServicesList();
            })
            .fail(function (){
            })
    });

    $("#addEmployeesBtn").on('click', function (){
        $("#editEmployeesModal").modal('show');
        $("#confirmAddEmployeeBtn").attr('value', $(this).attr("value"));
        getEmployeesToAdd($("#confirmAddEmployeeBtn").val(), $("#employeeNameSearch").val());
    });

    $("#employeeNameSearch").donetyping(function (){
        getEmployeesToAdd($("#confirmAddEmployeeBtn").val(), $("#employeeNameSearch").val());
    })
})