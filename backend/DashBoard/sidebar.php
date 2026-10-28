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
</style>
<div class="admin-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-shapes"></i>
            <h1>DashPanel</h1>
        </div>
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
