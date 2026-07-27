<?php
session_start();
if (!isset($_SESSION['customer_id']) && !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>APLABOR</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
</head>
<style>
  .swiper {
    width: 100%;
    aspect-ratio: 1022 / 442;
    border-radius: 10px;
    overflow: hidden;
  }

  @media (min-width: 1200px) {
    .swiper {
      max-height: 560px;
    }
  }

  .swiper-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .swiper-slide {
    transition: transform 0.6s ease, opacity 0.6s ease;
  }
</style>
<body>
	<?php include('menu.php'); ?>
	<div class="content">

<div class="swiper mySwiper">
  <div class="swiper-wrapper">
    <div class="swiper-slide">
      <img src="korea.png" alt="" style="width:100%">
    </div>
    <div class="swiper-slide">
      <img src="korea1.png" alt="" style="width:100%">
    </div>
  </div>

  <div class="swiper-button-next"></div>
  <div class="swiper-button-prev"></div>
  <div class="swiper-pagination"></div>
</div>

	</div>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
  var swiper = new Swiper(".mySwiper", {
    loop: true,
    autoplay: {
      delay: 6500,
      disableOnInteraction: false,
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    effect: "fade",
  });
</script>
</body>
</html>