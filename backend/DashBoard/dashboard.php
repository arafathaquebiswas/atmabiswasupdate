<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login/loging.php");
    exit();
}

include '../Database/db.php';

$db = new Db();

$conn = $db->connect();
function concatStrings($blog): string
{
    $display = "";
    $maximumLength = 80;

    $display = (strlen($blog) > $maximumLength) ? substr($blog, 0, $maximumLength) . "...." : $blog;

    return $display;
}
try {

    $sql = "SELECT * FROM blogs LIMIT 5";

    $stmt = $conn->prepare($sql);

    $stmt->execute();

    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e;
}

try {
    $imgSql = "SELECT * FROM img_upload LIMIT 5";

    $imgStmt = $conn->prepare($imgSql);

    $imgStmt->execute();

    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e;
}

try {
    $pdfSql = "SELECT * FROM pdsfiles LIMIT 5";

    $pdfStmt = $conn->prepare($pdfSql);

    $pdfStmt->execute();

    $pdfS = $pdfStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e;
}

try {
    $jobSql = "SELECT * FROM jobcodes LIMIT 5";

    $jobStmt = $conn->prepare($jobSql);

    $jobStmt->execute();

    $jobs = $jobStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e;
}


try {
    $cvSql = "SELECT * FROM cv_applications LIMIT 5";

    $cvStmt = $conn->prepare($cvSql);

    $cvStmt->execute();

    $cvs = $cvStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e;
}

try {
    $secSql = "SELECT * FROM sectors LIMIT 5";

    $secStmt = $conn->prepare($secSql);

    $secStmt->execute();

    $sectors = $secStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard-ATMABISWAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/admin-sidebar.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="../images/logo/logo.png">
</head>



<body class="bg-gray-50">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar-container">
            <?php include 'sidebar.php' ?>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <?php include 'navbar.inc.php'; ?>

            <!-- Content Area -->
            <main class="dashboard-main">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-left">
                            <h1>Dashboard Overview</h1>
                            <p class="page-subtitle">Welcome back, <?php echo $_SESSION['username']; ?>! Here's what's happening today.</p>
                        </div>
                        <div class="header-actions">
                            <a href="adminSignup.php" class="btn btn-outline">
                                <i class="fas fa-user-plus"></i>
                                Create Admin
                            </a>
                            <a href="manageAdmins.php" class="btn btn-outline">
                                <i class="fas fa-users-cog"></i>
                                Manage Admins
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <!-- Available Jobs Card -->
                    <div class="stats-card">
                        <div class="stats-header">
                            <div class="stats-info">
                                <h3>Available Jobs</h3>
                                <p>Active job positions</p>
                            </div>
                            <div class="stats-icon jobs">
                                <i class="fas fa-briefcase"></i>
                            </div>
                        </div>
                        <div class="stats-content">
                            <div class="stat-number"><?php
                                                        $countJobs = "SELECT COUNT(*) AS total_jobs FROM jobcodes";
                                                        $jobStmt = $conn->prepare($countJobs);
                                                        $jobStmt->execute();
                                                        $countBj = $jobStmt->fetchAll(PDO::FETCH_ASSOC);
                                                        echo $countBj[0]['total_jobs'];
                                                        ?></div>
                            <div class="stat-label">Active Positions</div>
                        </div>
                    </div>

                    <!-- Job Applications Card -->
                    <div class="stats-card">
                        <div class="stats-header">
                            <div class="stats-info">
                                <h3>Job Applications</h3>
                                <p>Pending applications</p>
                            </div>
                            <div class="stats-icon applications">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        </div>
                        <div class="stats-content">
                            <div class="stat-number"><?php
                                                        $countpen = "SELECT COUNT(*) AS total_jobs FROM cv_applications";
                                                        $penStmt = $conn->prepare($countpen);
                                                        $penStmt->execute();
                                                        $penBj = $penStmt->fetchAll(PDO::FETCH_ASSOC);
                                                        echo $penBj[0]['total_jobs'];
                                                        ?></div>
                            <div class="stat-label">Pending Requests</div>
                        </div>
                    </div>

                    <!-- Job Sectors Card -->
                    <div class="stats-card">
                        <div class="stats-header">
                            <div class="stats-info">
                                <h3>Job Sectors</h3>
                                <p>Available sectors</p>
                            </div>
                            <div class="stats-icon sectors">
                                <i class="fas fa-th-large"></i>
                            </div>
                        </div>
                        <div class="stats-content">
                            <div class="stat-number"><?php
                                                        $sect = "SELECT COUNT(*) AS total_jobs FROM sectors";
                                                        $newSecStmt = $conn->prepare($sect);
                                                        $newSecStmt->execute();
                                                        $secBj = $newSecStmt->fetchAll(PDO::FETCH_ASSOC);
                                                        echo $secBj[0]['total_jobs'];
                                                        ?></div>
                            <div class="stat-label">Total Sectors</div>
                        </div>
                    </div>

                    <!-- Content Overview Card -->
                    <div class="stats-card">
                        <div class="stats-header">
                            <div class="stats-info">
                                <h3>Content Overview</h3>
                                <p>Published content</p>
                            </div>
                            <div class="stats-icon content">
                                <i class="fas fa-book-open"></i>
                            </div>
                        </div>
                        <div class="stats-content">
                            <div class="content-stats">
                                <div class="content-item">
                                    <span class="content-number"><?php
                                                                    $sect = "SELECT COUNT(*) AS total_jobs FROM blogs";
                                                                    $newSecStmt = $conn->prepare($sect);
                                                                    $newSecStmt->execute();
                                                                    $secBj = $newSecStmt->fetchAll(PDO::FETCH_ASSOC);
                                                                    echo $secBj[0]['total_jobs'];
                                                                    ?></span>
                                    <span class="content-label">News</span>
                                </div>
                                <div class="content-item">
                                    <span class="content-number"><?php
                                                                    $sect = "SELECT COUNT(*) AS total_jobs FROM pdsfiles";
                                                                    $newSecStmt = $conn->prepare($sect);
                                                                    $newSecStmt->execute();
                                                                    $secBj = $newSecStmt->fetchAll(PDO::FETCH_ASSOC);
                                                                    echo $secBj[0]['total_jobs'];
                                                                    ?></span>
                                    <span class="content-label">Notices</span>
                                </div>
                                <div class="content-item">
                                    <span class="content-number"><?php
                                                                    $sect = "SELECT COUNT(*) AS total_jobs FROM img_upload";
                                                                    $newSecStmt = $conn->prepare($sect);
                                                                    $newSecStmt->execute();
                                                                    $secBj = $newSecStmt->fetchAll(PDO::FETCH_ASSOC);
                                                                    echo $secBj[0]['total_jobs'];
                                                                    ?></span>
                                    <span class="content-label">Images</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jobs table -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <div class="section-title">
                            <h2>Job Positions</h2>
                            <p>Active job positions and codes</p>
                        </div>
                        <div class="section-actions">
                            <button onclick="handleButton()" class="btn btn-primary">
                                <i class="fas fa-eye"></i>
                                View All
                            </button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>Job ID</th>
                                    <th>Job Position</th>
                                    <th>Job Code</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($jobs as $job) {
                                    echo '<tr>
                                        <td>' . $job['jobid'] . '</td>
                                        <td>' . concatStrings($job['JobTitle']) . '</td>
                                        <td>
                                            <span class="job-code">' . $job['JobCode'] . '</span>
                                        </td>
                                        <td>
                                            <a onclick="return confirm(\'Are you sure you want to delete this job position?\');" 
                                               href="../deleteJobPositions.php?job_id=' . $job['jobid'] . '" 
                                               class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </a>
                                        </td>
                                    </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Job Sectors -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <div class="section-title">
                            <h2>Job Sectors</h2>
                            <p>Available job sectors</p>
                        </div>
                        <div class="section-actions">
                            <button onclick="handleButton()" class="btn btn-primary">
                                <i class="fas fa-eye"></i>
                                View All
                            </button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>Sector ID</th>
                                    <th>Sector Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($sectors as $sec) {
                                    echo '<tr>
                                        <td>' . $sec['sector_id'] . '</td>
                                        <td>' . $sec['sector_name'] . '</td>
                                        <td>
                                            <a onclick="return confirm(\'Are you sure you want to delete this sector?\');" 
                                               href="../deleteSector.php?sec_id=' . $sec['sector_id'] . '" 
                                               class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </a>
                                        </td>
                                    </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>



                <!-- Pending Applications -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <div class="section-title">
                            <h2>Pending Applications</h2>
                            <p>Job applications awaiting review</p>
                        </div>
                        <div class="section-actions">
                            <button onclick="handleButton()" class="btn btn-primary">
                                <i class="fas fa-eye"></i>
                                View All
                            </button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>App ID</th>
                                    <th>Job ID</th>
                                    <th>Job Title</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Applied At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($cvs as $cv) {
                                    echo '<tr>
                                        <td>' . $cv['applicationId'] . '</td>
                                        <td>' . $cv['jobId'] . '</td>
                                        <td>' . $cv['job_title'] . '</td>
                                        <td>' . $cv['fullname'] . '</td>
                                        <td>' . $cv['email'] . '</td>
                                        <td>+88' . $cv['phone_no'] . '</td>
                                        <td>' . $cv['appliedAt'] . '</td>
                                        <td>
                                            <a onclick="return confirm(\'Are you sure you want to delete this job application?\');" 
                                               href="../../deletePendingJobs.php?applicationId=' . $cv['applicationId'] . '" 
                                               class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </a>
                                        </td>
                                    </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>



                <!-- Blogs Table -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <div class="section-title">
                            <h2>Published News</h2>
                            <p>Latest published press posts</p>
                        </div>
                        <div class="section-actions">
                            <button onclick="handleButton()" class="btn btn-primary">
                                <i class="fas fa-eye"></i>
                                View All
                            </button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>Press ID</th>
                                    <th>Press Title</th>
                                    <th>Author</th>
                                    <th>Published Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($blogs as $blog) {
                                    echo '<tr>
                                        <td>' . $blog['blog_id'] . '</td>
                                        <td>' . concatStrings($blog['blog_title']) . '</td>
                                        <td>
                                            <span class="author-badge">' . $blog['blog_author'] . '</span>
                                        </td>
                                        <td>' . $blog['upload_date'] . '</td>
                                        <td>
                                            <a onclick="return confirm(\'Are you sure you want to delete this press post?\');"
                                               href="../../deleteblog.php?blog_id=' . $blog['blog_id'] . '" 
                                               class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </a>
                                        </td>
                                    </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>



                <!-- Images Table -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <div class="section-title">
                            <h2>Uploaded Images</h2>
                            <p>Recently uploaded image gallery</p>
                        </div>
                        <div class="section-actions">
                            <button onclick="handleButton()" class="btn btn-primary">
                                <i class="fas fa-eye"></i>
                                View All
                            </button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>Image ID</th>
                                    <th>Image Title</th>
                                    <th>Description</th>
                                    <th>Image Path</th>
                                    <th>Upload Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($images as $image) {
                                    echo '<tr>
                                        <td>' . $image['img_id'] . '</td>
                                        <td>' . $image['img_title'] . '</td>
                                        <td>' . concatStrings($image['img_description']) . '</td>
                                        <td>' . concatStrings($image['img_path']) . '</td>
                                        <td>' . $image['uploaded_on'] . '</td>
                                        <td>
                                            <a onclick="return confirm(\'Are you sure you want to delete this image?\')" 
                                               href="../../deleteimage.php?img_id=' . $image['img_id'] . '" 
                                               class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </a>
                                        </td>
                                    </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>


                <!-- PDF Table -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <div class="section-title">
                            <h2>Uploaded Notices</h2>
                            <p>PDF notice files</p>
                        </div>
                        <div class="section-actions">
                            <button onclick="handleButton()" class="btn btn-primary">
                                <i class="fas fa-eye"></i>
                                View All
                            </button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>PDF ID</th>
                                    <th>PDF Title</th>
                                    <th>PDF Path</th>
                                    <th>Upload Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($pdfS as $pdf) {
                                    echo '<tr>
                                        <td>' . $pdf['pdf_id'] . '</td>
                                        <td>' . $pdf['pdf_title'] . '</td>
                                        <td>' . $pdf['pdf_path'] . '</td>
                                        <td>' . $pdf['upload_date'] . '</td>
                                        <td>
                                            <a onclick="return confirm(\'Are you sure you want to delete this PDF?\')" 
                                               href="../../deletepdf.php?pdf_id=' . $pdf['pdf_id'] . '" 
                                               class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </a>
                                        </td>
                                    </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>
    <script src="js/dashboard.js"></script>
</body>

</html>