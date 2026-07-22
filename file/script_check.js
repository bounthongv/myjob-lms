$(document).ready(function () {

    // ຈັບ Event ຕອນສົ່ງຟອມກວດສອບ Passport
    $("#formCheckPassport").on("submit", function (e) {
        e.preventDefault();

        var passport = $("#passport").val().trim();

        if (passport === "") {
            showResult("empty", "ກະລຸນາປ້ອນເລກ ລະຫັດລະບຸຕົວຕົນ");
            return;
        }

        // ສະແດງສະຖານະ Loading ຢູ່ປຸ່ມ
        setLoading(true);
        $("#resultBox").html("");

        // ບັນທຶກເວລາເລີ່ມຕົ້ນ ເພື່ອຄຳນວນໃຫ້ Loading ສະແດງຢ່າງໜ້ອຍ 900ms
        var startTime = Date.now();
        var minLoadingTime = 900; // ໜ່ວຍເປັນ ms, ປັບໄດ້ຕາມຕ້ອງການ

        $.ajax({
            url: "check_action.php",
            type: "POST",
            data: { passport: passport },
            dataType: "json",
            success: function (res) {
                var elapsed = Date.now() - startTime;
                var remaining = Math.max(minLoadingTime - elapsed, 0);

                // ລໍຖ້າໃຫ້ຄົບເວລາຂັ້ນຕ່ຳ ກ່ອນສະແດງຜົນ ເພື່ອໃຫ້ animation ໄຫຼລື່ນ
                setTimeout(function () {
                    setLoading(false);

                    if (res.status === "found") {
                        // ພົບຂໍ້ມູນ -> ພາໄປໜ້າແກ້ໄຂ
                        showResult("found", "ພົບຂໍ້ມູນແລ້ວ ກຳລັງນຳໄປໜ້າສະແດງຂໍ້ມູນ...");

                        setTimeout(function () {
                            window.location.href = "vacancy_edit.php?vacancy_check=" + encodeURIComponent(res.vacancy_check);
                        }, 2000);

                    } else if (res.status === "not_found") {
                        showResult("not_found", "ບໍ່ພົບຂໍ້ມູນ ກະລຸນາກວດສອບ ລະຫັດລະບຸຕົວຕົນ ອີກຄັ້ງ");
                    } else {
                        showResult("empty", res.message);
                    }
                }, remaining);
            },
            error: function () {
                var elapsed = Date.now() - startTime;
                var remaining = Math.max(minLoadingTime - elapsed, 0);

                setTimeout(function () {
                    setLoading(false);
                    showResult("error", "ເກີດຂໍ້ຜິດພາດ ກະລຸນາລອງໃໝ່ອີກຄັ້ງ");
                }, remaining);
            }
        });
    });

    // ຟັງຊັນສະແດງຜົນລັບຕາມສະຖານະ ພ້ອມ animation ຄ່ອຍໆປາກົດ
    function showResult(type, message) {
        var alertClass = "alert-secondary";
        var icon = "";

        if (type === "found") {
            alertClass = "alert-success";
            icon = "✔ ";
        } else if (type === "not_found") {
            alertClass = "alert-danger";
            icon = "✖ ";
        } else if (type === "error") {
            alertClass = "alert-warning";
            icon = "⚠ ";
        } else {
            alertClass = "alert-warning";
        }

        var $alert = $(
            '<div class="alert ' + alertClass + ' text-center mb-0 result-fade">' + icon + message + '</div>'
        );

        $("#resultBox").html($alert);

        // ໃຫ້ browser render ກ່ອນ ແລ້ວຄ່ອຍໃສ່ class ເພື່ອໃຫ້ transition ເຮັດວຽກ
        requestAnimationFrame(function () {
            $alert.addClass("show");
        });
    }

    // ຟັງຊັນສະລັບສະຖານະ Loading ຂອງປຸ່ມ
    function setLoading(isLoading) {
        if (isLoading) {
            $("#btnCheck").prop("disabled", true).addClass("btn-loading");
            $("#btnText").text("ກຳລັງກວດສອບ...");
            $("#btnSpinner").removeClass("d-none");
        } else {
            $("#btnCheck").prop("disabled", false).removeClass("btn-loading");
            $("#btnText").text("ກວດສອບຂໍ້ມູນ");
            $("#btnSpinner").addClass("d-none");
        }
    }

});