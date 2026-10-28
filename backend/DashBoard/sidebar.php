<?php
// The page being shown, so its link is highlighted and the admin can see where they are.
$sidebarCurrent = basename((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
$sidebarActive  = static fn(string $file): string => $file === $sidebarCurrent ? ' active' : '';
?>
<style>
    /* Section headings: kept here, not in admin-sidebar.css, because a few
       pages include this sidebar without that stylesheet. */
    .admin-sidebar .nav-section {
        display: flex;
        align-items: center;
        gap: .6rem;
        margin: 1.5rem 1rem .5rem;
        padding: 0 .25rem .55rem;
        border-bottom: 2px solid #fff;
        color: #fff;
        font-size: .82rem;
        font-weight: 800;
        letter-spacing: 1.2px;
        text-transform: uppercase;
    }
    .admin-sidebar .nav-section i {
        width: 18px;
        text-align: center;
        font-size: .9rem;
        color: #fbbf24;
    }

    /* Phones: the sidebar slides in from a menu button in a top bar. The page
       stylesheets hide .sidebar-container below 768px, which used to leave
       admins on a phone with no menu at all; it is shown again here (zero
       width -- the sidebar itself is fixed and off-screen until opened). */
    .admin-mobile-bar, .admin-sidebar-close, .admin-sidebar-overlay { display: none; }
    @media screen and (max-width: 768px) {
        .sidebar-container { display: block !important; width: 0 !important; }
        body { padding-top: 56px; }
        .admin-mobile-bar {
            display: flex; align-items: center; gap: .75rem;
            position: fixed; top: 0; left: 0; right: 0; height: 56px; padding: 0 1rem; z-index: 1000;
            color: #fff; background: linear-gradient(135deg, #1e3a5f 0%, #0073e6 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, .15);
        }
        .admin-mobile-bar button {
            flex-shrink: 0; width: 42px; height: 42px; border: 0; border-radius: 8px; cursor: pointer;
            background: rgba(255, 255, 255, .15); color: #fff; font-size: 1.2rem;
        }
        .admin-mobile-bar .bar-title { font-weight: 700; font-size: 1.05rem; }
        .admin-mobile-bar .bar-page {
            margin-left: auto; font-size: .8rem; opacity: .9;
            max-width: 45%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .admin-sidebar-overlay {
            display: block; position: fixed; inset: 0; z-index: 1001; background: rgba(0, 0, 0, .5);
            opacity: 0; visibility: hidden; transition: opacity .3s ease, visibility .3s;
        }
        .admin-sidebar-overlay.active { opacity: 1; visibility: visible; }
        .admin-sidebar { z-index: 1002; transform: translateX(-100%); transition: transform .3s ease; }
        .admin-sidebar.mobile-open { transform: translateX(0); box-shadow: 4px 0 24px rgba(0, 0, 0, .3); }
        .admin-sidebar-close {
            display: inline-flex; align-items: center; justify-content: center;
            position: absolute; top: 1rem; right: 1rem; width: 38px; height: 38px;
            border: 0; border-radius: 8px; cursor: pointer;
            background: rgba(255, 255, 255, .15); color: #fff; font-size: 1.1rem;
        }
    }
</style>
<div class="admin-mobile-bar">
    <button type="button" aria-label="Open menu" aria-controls="adminSidebar" aria-expanded="false">
        <i class="fas fa-bars"></i>
    </button>
    <span class="bar-title">DashPanel</span>
    <span class="bar-page"></span>
</div>
<div class="admin-sidebar-overlay"></div>
<div class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-shapes"></i>
            <h1>DashPanel</h1>
        </div>
        <button type="button" class="admin-sidebar-close" aria-label="Close menu"><i class="fas fa-times"></i></button>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item<?= $sidebarActive('dashboard.php') ?>">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>
        <div class="nav-section"><i class="fas fa-briefcase"></i>Jobs &amp; Careers</div>
        <a href="addJobPosition.php" class="nav-item<?= $sidebarActive('addJobPosition.php') ?>">
            <i class="fas fa-user-plus"></i>
            <span>Add Job Position</span>
        </a>
        <a href="createjob.php" class="nav-item<?= $sidebarActive('createjob.php') ?>">
            <i class="fas fa-briefcase-medical"></i>
            <span>Create New Job Post</span>
        </a>
        <a href="updatejobs.php" class="nav-item<?= $sidebarActive('updatejobs.php') ?>">
            <i class="fas fa-edit"></i>
            <span>Update Job Posts</span>
        </a>
        <div class="nav-section"><i class="fas fa-newspaper"></i>News &amp; Media</div>
        <a href="blog_enhanced.php" class="nav-item<?= $sidebarActive('blog_enhanced.php') ?>">
            <i class="fas fa-plus-circle"></i>
            <span>Add Press Post</span>
        </a>
        <a href="blog_manager.php" class="nav-item<?= $sidebarActive('blog_manager.php') ?>">
            <i class="fas fa-newspaper"></i>
            <span>All Press Posts</span>
        </a>
        <a href="../../press.php" class="nav-item" target="_blank">
            <i class="fas fa-external-link-alt"></i>
            <span>View Newsroom</span>
        </a>
        <a href="uploadimg.php" class="nav-item<?= $sidebarActive('uploadimg.php') ?>">
            <i class="fas fa-photo-video"></i>
            <span>Upload Image Gallery</span>
        </a>
        <a href="uploadpdf.php" class="nav-item<?= $sidebarActive('uploadpdf.php') ?>">
            <i class="fas fa-bell"></i>
            <span>Upload Notice</span>
        </a>
        <a href="changeCredentials.php" class="nav-item<?= $sidebarActive('changeCredentials.php') ?>">
            <i class="fas fa-user-shield"></i>
            <span>Change Credentials</span>
        </a>
        <div class="nav-section"><i class="fas fa-file-alt"></i>Page Content</div>
        <a href="about_us_editor.php" class="nav-item<?= $sidebarActive('about_us_editor.php') ?>">
            <i class="fas fa-pen-to-square"></i>
            <span>About Us Editor</span>
        </a>
        <a href="homepage_stats.php" class="nav-item<?= $sidebarActive('homepage_stats.php') ?>">
            <i class="fas fa-chart-line"></i>
            <span>Homepage Statistics</span>
        </a>
        <a href="social_images.php" class="nav-item<?= $sidebarActive('social_images.php') ?>">
            <i class="fas fa-images"></i>
            <span>Social Page Images</span>
        </a>
        <a href="events_images.php" class="nav-item<?= $sidebarActive('events_images.php') ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Events Page Images</span>
        </a>
        <div class="nav-section"><i class="fas fa-address-book"></i>Contact Management</div>
        <a href="regional_offices.php" class="nav-item<?= $sidebarActive('regional_offices.php') ?>">
            <i class="fas fa-map-marker-alt"></i>
            <span>Regional Offices</span>
        </a>
        <a href="divisions.php" class="nav-item<?= $sidebarActive('divisions.php') ?>">
            <i class="fas fa-layer-group"></i>
            <span>Divisions</span>
        </a>
        <a href="branches.php" class="nav-item<?= $sidebarActive('branches.php') ?>">
            <i class="fas fa-code-branch"></i>
            <span>Branches</span>
        </a>
        <?php if (function_exists('is_super_admin') && is_super_admin()): ?>
        <div class="nav-section"><i class="fas fa-user-lock"></i>Access &amp; Security</div>
        <a href="google_access.php" class="nav-item<?= $sidebarActive('google_access.php') ?>">
            <i class="fab fa-google"></i>
            <span>Google Sign-in Access</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="../login/logout.php" class="nav-item logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
        <div class="user-info">
            <a href="../../index.php" class="user-link">
                <img src="../images/logo/logo.png" alt="ATMABISWAS Logo" class="user-logo" />
                <div class="user-details">
                    <div class="user-name">ATMABISWAS</div>
                    <div class="user-role">Administrator</div>
                </div>
            </a>
        </div>
    </div>
</div>
<script>
    // Phone menu: open from the top bar; close with the X, a tap outside, or Escape.
    (function () {
        var sidebar = document.getElementById('adminSidebar');
        var overlay = document.querySelector('.admin-sidebar-overlay');
        var openBtn = document.querySelector('.admin-mobile-bar button');
        var closeBtn = sidebar ? sidebar.querySelector('.admin-sidebar-close') : null;
        if (!sidebar || !overlay || !openBtn || !closeBtn) return;

        function setOpen(open) {
            sidebar.classList.toggle('mobile-open', open);
            overlay.classList.toggle('active', open);
            openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            document.body.style.overflow = open ? 'hidden' : '';
            (open ? closeBtn : openBtn).focus();
        }
        openBtn.addEventListener('click', function () { setOpen(true); });
        closeBtn.addEventListener('click', function () { setOpen(false); });
        overlay.addEventListener('click', function () { setOpen(false); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('mobile-open')) setOpen(false);
        });
        // Turning a tablet to landscape (desktop layout) must not leave the page locked.
        var wide = window.matchMedia('(min-width: 769px)');
        (wide.addEventListener ? wide.addEventListener.bind(wide, 'change') : wide.addListener.bind(wide))(function (m) {
            if (m.matches && sidebar.classList.contains('mobile-open')) setOpen(false);
        });

        // The top bar names the page you are on.
        var active = sidebar.querySelector('.nav-item.active span');
        if (active) document.querySelector('.admin-mobile-bar .bar-page').textContent = active.textContent;
    })();
</script>
