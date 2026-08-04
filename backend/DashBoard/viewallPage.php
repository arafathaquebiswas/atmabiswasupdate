<?php
session_start();
include '../Database/db.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login/loging.php");
    exit();
}


$db = new Db();

$conn = $db->connect();
function concatStrings(string $blog): string
{
    $display = "";
    $maximumLength = 80;

    $display = (strlen($blog) > $maximumLength) ? substr($blog, 0, $maximumLength) . "...." : $blog;

    return $display;
}
try {

    $sql = "SELECT * FROM blogs";

    $stmt = $conn->prepare($sql);

    $stmt->execute();

    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e;
}

try {
    $imgSql = "SELECT * FROM img_upload";

    $imgStmt = $conn->prepare($imgSql);

    $imgStmt->execute();

    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e;
}

try {
    $pdfSql = "SELECT * FROM pdsfiles";

    $pdfStmt = $conn->prepare($pdfSql);

    $pdfStmt->execute();

    $pdfS = $pdfStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e;
}

try {
    $jobSql = "SELECT * FROM jobcodes";

    $jobStmt = $conn->prepare($jobSql);

    $jobStmt->execute();

    $jobs = $jobStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e;
}


try {
    $cvSql = "SELECT * FROM cv_applications";

    $cvStmt = $conn->prepare($cvSql);

    $cvStmt->execute();

    $cvs = $cvStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e;
}

try {
    $secSql = "SELECT * FROM sectors";

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
    <title>View All Data - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="css/admin-sidebar.css">
    <link rel="stylesheet" href="css/viewall.css">
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
                <div class="container">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="header-content">
                            <div class="header-left">
                                <h1 class="page-title">
                                    <i class="fas fa-database"></i>
                                    View All Data
                                </h1>
                                <p class="page-subtitle">Manage and view all system data</p>
                            </div>
                            <div class="header-right">
                                <div class="stats-summary">
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo count($jobs); ?></span>
                                        <span class="stat-label">Job Positions</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo count($blogs); ?></span>
                                        <span class="stat-label">Press Posts</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo count($cvs); ?></span>
                                        <span class="stat-label">Applications</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Positions Table -->
                    <div class="data-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i class="fas fa-briefcase"></i>
                                Job Positions and Job Codes
                            </h2>
                            <p class="section-subtitle">Manage job positions and their codes</p>
                        </div>
                        <div class="table-container">
                            <div class="table-wrapper">
                                <div class="table-actions">
                                    <button onclick="handleButton()" class="btn btn-primary">
                                        <i class="fas fa-arrow-left"></i>
                                        Back to Dashboard
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Job ID</th>
                                                <th>Job Position</th>
                                                <th>Job Code</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($jobs as $job): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($job['jobid']); ?></td>
                                                    <td><?php echo htmlspecialchars(concatStrings($job['JobTitle'])); ?></td>
                                                    <td>
                                                        <span class="badge badge-primary"><?php echo htmlspecialchars($job['JobCode']); ?></span>
                                                    </td>
                                                    <td>
                                                        <a onclick="return confirm('Are you sure you want to delete this job position?');"
                                                            href="../deleteJobPositions.php?job_id=<?php echo $job['jobid']; ?>"
                                                            class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Sectors Table -->
                    <div class="data-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i class="fas fa-building"></i>
                                Job Sectors
                            </h2>
                            <p class="section-subtitle">Manage job sectors and departments</p>
                        </div>
                        <div class="table-container">
                            <div class="table-wrapper">
                                <div class="table-actions">
                                    <button onclick="handleButton()" class="btn btn-primary">
                                        <i class="fas fa-arrow-left"></i>
                                        Back to Dashboard
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Sector ID</th>
                                                <th>Sector Name</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($sectors as $sec): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($sec['sector_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($sec['sector_name']); ?></td>
                                                    <td>
                                                        <a onclick="return confirm('Are you sure you want to delete this sector?');"
                                                            href="../deleteSector.php?sec_id=<?php echo $sec['sector_id']; ?>"
                                                            class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- Pending Applications Table -->
                    <div class="data-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i class="fas fa-user-clock"></i>
                                Pending Job Applications
                            </h2>
                            <p class="section-subtitle">Review and manage job applications</p>
                        </div>
                        <div class="table-container">
                            <div class="table-wrapper">
                                <div class="table-actions">
                                    <button onclick="handleButton()" class="btn btn-primary">
                                        <i class="fas fa-arrow-left"></i>
                                        Back to Dashboard
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Application ID</th>
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
                                            <?php foreach ($cvs as $cv): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($cv['applicationId']); ?></td>
                                                    <td><?php echo htmlspecialchars($cv['jobId']); ?></td>
                                                    <td><?php echo htmlspecialchars($cv['job_title']); ?></td>
                                                    <td><?php echo htmlspecialchars($cv['fullname']); ?></td>
                                                    <td><?php echo htmlspecialchars($cv['email']); ?></td>
                                                    <td><?php echo '+88' . htmlspecialchars($cv['phone_no']); ?></td>
                                                    <td><?php echo htmlspecialchars($cv['appliedAt']); ?></td>
                                                    <td>
                                                        <a onclick="return confirm('Are you sure you want to delete this job application?');"
                                                            href="../deletePendingJobs.php?applicationId=<?php echo $cv['applicationId']; ?>"
                                                            class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- Blogs Table -->
                    <div class="data-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i class="fas fa-newspaper"></i>
                                Published Press Posts
                            </h2>
                            <p class="section-subtitle">Manage published press posts</p>
                        </div>
                        <div class="table-container">
                            <div class="table-wrapper">
                                <div class="table-actions">
                                    <button onclick="handleButton()" class="btn btn-primary">
                                        <i class="fas fa-arrow-left"></i>
                                        Back to Dashboard
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="data-table">
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
                                            <?php foreach ($blogs as $blog): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($blog['blog_id']); ?></td>
                                                    <td><?php echo htmlspecialchars(concatStrings($blog['blog_title'])); ?></td>
                                                    <td>
                                                        <span class="badge badge-info"><?php echo htmlspecialchars($blog['blog_author']); ?></span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($blog['upload_date']); ?></td>
                                                    <td>
                                                        <a onclick="return confirm('Are you sure you want to delete this press post?');"
                                                            href="../deleteblog.php?blog_id=<?php echo $blog['blog_id']; ?>"
                                                            class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- Images Table -->
                    <div class="data-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i class="fas fa-images"></i>
                                Uploaded Images
                            </h2>
                            <p class="section-subtitle">Manage uploaded images and media</p>
                        </div>
                        <div class="table-container">
                            <div class="table-wrapper">
                                <div class="table-actions">
                                    <button onclick="handleButton()" class="btn btn-primary">
                                        <i class="fas fa-arrow-left"></i>
                                        Back to Dashboard
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Image ID</th>
                                                <th>Title</th>
                                                <th>Description</th>
                                                <th>Path</th>
                                                <th>Upload Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($images as $image): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($image['img_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($image['img_title']); ?></td>
                                                    <td><?php echo htmlspecialchars(concatStrings($image['img_description'])); ?></td>
                                                    <td><?php echo htmlspecialchars(concatStrings($image['img_path'])); ?></td>
                                                    <td><?php echo htmlspecialchars($image['uploaded_on']); ?></td>
                                                    <td>
                                                        <a onclick="return confirm('Are you sure you want to delete this image?');"
                                                            href="../deleteimage.php?img_id=<?php echo $image['img_id']; ?>"
                                                            class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- PDF Files Table -->
                    <div class="data-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i class="fas fa-file-pdf"></i>
                                Uploaded PDF Files
                            </h2>
                            <p class="section-subtitle">Manage uploaded PDF documents</p>
                        </div>
                        <div class="table-container">
                            <div class="table-wrapper">
                                <div class="table-actions">
                                    <button onclick="handleButton()" class="btn btn-primary">
                                        <i class="fas fa-arrow-left"></i>
                                        Back to Dashboard
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>PDF ID</th>
                                                <th>Title</th>
                                                <th>Path</th>
                                                <th>Upload Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pdfS as $pdf): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($pdf['pdf_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($pdf['pdf_title']); ?></td>
                                                    <td><?php echo htmlspecialchars($pdf['pdf_path']); ?></td>
                                                    <td><?php echo htmlspecialchars($pdf['upload_date']); ?></td>
                                                    <td>
                                                        <a onclick="return confirm('Are you sure you want to delete this PDF?');"
                                                            href="../deletepdf.php?pdf_id=<?php echo $pdf['pdf_id']; ?>"
                                                            class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>
    <script src="js/dashboard.js"></script>
    <script src="js/viewall.js"></script>
</body>

</html>