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
            if (data.userType === 0){
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
                    if (element.isActive == 1){
                        element.isActive = "Attivo";
                    } else {
                        element.isActive = "Non attivo";
                    }
                    $('#employeeList').append('<a href="#" class="list-group-item list-group-item-action flex-column align-items-start"> ' +
                        '<div class="d-flex w-100 justify-content-between"> ' +
                        '<div><span class="name mb-1 me-1">' + element.name + ' ' + element.surname + '</span><small class="status">(' + element.isActive+ ')</small></div>' +
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
                    $("#confirmEditEmployeeBtn").attr('value', userId);
                    populateEditModal(userId);
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
                if (!data.error){
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
})