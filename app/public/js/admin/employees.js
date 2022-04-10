function populateEditModal(employeeId){
    $.get("/admin/api/employee/get_employees.php", {id: employeeId})
        .done(function (data){
            data = data[0]
            $("#name-edit").val(data.name);
            $("#surname-edit").val(data.surname);
            $("#role-edit").val(data.role);
            $("#username-edit").val(data.username);
            if (data.userType == 1){
                data.userType = true;
            } else {
                data.userType = false;
            }
            $("#admin-edit").prop("checked", data.userType);
        })
        .fail(function (){

        });
}

function getEmployeesList(){
    $.get("/admin/api/employee/get_employees.php")
        .done(function (data) {
            $('#employeeList').empty();
            if (!data.error && data.length > 0) {
                data.forEach(element => {
                    if (element.userType == 1){
                        element.userType = "Amministratore";
                    } else {
                        element.userType = "Dipendente";
                    }
                    $('#employeeList').append('<a href="#" class="list-group-item list-group-item-action flex-column align-items-start"> ' +
                        '<div class="d-flex w-100 justify-content-between"> ' +
                        '<h5 class="mb-1">' + element.name + ' ' + element.surname + '</h5> ' +
                        '<div class="pointer">' +
                        '<i class="fa-solid fa-pen edit-user ms-2" value="' + element.id + '"></i><i class="fa-solid fa-trash ms-2 delete-user" value="' + element.id + '"></i></div>' +
                        '</div><div class="d-flex w-100 justify-content-between"><h7 class="mb-1">' + element.role + '</h7></div>' +
                        '<div class="d-flex w-100 justify-content-between">  ' +
                        '<small>Username: ' + element.username + '</small>' +
                        '<small>' + element.userType + '</small>' +
                        '</div></a>');
                });
                $(".edit-user").on("click", function () {
                    userId = $(this).attr("value");
                    populateEditModal(userId);
                    $("#confirmEditEmployeeBtn").attr('value', userId);
                    // open modal to confirm
                    $("#editEmployeeModal").modal("show");
                });
                $(".delete-user").on("click", function () {
                    userId = $(this).attr("value");
                    $("#confirmDeleteEmployeeBtn").attr('value', userId);
                    // open modal to confirm
                    $("#deleteEmployeeModal").modal("show");
                });
            } else if (data.length === 0) {
                // display no appointments message
                $('#employeeList').append('');
                $('#employeeList').append('<div class="card-body">' +
                    '<p class="card-text noServices">Non è presente alcun dipendente, aggiungine uno col pulsante qua sopra</p>' +
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
$(function () {
    getEmployeesList();

    $("#addEmployeeBtn").on("click", function (){
        $("#addEmployeeModal").modal("show");
    });

    $("#confirmAddEmployeeBtn").on("click", function (){
        $.post("/admin/api/employee/add_employee.php", {
            name: $("#name").val(),
            surname: $("#surname").val(),
            role: $("#role").val(),
            username: $("#username").val(),
            password: $("#password").val(),
            admin: $("#admin").prop("checked")
        })
            .done(function (data) {
                if (!data.error) {
                    // show confirmation modal
                    $("#successModal").modal("show");
                    getEmployeesList();
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
    })

    $("#confirmEditEmployeeBtn").on("click", function (){
        $.post("/admin/api/employee/update_employee.php", {
            id: $(this).val(),
            name: $("#name-edit").val(),
            surname: $("#surname-edit").val(),
            role: $("#role-edit").val(),
            username: $("#username-edit").val(),
            password: $("#password-edit").val(),
            admin: $("#admin-edit").prop("checked")
        })
            .done(function (data) {
                if (!data.error) {
                    // show confirmation modal
                    $("#successModal").modal("show");
                    getEmployeesList();
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

    $("#confirmDeleteEmployeeBtn").on("click", function (){
        $.get("/admin/api/employee/delete_employee.php", {id: $(this).val()})
            .done(function (data){
                getEmployeesList();
            })
            .fail(function (){
            })
    });
})