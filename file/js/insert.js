$(document).ready(function () {

    $("#save_vacancy").on("submit", function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        formData.append("sub", 'insert');
        $.ajax({
            type: "post",
            url: "insert/insert_n_update_vacancy.php",
            data: formData,
            dataType: "json",
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.sts === 'error') {
                    showToast(response.message, 'error');
                    return;
                } else {
                    showToast(response.message, 'success');
                    setTimeout(function () {
                        window.location.href = '../login.php?mode=login';
                    }, 2500);
                }

            },
            error: function (xhr, status, error) {
                showToast('An error occurred: ' + error, 'error');
            }
        });
    });
    $("#edit_vacancy").on("submit", function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        formData.append("sub", 'update');
        $.ajax({
            type: "post",
            url: "insert/insert_n_update_vacancy.php",
            data: formData,
            dataType: "json",
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.sts === 'error') {
                    showToast(response.message, 'error');
                    return;
                } else {
                    showToast(response.message, 'success');
                    setTimeout(function () {
                        location = '../user_menu.php';
                    }, 1500);
                }

            },
            error: function (xhr, status, error) {
                showToast('An error occurred: ' + error, 'error');
            }
        });
    });
});