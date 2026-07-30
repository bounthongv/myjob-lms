<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!defined('BASE_URLS')) {
    define('BASE_URLS', '/');
    define('BASE_URLSS', '/file/');
  }
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APLABOR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URLS ?>css/navbar.css?v=<?= filemtime(__DIR__ . '/css/navbar.css') ?>">
</head>
<body>

    <header class="navbar-container">
        <div class="navbar-content">
            <a href="<?= BASE_URLS ?>" class="navbar-logo">
                <img src="<?= BASE_URLS ?>IjobsLogo.png" alt="iJobs" width="32" height="32" class="apis-icon">
                iJobs
            </a>

            <nav class="navbar-menu">
                <a href="<?= BASE_URLS ?>#">ລາຍການໜ້າວຽກທີ່ຮັບສະໝັກ</a>
                <a href="<?= BASE_URLS ?>#">ລາຍການຜູ້ຈ້າງງານ</a>
				<a href="<?= BASE_URLS ?>#">ລາຍການບ່ອນເຮັດວຽກ</a>
				<a href="<?= BASE_URLSS ?>vacancy_add.php">ລົງທະບຽນ</a>
				<a href="<?= BASE_URLSS ?>check.php">ກວດສອບຂໍ້ມູນ</a>
				<?php if (isset($_SESSION['customer_id']) || isset($_SESSION['user_id'])): ?>
					<a href="<?= BASE_URLS ?>logout.php" style="color: #ff6b6b;"><i class="fa-solid fa-right-from-bracket"></i> ອອກຈາກລະບົບ</a>
				<?php else: ?>
					<a href="<?= BASE_URLS ?>login.php"><i class="fa-solid fa-right-to-bracket"></i> ເຂົ້າສູ່ລະບົບ</a>
				<?php endif; ?>
            </nav>

            <button class="menu-toggle" aria-label="Toggle navigation" aria-expanded="false">
                &#9776;
            </button>
        </div>

        <nav class="mobile-menu">
            <a href="<?= BASE_URLS ?>#">ລາຍການໜ້າວຽກທີ່ຮັບສະໝັກ</a>
            <a href="<?= BASE_URLS ?>#">ລາຍການຜູ້ຈ້າງງານ</a>
            <a href="<?= BASE_URLS ?>#">ລາຍການບ່ອນເຮັດວຽກ</a>
            <a href="<?= BASE_URLSS ?>vacancy_add.php">ລົງທະບຽນ</a>
            <a href="<?= BASE_URLSS ?>check.php">ກວດສອບຂໍ້ມູນ</a>
            <?php if (isset($_SESSION['customer_id']) || isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URLS ?>logout.php" style="color: #ff6b6b;"><i class="fa-solid fa-right-from-bracket"></i> ອອກຈາກລະບົບ</a>
            <?php else: ?>
                <a href="<?= BASE_URLS ?>login.php"><i class="fa-solid fa-right-to-bracket"></i> ເຂົ້າສູ່ລະບົບ</a>
            <?php endif; ?>
        </nav>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var navbarContainer = document.querySelector('.navbar-container');
            var menuToggle = document.querySelector('.menu-toggle');

            menuToggle.addEventListener('click', function () {
                var isOpen = navbarContainer.classList.toggle('active');
                menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            document.querySelectorAll('.mobile-menu a').forEach(function (link) {
                link.addEventListener('click', function () {
                    navbarContainer.classList.remove('active');
                    menuToggle.setAttribute('aria-expanded', 'false');
                });
            });
        });
    </script>
</body>
</html>