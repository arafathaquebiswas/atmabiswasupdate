<link rel="stylesheet" href="imageSlider.css?v=<?php echo filemtime(__DIR__ . '/imageSlider.css'); ?>">
<div class="slider">
    <div class="list">
        <div class="item">
            <img src="toilet/toiletpic1.jpg" loading="lazy" alt="ATMABISWAS sanitation program – community toilet facility in rural Bangladesh">
        </div>
        <div class="item">
            <img src="FISH/fish_pic6.jpg" loading="lazy" alt="ATMABISWAS fish farming project – sustainable aquaculture in Bangladesh">
        </div>
        <div class="item">
            <img src="Awarness/awarness_pic6.jpg" loading="lazy" alt="ATMABISWAS awareness campaign – community health education program">
        </div>
        <div class="item">
            <img src="Awarness/awarness_pic7.jpg" loading="lazy" alt="ATMABISWAS awareness program – rural community outreach in Bangladesh">
        </div>
        <div class="item">
            <img src="FISH/fish_pic4.jpg" loading="lazy" alt="ATMABISWAS fish farming initiative – rural livelihood project in Bangladesh">
        </div>
    </div>
    <div class="buttons">
        <button id="prev">&#10094;</button>
        <button id="next">&#10095;</button>
    </div>
    <ul class="dots">
        <li class="active"></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
    </ul>
</div>
<script src="imageSlider.js?v=<?php echo filemtime(__DIR__ . '/imageSlider.js'); ?>"></script>