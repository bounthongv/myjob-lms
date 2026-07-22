<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ກວດສອບຂໍ້ມູນ</title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="style_check.css">
</head>
<body>
<?php include('../menu.php'); ?>
<div class="container">
<div class="content">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="card shadow-sm check-card">
                <div class="card-body p-4">

                    <h4 class="text-center mb-1">ກວດສອບຂໍ້ມູນແຮງງານ</h4>
                    <p class="text-center text-muted mb-4">ກະລຸນາປ້ອນລະຫັດລະບຸຕົວຕົນ ເພື່ອກວດສອບຂໍ້ມູນ</p>

                    <form id="formCheckPassport" autocomplete="off">
                        <div class="mb-3">
                            <label for="passport" class="form-label">ລະຫັດລະບຸຕົວຕົນ</label>
                            <input
                                type="text"
                                class="form-control form-control-lg"
                                id="passport"
                                name="passport"
                                placeholder="ເຊັ່ນ N1234567"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg" id="btnCheck">
                            <span id="btnText">ກວດສອບຂໍ້ມູນ</span>
                            <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2 d-none"></span>
                        </button>
                    </form>

                    <!-- ພື້ນທີ່ສະແດງຜົນການກວດສອບ -->
                    <div id="resultBox" class="mt-4"></div>

                </div>
            </div>

        </div>
    </div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script_check.js?v=<?= filemtime('script_check.js') ?>"></script>

</body>
</html>
