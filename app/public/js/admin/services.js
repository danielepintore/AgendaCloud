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
})