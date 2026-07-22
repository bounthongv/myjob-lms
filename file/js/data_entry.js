$(document).ready(function() {

    // ປຸ່ມທັງໝົດທີ່ໃຫ້ເລືອກໄດ້ (Borrow ຫຼື Pay)
    var $actionButtons = $(".btn-borrow, .btn-pay");
    var $selectedInput = $("#selected_action");
    var $row = $(".custom-action-row");

    $actionButtons.on("click", function() {

        var $this = $(this);
        var action = $this.data("action");

        // ຖ້າກົດປຸ່ມທີ່ຖືກເລືອກຢູ່ແລ້ວ ໃຫ້ຍົກເລີກການເລືອກ
        if ($this.hasClass("active")) {

            $this.removeClass("active");
            $selectedInput.val("");
            $row.removeClass("has-selection");

        } else {

            // ລຶບ Active ອອກຈາກປຸ່ມອື່ນ ແລ້ວໃສ່ໃຫ້ປຸ່ມທີ່ຖືກກົດ
            $actionButtons.removeClass("active");
            $this.addClass("active");

            // ບັນທຶກຄ່າທີ່ເລືອກໄວ້ໃນ Hidden Input
            $selectedInput.val(action);
            $row.addClass("has-selection");

        }

    });

});
    // ==========================================
    // 1. ระบบดักจับการเลือกไฟล์และแสดงภาพ Preview
    // ==========================================
    function setupImagePreview(fileInputId, boxId, contentId, previewBoxId, imgPreviewId) {
        document.getElementById(fileInputId).addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    // นำข้อมูลรูปไปใส่ในแท็ก <img>
                    document.getElementById(imgPreviewId).src = event.target.result;
                    
                    // ซ่อนหน้าตาอัปโหลดเดิม และแสดงกล่องพรีวิวรูปแทน
                    document.getElementById(contentId).classList.add('d-none');
                    document.getElementById(previewBoxId).classList.remove('d-none');
                    
                    // เพิ่มคลาสตกแต่งกล่องว่ามีไฟล์เข้ามาแล้ว
                    document.getElementById(boxId).classList.add('has-file');
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // // เรียกใช้งานระบบ Preview ทั้งฝั่งรูปหน้า และฝั่งอัปโหลดลายเซ็น
    setupImagePreview('file-photo', 'box-photo', 'content-photo', 'preview-box-photo', 'img-preview-photo');
    setupImagePreview('file-interview-form', 'box-interview-form', 'content-interview-form', 'preview-box-interview-form', 'img-preview-interview-form');
    $(document).ready(function() {

        // ລາຍການ id ຂອງໄຟລ໌ເອກະສານທັງໝົດ
        var documentList = [
            "passport",
            "farmer-cert",
            "labor-contract",
            "census",
            "collateral"
        ];

        // ວົນຮອບແຕ່ລະເອກະສານ ເພື່ອຜູກ Event ການເລືອກໄຟລ໌
        $.each(documentList, function(index, docId) {

            $("#file-" + docId).on("change", function() {

                var fileInput = this;

                // ກວດສອບວ່າມີການເລືອກໄຟລ໌ຫຼືບໍ່
                if (fileInput.files && fileInput.files.length > 0) {

                    var fileName = fileInput.files[0].name;

                    // ສະແດງຊື່ໄຟລ໌ໃນຊ່ອງ Text
                    $("#text-" + docId).val(fileName);

                } else {

                    // ຖ້າຍົກເລີກການເລືອກ ໃຫ້ລ້າງຄ່າ
                    $("#text-" + docId).val("");

                }

            });

        });

    });
    $(document).ready(function() {
        $("#btn-borrow, #btn-pay").click(function (e) { 
            var buttonId = $(this).attr('id');
            var buttonText = $(this).text().trim();
            $('#pay_sts').val(buttonText);
            
        });
        $('#not-pass, #pass, #re-pass').click(function() {
            
            // 1. ดึงข้อความจากปุ่มที่ถูกกดไปใส่ใน input#heal_sts
            var buttonId = $(this).attr('id');
            var buttonText = $(this).text();
            $('#heal_sts').val(buttonText);
            
            // 2. เคลียร์สถานะปุ่ม: ให้ทุกปุ่มเปิดใช้งานได้ก่อน
            $('#not-pass, #pass, #re-pass').prop('disabled', false);

            // 3. เช็กเงื่อนไขแยกตามปุ่มที่กด
            if (buttonId === 'not-pass') {
                // --- กรณีเซ็ตค่าถ้ากดปุ่ม Not Pass ---
                $('#heal_date').val(''); // ล้างค่าใน heal_date เป็นค่าว่าง
                
                // ค้นหา option ใน select#diagnose ที่มีคำว่า (ປົກກະຕິ) แล้วลบคำนั้นออก
                $('#diagnose option').each(function() {
                    var currentText = $(this).text();
                    if (currentText.includes('(ປົກກະຕິ)')) {
                        var newText = currentText.replace('(ປົກກະຕິ)', '').trim();
                        $(this).text(newText);
                    }
                });

                // เปิดให้เลือกตัวเลือกอื่นใน diagnose ได้
                $("#diagnose").prop('disabled', false);

                // [แก้ไขจุดบั๊ก]: ใส่คำสั่งล็อกปุ่ม Not Pass ทันทีเมื่อถูกกด
                $(this).prop('disabled', true);

            } else {
                // --- กรณีเซ็ตค่าถ้ากดปุ่มอื่นๆ (Pass หรือ Re-Pass) ---
                
                // เช็กก่อนว่ามีคำว่า (ປົກກະຕิ) อยู่ใน option หรือยัง ถ้ายังไม่มีให้เติมกลับเข้าไปข้างท้าย
                $('#diagnose option').each(function() {
                    var currentText = $(this).text();
                    if (!currentText.includes('(ປົກກະຕິ)')) {
                        $(this).text(currentText + ' (ປົກກະຕິ)');
                    }
                });

                // ปิดใช้งาน (Disabled) เฉพาะปุ่มที่เพิ่งโดนกดล่าสุด และล็อกช่อง select
                $(this).prop('disabled', true);
                $("#diagnose").prop('disabled', true);
            }
        });
    });
    $(document).ready(function() {
        
        $("#dob").on("change",function(){
            var birthday = new Date($(this).val());
            var today = new Date();
            var age = today.getFullYear() - birthday.getFullYear();
            $("#age").val(age);
            let yearf = parseFloat(age - 18);
            $("#agricu").val(yearf);
        }) 
        $("#gua_dob").on("change",function(){
            var birthday = new Date($(this).val());
            var today = new Date();
            var age = today.getFullYear() - birthday.getFullYear();
            $("#gua_age").val(age);
        }) 
        $("#pro_id").change(function(e) {
            let pro_id = $(this).val();
            $.ajax({
                type: "post",
                url: "get/get_pro.php",
                data: {
                    pro_id: pro_id
                },
                success: function(response) {
                    $("#dis_id").html(response);
                }
            });

        });
        $("#dis_id").change(function(e) {
            let dis_id = $(this).val();
            $.ajax({
                type: "post",
                url: "get/get_dis.php",
                data: {
                    dis_id: dis_id
                },
                success: function(response) {
                    $("#vill_id").html(response);
                }
            });

        });
        $("#gua_pro").change(function(e) {
            let pro_id = $(this).val();
            $.ajax({
                type: "post",
                url: "get/get_pro.php",
                data: {
                    pro_id: pro_id
                },
                success: function(response) {
                    $("#gua_dis").html(response);
                }
            });

        });
        $("#gua_dis").change(function(e) {
            let dis_id = $(this).val();
            $.ajax({
                type: "post",
                url: "get/get_dis.php",
                data: {
                    dis_id: dis_id
                },
                success: function(response) {
                    $("#gua_vill").html(response);
                }
            });

        });
        $("#coll_pro").change(function(e) {
            let pro_id = $(this).val();
            $.ajax({
                type: "post",
                url: "get/get_pro.php",
                data: {
                    pro_id: pro_id
                },
                success: function(response) {
                    $("#coll_dis").html(response);
                }
            });

        });
        $("#coll_dis").change(function(e) {
            let dis_id = $(this).val();
            $.ajax({
                type: "post",
                url: "get/get_dis.php",
                data: {
                    dis_id: dis_id
                },
                success: function(response) {
                    $("#coll_vill").html(response);
                }
            });

        });
        $("#pro_id_b").change(function(e) {
            let pro_id = $(this).val();
            $.ajax({
                type: "post",
                url: "get/get_pro.php",
                data: {
                    pro_id: pro_id
                },
                success: function(response) {
                    $("#dis_id_b").html(response);
                }
            });

        });
        $("#dis_id_b").change(function(e) {
            let dis_id = $(this).val();
            $.ajax({
                type: "post",
                url: "get/get_dis.php",
                data: {
                    dis_id: dis_id
                },
                success: function(response) {
                    $("#vill_id_b").html(response);
                }
            });

        });
    });
