<?php require_once 'config.php'; ?>
<link rel="stylesheet" href="<?= SITE_ROOT ?>/foot.css?v=<?php echo filemtime(__DIR__ . '/foot.css'); ?>">

<footer class="footer">
    <div class="footer-inner">

        <!-- Brand -->
        <div class="footer-col footer-brand">
            <div class="footer-brand-name">ATMABISWAS</div>
            <p class="footer-tagline" data-en="Non-Governmental Voluntary Organisation" data-bn="বেসরকারি স্বেচ্ছাসেবী সংস্থা">Non-Governmental Voluntary Organisation</p>
            <p class="footer-desc" data-en="Empowering individuals and fostering self-belief across Bangladesh since 1991. Committed to sustainable social change and community development."
               data-bn="১৯৯১ সাল থেকে বাংলাদেশ জুড়ে মানুষের ক্ষমতায়ন ও আত্মবিশ্বাস গড়ে তোলার কাজ করছি। টেকসই সামাজিক পরিবর্তন ও সম্প্রদায় উন্নয়নে প্রতিশ্রুতিবদ্ধ।">Empowering individuals and fostering self-belief across Bangladesh since 1991. Committed to sustainable social change and community development.</p>
        </div>

        <!-- Important Links -->
        <div class="footer-col">
            <h4 data-en="Important Links" data-bn="গুরুত্বপূর্ণ লিংক">Important Links</h4>
            <ul class="footer-list">
                <li><a href="<?= NOTICE_PATH ?>"><i class="fa-solid fa-chevron-right"></i> <span data-en="Notice" data-bn="নোটিশ">Notice</span></a></li>
                <li><a href="<?= CAREER_PATH ?>"><i class="fa-solid fa-chevron-right"></i> <span data-en="Career" data-bn="ক্যারিয়ার">Career</span></a></li>
                <li><a href="<?= ABOUTUS_PATH ?>"><i class="fa-solid fa-chevron-right"></i> <span data-en="About Us" data-bn="আমাদের সম্পর্কে">About Us</span></a></li>
                <li><a href="<?= PRESS_PATH ?>"><i class="fa-solid fa-chevron-right"></i> <span data-en="Press" data-bn="প্রেস">Press</span></a></li>
                <li><a href="<?= CONTACT_PATH ?>"><i class="fa-solid fa-chevron-right"></i> <span data-en="Contact" data-bn="যোগাযোগ">Contact</span></a></li>
                <li><a href="<?= EVENTS_PATH ?>"><i class="fa-solid fa-chevron-right"></i> <span data-en="Events" data-bn="ইভেন্টস">Events</span></a></li>
            </ul>
        </div>

        <!-- Our Programs -->
        <div class="footer-col">
            <h4 data-en="Our Programs" data-bn="আমাদের কার্যক্রম">Our Programs</h4>
            <ul class="footer-list">
                <li><a href="<?= GREEN_ENERGY_PATH ?>"><i class="fa-solid fa-chevron-right"></i> <span data-en="Green Energy" data-bn="সবুজ জ্বালানি">Green Energy</span></a></li>
                <li><a href="<?= ENTERPRISE_PATH ?>"><i class="fa-solid fa-chevron-right"></i> <span data-en="Enterprise Development" data-bn="উদ্যোক্তা উন্নয়ন">Enterprise Development</span></a></li>
                <li><a href="<?= AGRICULTURAL_PATH ?>"><i class="fa-solid fa-chevron-right"></i> <span data-en="Food &amp; Agriculture" data-bn="খাদ্য ও কৃষি">Food &amp; Agriculture</span></a></li>
                <li><a href="<?= READYTOEAT_PATH ?>"><i class="fa-solid fa-chevron-right"></i> <span data-en="Ready To Eat" data-bn="রেডি টু ইট">Ready To Eat</span></a></li>
                <li><a href="<?= HEALTH_PATH ?>"><i class="fa-solid fa-chevron-right"></i> <span data-en="Health &amp; Nutrition" data-bn="স্বাস্থ্য ও পুষ্টি">Health &amp; Nutrition</span></a></li>
                <li><a href="<?= SOCIAL_PATH ?>"><i class="fa-solid fa-chevron-right"></i> <span data-en="Social Work" data-bn="সামাজিক কার্যক্রম">Social Work</span></a></li>
            </ul>
        </div>

        <!-- Find Us -->
        <div class="footer-col">
            <h4 data-en="Find Us" data-bn="আমাদের খুঁজুন">Find Us</h4>
            <iframe
                class="footer-map"
                src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3654.988273234273!2d88.8443!3d23.640591!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39fecc6a98f15555%3A0x7237da8c2d53a42d!2sAtmabiswas!5e0!3m2!1sen!2sbd!4v1739124674000!5m2!1sen!2sbd"
                allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
            </iframe>
            <p class="footer-address">
                <i class="fa-solid fa-location-dot"></i>
                <span data-en="Chuadanga District, Bangladesh" data-bn="চুয়াডাঙ্গা জেলা, বাংলাদেশ">Chuadanga District, Bangladesh</span>
            </p>
        </div>

    </div>

    <!-- Bottom bar -->
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> <a href="<?= HOME_PATH ?>">ATMABISWAS</a>. <span data-en="All rights reserved." data-bn="সর্বস্বত্ব সংরক্ষিত।">All rights reserved.</span></p>
        <div class="footer-social">
            <a class="fb" target="_blank" href="https://www.facebook.com/atmabiswas.chuadanga/" aria-label="Facebook">
                <?= brand_icon('facebook-f') ?>
            </a>
            <a class="yt" target="_blank" href="https://www.youtube.com/channel/UCeqHBixXXoYfaX1gBOP-zOw" aria-label="YouTube">
                <?= brand_icon('youtube') ?>
            </a>
            <a class="em" target="_blank" href="https://mail.google.com/mail/?view=cm&fs=1&to=atmabiswas_ngo@yahoo.com" aria-label="Email">
                <i class="fas fa-envelope"></i>
            </a>
            <a class="li" target="_blank" href="https://www.linkedin.com/company/atmabiswas/" aria-label="LinkedIn">
                <?= brand_icon('linkedin-in') ?>
            </a>
        </div>
    </div>
</footer>

<!-- Back to top (floating button) -->
<button id="back-to-top" aria-label="Back to top">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<script>
(function () {
    // Registers the offline-fallback service worker (see sw.js). Only
    // helps on repeat visits — a browser can't run a worker it has never
    // downloaded, so this can't help first-time visitors with no connection.
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('<?= SITE_ROOT ?>/sw.js').catch(function () {});
        });
    }
}());
</script>

<script>
(function () {
    var btn = document.getElementById('back-to-top');
    window.addEventListener('scroll', function () {
        btn.style.display = window.scrollY > 300 ? 'flex' : 'none';
    });
    btn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}());
</script>
