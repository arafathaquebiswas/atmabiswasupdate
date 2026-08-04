<?php
include '../Database/db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login/loging.php");
    exit();
}
$db = new Db();
$connection = $db->connect();
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($job_id <= 0) {
    header("Location: updatejobs.php");
    exit();
}

$sql = "SELECT * FROM jobs WHERE job_id = :job_id";
$stmt = $connection->prepare($sql);
$stmt->bindParam(":job_id", $job_id, PDO::PARAM_INT);
$stmt->execute();
$existing = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($existing)) {
    header("Location: updatejobs.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedValue = [
        ':job_title'       => !empty($_POST['job_title'])       ? $_POST['job_title']       : $existing[0]['job_title'],
        ':deadline'        => !empty($_POST['deadline'])         ? $_POST['deadline']         : $existing[0]['deadline'],
        ':job_dept'        => !empty($_POST['job_dept'])         ? $_POST['job_dept']         : $existing[0]['job_dept'],
        ':job_location'    => !empty($_POST['job_location'])     ? $_POST['job_location']     : $existing[0]['job_location'],
        ':salary_range'    => !empty($_POST['salary_range'])     ? $_POST['salary_range']     : $existing[0]['salary_range'],
        ':job_experience'  => !empty($_POST['job_experience'])   ? $_POST['job_experience']   : $existing[0]['job_experience'],
        ':job_skillset'    => !empty($_POST['job_skillset'])     ? $_POST['job_skillset']     : $existing[0]['job_skillset'],
        ':job_description' => !empty($_POST['job_description'])  ? $_POST['job_description']  : $existing[0]['job_description'],
        ':job_req'         => !empty($_POST['job_req'])          ? $_POST['job_req']          : $existing[0]['job_req'],
        ':job_benefits'    => !empty($_POST['job_benefits'])     ? $_POST['job_benefits']     : $existing[0]['job_benefits'],
        ':vacancy'         => !empty($_POST['vacancy'])          ? $_POST['vacancy']          : $existing[0]['vacancy'],
        ':bdjobs_link'     => trim($_POST['bdjobs_link'] ?? ''),
        ':apply_enabled'   => isset($_POST['apply_enabled']) ? 1 : 0,
        ':where_job_id'    => $job_id,
    ];

    $newstring = [];
    foreach ($updatedValue as $key => $value) {
        if ($key === ':where_job_id') continue;
        $field = ltrim($key, ':');
        $newstring[] = "$field = $key";
    }

    $sqli = "UPDATE jobs SET " . implode(", ", $newstring) . " WHERE job_id = :where_job_id";
    $stmt1 = $connection->prepare($sqli);
    $stmt1->execute($updatedValue);

    // Always redirect after POST — even if no fields changed, the user expects
    // to return to the listing. Previously, rowCount() == 0 caused a silent
    // no-redirect that left the admin stuck on the form with no feedback.
    header("Location: updatejobs.php");
    exit();
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Update Job - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="css/admin-sidebar.css">
    <link rel="stylesheet" href="css/createjob.css">
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
                    <form method="POST" action="">
                        <div class="formfirst">
                            <header>Update Job Post (Job id: <span style="color: blue;"> <?php echo $job_id; ?> </span> )
                            </header>
                            <div class="details personal">
                                <span class="title">Job Details</span>
                                <div class="fields">
                                    <!-- Job title -->

                                    <div class="input-field">
                                        <label>Job Position</label>
                                        <select id="jobPosition" name="job_title">
                                            <option value="">Select Position..</option>

                                        </select>
                                    </div>


                                    <!-- Job code -->


                                    <input type="hidden" id="jobcode" name="job_code">



                                    <div class="input-field">
                                        <label>Application Deadline</label>
                                        <input name="deadline" type="date" placeholder="Enter Application Deadline">
                                    </div>

                                    <div class="input-field">
                                        <label>Job Sector</label>
                                        <select name="job_dept">
                                            <option disabled selected>Select Sector</option>
                                            <?php

                                            $sql = "SELECT sector_name FROM sectors ORDER BY sector_name ASC";

                                            $stmtSql =

                                                $connection->prepare($sql);

                                            $stmtSql->execute();

                                            $res = $stmtSql->fetchAll(PDO::FETCH_ASSOC);

                                            print_r($res);

                                            foreach ($res as $r) {
                                                echo '<option value="' . htmlspecialchars($r["sector_name"]) . '">' . htmlspecialchars($r["sector_name"]) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>


                                    <div class="input-field">
                                        <label>Job Location</label>
                                        <input name="job_location" type="text" placeholder="Enter Job Location">
                                    </div>

                                    <div class="input-field">
                                        <label>Salary Range</label>
                                        <input name="salary_range" type="text" placeholder="BDT 000 - BDT 999">
                                    </div>

                                    <div class="input-field">
                                        <label>Vacancy</label>
                                        <input name="vacancy" type="text" placeholder="1">
                                    </div>

                                </div>
                            </div>
                            <div class="details ID">
                                <span class="title">Job Details</span>
                                <div>
                                    <div class="fields">
                                        <div class="spinput-field">
                                            <label> Job Experience</label>
                                            <input name="job_experience" type="text"
                                                placeholder="Experience required: eg: 5 years">
                                        </div>
                                        <div class="spinput-field">
                                            <label>Job Skillset</label>
                                            <input name="job_skillset" type="text"
                                                placeholder="eg: PHP, JavaScript, MySQL, REST APIs, Frontend frameworks (React or Angular).">
                                        </div>
                                    </div>
                                    <div class="fields">
                                        <div class="spinput-field">
                                            <label>Job Description</label>
                                            <textarea name="job_description"
                                                placeholder="Use fullstop(.) at the end of a description."></textarea>
                                        </div>
                                        <div class="spinput-field">
                                            <label>Job Requirements</label>
                                            <textarea name="job_req"
                                                placeholder="Use fullstop(.) at the end of a Requirement."></textarea>
                                        </div>
                                        <div class="spinput-field">
                                            <label>Job Benefits</label>
                                            <textarea name="job_benefits"
                                                placeholder="Use fullstop(.) at the end of a Beneifit."></textarea>
                                        </div>
                                    </div>
                                    <div class="fields">
                                        <div class="spinput-field">
                                            <label>BD Jobs Link <span style="font-weight:400;color:#94a3b8;">(optional)</span></label>
                                            <input name="bdjobs_link" type="url"
                                                value="<?php echo htmlspecialchars($existing[0]['bdjobs_link'] ?? ''); ?>"
                                                placeholder="https://jobs.bdjobs.com/jobdetails.asp?id=...">
                                            <small style="color:#94a3b8;">Clear the field to remove the Bdjobs button from the career page.</small>
                                        </div>
                                        <div class="spinput-field" style="display:flex;flex-direction:column;justify-content:center;">
                                            <label>Apply Now Button</label>
                                            <label style="display:flex;align-items:center;gap:10px;margin-top:8px;cursor:pointer;">
                                                <input type="checkbox" name="apply_enabled" value="1"
                                                    <?php echo ($existing[0]['apply_enabled'] ?? 1) ? 'checked' : ''; ?>
                                                    style="width:18px;height:18px;accent-color:#4f46e5;cursor:pointer;">
                                                <span style="font-size:.9rem;color:#374151;">Enable "Apply Now" on the career page</span>
                                            </label>
                                            <small style="color:#94a3b8;margin-top:4px;">Uncheck to hide the Apply Now button publicly.</small>
                                        </div>
                                    </div>

                                </div>

                            </div>

                            <button type="submit" class="nextBtn">
                                <span class="btnText">Update Job</span>
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script src="js/dashboard.js"></script>
    <script src="js/jobSelection.js"></script>

</body>

</html>