<?php
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}
?>
<link rel="stylesheet" href="<?= SITE_ROOT ?>/navbar.css?v=<?php echo filemtime(__DIR__ . '/navbar.css'); ?>">
<link rel="stylesheet" href="<?= SITE_ROOT ?>/menutoggle.css?v=<?php echo filemtime(__DIR__ . '/menutoggle.css'); ?>">
<link rel="stylesheet" href="<?= SITE_ROOT ?>/sidebar.css?v=<?php echo filemtime(__DIR__ . '/sidebar.css'); ?>">
<?php /* The solid icon font still comes from cdnjs. Opening the connection
         early overlaps DNS, TCP and TLS with HTML parsing instead of paying for
         them after the stylesheet is discovered -- the font itself did not
         finish until 7.9s on Slow 4G. Costs nothing and changes no markup. */ ?>
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- Desktop Navbar -->
<div class="navbar-band">

    <!-- Top row: white band, logo left, utility links right -->
    <div class="navbar">
        <div class="top-row">
            <div class="logo">
                <a href="<?= HOME_PATH ?>"><img src="<?= SITE_ROOT ?>/logoBg.png" loading="lazy" alt="ATMABISWAS NGO"></a>
            </div>
            <div class="bars">
                <a href="<?= NOTICE_PATH ?>" data-en="Notice" data-bn="নোটিশ">Notice</a>
                <a href="<?= CAREER_PATH ?>" target="_blank" data-en="Career" data-bn="ক্যারিয়ার">Career</a>
                <a href="<?= PRESS_PATH ?>" data-en="Press" data-bn="প্রেস">Press</a>
                <button type="button" class="lang-toggle-btn" aria-label="Switch to Bangla">বাংলা</button>
            </div>
        </div>
    </div>

    <!-- Bottom row: full-width cyan band, centered nav links -->
    <div class="bottom-band">
        <div class="navbar">
            <div class="bottom-row">
                <a href="<?= HOME_PATH ?>" data-en="Who We Are" data-bn="আমরা কারা">Who We Are</a>

                <div class="dropdown">
                    <div class="maindrop">
                        <a href="javascript:void(0)"><span data-en="Our Team" data-bn="আমাদের দল">Our Team</span> <i class="fa-solid fa-caret-down arrow-icon"></i></a>
                    </div>
                    <div class="dropdown-content">
                        <a href="<?= EVE_PATH ?>" data-en="Executive" data-bn="নির্বাহী পরিষদ">Executive</a>
                        <a href="<?= GENERALBODY_PATH ?>" data-en="General Body" data-bn="সাধারণ পরিষদ">General Body</a>
                        <a href="<?= SENIOR_MANAGEMENT_PATH ?>" data-en="Senior Management" data-bn="সিনিয়র ম্যানেজমেন্ট">Senior Management</a>
                        <a href="<?= FOUNDER_PATH ?>" data-en="Founder" data-bn="প্রতিষ্ঠাতা">Founder</a>
                    </div>
                </div>

                <div class="dropdown">
                    <div class="maindrop">
                        <a href="javascript:void(0)"><span data-en="What We Do" data-bn="আমরা কী করি">What We Do</span> <i class="fa-solid fa-caret-down arrow-icon"></i></a>
                    </div>
                    <div class="dropdown-content">
                        <a href="<?= GREEN_ENERGY_PATH ?>" data-en="Green Energy" data-bn="সবুজ জ্বালানি">Green Energy</a>
                        <a href="<?= ENTERPRISE_PATH ?>" data-en="Enterprise Development" data-bn="উদ্যোক্তা উন্নয়ন">Enterprise Development</a>
                        <a href="<?= AGRICULTURAL_PATH ?>" data-en="Food &amp; Agriculture" data-bn="খাদ্য ও কৃষি">Food &amp; Agriculture</a>
                        <a href="<?= READYTOEAT_PATH ?>" data-en="Ready To Eat" data-bn="রেডি টু ইট">Ready To Eat</a>
                        <a href="<?= HEALTH_PATH ?>" data-en="Health &amp; Nutrition" data-bn="স্বাস্থ্য ও পুষ্টি">Health &amp; Nutrition</a>
                    </div>
                </div>

                <a href="<?= EVENTS_PATH ?>" data-en="Events" data-bn="ইভেন্টস">Events</a>
                <a href="<?= SOCIAL_PATH ?>" data-en="Social" data-bn="সামাজিক">Social</a>
                <a href="<?= CONTACT_PATH ?>" data-en="Contact" data-bn="যোগাযোগ">Contact</a>
                <a href="<?= ABOUTUS_PATH ?>" data-en="About Us" data-bn="আমাদের সম্পর্কে">About Us</a>

                <?php if (isset($_SESSION['username'])): ?>
                    <a class="nav-login-btn" href="<?= DASHBOARD_PATH ?>" data-en="Dashboard" data-bn="ড্যাশবোর্ড">Dashboard</a>
                <?php else: ?>
                    <a class="nav-login-btn" href="<?= LOGIN_PATH ?>" data-en="Login" data-bn="লগইন">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div><!-- /.navbar-band -->

<!-- Mobile Header -->
<div class="mobile-header">
    <div class="logo">
        <a href="<?= HOME_PATH ?>"><img src="<?= SITE_ROOT ?>/logoBg.png" loading="lazy" alt="ATMABISWAS NGO"></a>
    </div>
    <div class="menu-toggle" id="menu-toggleId">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
    </div>
</div>

<!-- Mobile Sidebar -->
<div class="sidenav">
    <div class="sidelogo">
        <img src="<?= SITE_ROOT ?>/LOGO/Monogram for web only.png" loading="lazy" alt="Logo" class="profile-img">
        <i id="close-btn" class="fa-solid fa-times"></i>
    </div>

    <button type="button" class="lang-toggle-btn side-lang-toggle" aria-label="Switch to Bangla">বাংলা</button>

    <a href="<?= HOME_PATH ?>"><i class="fa-solid fa-house-user"></i> <span data-en="Who We Are" data-bn="আমরা কারা">Who We Are</span></a>

    <div class="sidedrop">
        <div class="mainsidedrop">
            <a href="javascript:void(0)"><i class="fa-solid fa-people-group"></i> <span data-en="Our Team" data-bn="আমাদের দল">Our Team</span> <i class="fa-solid fa-caret-down side-arrow"></i></a>
        </div>
        <div class="sidedropContent">
            <a href="<?= EVE_PATH ?>"><i class="fa-solid fa-user-tie"></i> <span data-en="Executive" data-bn="নির্বাহী পরিষদ">Executive</span></a>
            <a href="<?= GENERALBODY_PATH ?>"><i class="fa-solid fa-users"></i> <span data-en="General Body" data-bn="সাধারণ পরিষদ">General Body</span></a>
            <a href="<?= SENIOR_MANAGEMENT_PATH ?>"><i class="fa-solid fa-user-shield"></i> <span data-en="Senior Management" data-bn="সিনিয়র ম্যানেজমেন্ট">Senior Management</span></a>
            <a href="<?= FOUNDER_PATH ?>"><i class="fa-solid fa-user"></i> <span data-en="Founder" data-bn="প্রতিষ্ঠাতা">Founder</span></a>
        </div>
    </div>

    <div class="sidedrop">
        <div class="mainsidedrop">
            <a href="javascript:void(0)"><i class="fa-solid fa-clipboard-list"></i> <span data-en="What We Do" data-bn="আমরা কী করি">What We Do</span> <i class="fa-solid fa-caret-down side-arrow"></i></a>
        </div>
        <div class="sidedropContent">
            <a href="<?= GREEN_ENERGY_PATH ?>"><i class="fa-solid fa-leaf"></i> <span data-en="Green Energy" data-bn="সবুজ জ্বালানি">Green Energy</span></a>
            <a href="<?= ENTERPRISE_PATH ?>"><i class="fa-solid fa-building"></i> <span data-en="Enterprise Development" data-bn="উদ্যোক্তা উন্নয়ন">Enterprise Development</span></a>
            <a href="<?= AGRICULTURAL_PATH ?>"><i class="fa-solid fa-seedling"></i> <span data-en="Food &amp; Agriculture" data-bn="খাদ্য ও কৃষি">Food &amp; Agriculture</span></a>
            <a href="<?= READYTOEAT_PATH ?>"><i class="fa-solid fa-pizza-slice"></i> <span data-en="Ready To Eat" data-bn="রেডি টু ইট">Ready To Eat</span></a>
            <a href="<?= HEALTH_PATH ?>"><i class="fa-solid fa-stethoscope"></i> <span data-en="Health &amp; Nutrition" data-bn="স্বাস্থ্য ও পুষ্টি">Health &amp; Nutrition</span></a>
        </div>
    </div>

    <div class="sidedrop">
        <div class="mainsidedrop activities-main">
            <a href="javascript:void(0)"><i class="fa-solid fa-chart-line"></i> <span data-en="Activities" data-bn="কার্যক্রম">Activities</span> <i class="fa-solid fa-caret-down side-arrow"></i></a>
        </div>
        <div class="sidedropContent">
            <a href="<?= CAREER_PATH ?>"><i class="fa-solid fa-briefcase"></i> <span data-en="Career" data-bn="ক্যারিয়ার">Career</span></a>
            <a href="<?= NOTICE_PATH ?>"><i class="fa-solid fa-bullhorn"></i> <span data-en="Notice" data-bn="নোটিশ">Notice</span></a>
            <a href="<?= PRESS_PATH ?>"><i class="fa-solid fa-newspaper"></i> <span data-en="Press" data-bn="প্রেস">Press</span></a>
        </div>
    </div>

    <a href="<?= EVENTS_PATH ?>"><i class="fa-solid fa-calendar-check"></i> <span data-en="Events" data-bn="ইভেন্টস">Events</span></a>
    <a href="<?= SOCIAL_PATH ?>"><i class="fa-solid fa-handshake"></i> <span data-en="Social Work" data-bn="সামাজিক কার্যক্রম">Social Work</span></a>
    <a href="<?= CONTACT_PATH ?>"><i class="fa-solid fa-envelope-open-text"></i> <span data-en="Contact" data-bn="যোগাযোগ">Contact</span></a>
    <a href="<?= ABOUTUS_PATH ?>"><i class="fa-solid fa-circle-info"></i> <span data-en="About Us" data-bn="আমাদের সম্পর্কে">About Us</span></a>

    <?php if (isset($_SESSION['username'])): ?>
        <a href="<?= DASHBOARD_PATH ?>"><i class="fa-solid fa-gauge"></i> <span data-en="Dashboard" data-bn="ড্যাশবোর্ড">Dashboard</span></a>
    <?php else: ?>
        <a href="<?= LOGIN_PATH ?>"><i class="fa-solid fa-right-to-bracket"></i> <span data-en="Login" data-bn="লগইন">Login</span></a>
    <?php endif; ?>
</div>

<script src="<?= SITE_ROOT ?>/navbar.js?v=<?php echo filemtime(__DIR__ . '/navbar.js'); ?>"></script>
<script src="<?= SITE_ROOT ?>/menutoggle.js?v=<?php echo filemtime(__DIR__ . '/menutoggle.js'); ?>"></script>
<script src="<?= SITE_ROOT ?>/lang.js?v=<?php echo filemtime(__DIR__ . '/lang.js'); ?>"></script>
