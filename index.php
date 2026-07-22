<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>APLABOR</title>
			<!-- ====== 1. เรียกใช้ Swiper CSS ====== -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
</head>
<style>
  /* ปรับขนาดและตกแต่ง — ใช้ aspect-ratio ให้พอดีกับรูปแบนเนอร์ (~2.3:1) ทุกขนาดจอ แทน vh ที่ทำให้จอมือถือครอปภาพจนขาด */
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

  /* เพิ่มเอฟเฟกต์การจาง */
  .swiper-slide {
    transition: transform 0.6s ease, opacity 0.6s ease;
  }
</style>
<body>
	<?php include('menu.php'); ?>
	<div class="content">




<!-- ====== 2. โครงสร้าง HTML ของ Slider ====== -->
<div class="swiper mySwiper">
  <div class="swiper-wrapper">
    <div class="swiper-slide">
      <img src="korea.png" alt="" style="width:100%">
    </div>
    <div class="swiper-slide">
      <img src="korea1.png" alt="" style="width:100%">
    </div>
  </div>

  <!-- ปุ่มเลื่อน -->
  <div class="swiper-button-next"></div>
  <div class="swiper-button-prev"></div>
  <!-- จุดแสดงตำแหน่ง -->
  <div class="swiper-pagination"></div>
</div>



	</div>
	<!-- ====== 3. เรียกใช้ JS ====== -->
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
    effect: "fade", // เปลี่ยนเป็น slide, cube, coverflow ได้
  });
</script>
</body>
</html>