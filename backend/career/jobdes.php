<?php
include '../Database/db.php';
$database = new Db();
$connection = $database->connect();
$connection1 = $database->connect();


$jobId = htmlspecialchars($_GET['id']);
$jobCode = htmlspecialchars($_GET['deptCode']);

$sql = "SELECT * FROM jobs WHERE job_id =:job_id";

$stmt = $connection->prepare($sql);
$stmt->bindParam(":job_id", $jobId);
$stmt->execute();
$jobDes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql1 = "SELECT vacancy FROM jobs WHERE job_id=:job_id;";
$stmt1 = $connection1->prepare($sql1);
$stmt1->bindParam(":job_id", $jobId);
$stmt1->execute();
$deptCode = $stmt1->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details - ATMABISWAS</title>
    <link rel="icon" type="image/png" href="../../LOGO/NGO_logo_monogram.png">
    <link rel="stylesheet" href="css/jobdes.css">
    <style>
        .bdjobs-button {
            display: block;
            width: 100%;
            padding: 12px 20px;
            margin-bottom: 10px;
            background: #e65c00;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
            box-sizing: border-box;
        }
        .bdjobs-button:hover { background: #c44f00; color: #fff; }
        .apply-button-disabled { opacity:.5; cursor:not-allowed; pointer-events:none; }
    </style>
    <?php if (!empty($jobDes)):
        // This page doesn't include seo.php (different, dynamic per-job
        // content), so hiringOrganization is inlined in full rather than
        // referenced by @id — a bare @id reference wouldn't resolve for
        // Google's parser on a page that doesn't itself declare that node.
        $job = $jobDes[0];

        $jobPosting = [
            '@context'    => 'https://schema.org',
            '@type'       => 'JobPosting',
            'title'       => $job['job_title'],
            'description' => $job['job_description'],
            'identifier'  => [
                '@type' => 'PropertyValue',
                'name'  => 'ATMABISWAS',
                'value' => $jobCode,
            ],
            'datePosted'  => $job['PostDate'],
            'validThrough' => $job['deadline'],
            'employmentType' => 'FULL_TIME',
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name'  => 'ATMABISWAS',
                'sameAs' => 'https://atmabiswas.org/',
                'logo'  => 'https://atmabiswas.org/LOGO/NGO_logo_monogram.png',
            ],
            'jobLocation' => [
                '@type'   => 'Place',
                'address' => [
                    '@type'           => 'PostalAddress',
                    'addressLocality' => $job['job_location'],
                    'addressCountry'  => 'BD',
                ],
            ],
        ];

        // Only include baseSalary when the stored value is a real, clean
        // number — "Negotiable" and similar free-text values are common
        // here and schema.org's baseSalary requires a numeric value, so
        // fabricating one would be worse than omitting it.
        $salaryDigits = preg_replace('/[^0-9]/', '', $job['salary_range'] ?? '');
        if ($salaryDigits !== '' && (int) $salaryDigits > 0) {
            $jobPosting['baseSalary'] = [
                '@type'    => 'MonetaryAmount',
                'currency' => 'BDT',
                'value'    => [
                    '@type'    => 'QuantitativeValue',
                    'value'    => (int) $salaryDigits,
                    'unitText' => 'MONTH',
                ],
            ];
        }
        ?>
        <script type="application/ld+json">
            <?= json_encode($jobPosting, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
        </script>
    <?php endif; ?>
</head>

<body>
    <?php include '../../Navbar.php'; ?>
    <div class="container">
        <div class="job-header">
            <h1 class="job-title"><?= htmlspecialchars($jobDes[0]['job_title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
            <div class="company-info">
                <img src="../images/logo/logo.png" alt="Company Logo" class="company-logo">
                <div>
                    <h3>ATMABISWAS.</h3>
                    <p>A non-profitable Organisation</p>
                </div>
            </div>
            <div class="job-meta">
                <div class="meta-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php 
                    if($jobDes[0]['job_location'] ==="Negotiable"){
echo '<span>Location: ' . htmlspecialchars($jobDes[0]["job_location"]) . '</span>';


                    }else{
                        echo '<span>Location: ' . htmlspecialchars($jobDes[0]["job_location"]) . ', Bangladesh</span>';

                    }
                    ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-clock"></i>
                    <span>Full-time</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar-times"></i>
                    <span>DeptCode: <?= $jobCode ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar-times"></i>
                    <span class="deadline">Application Deadline: <?= htmlspecialchars($jobDes[0]['deadline'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                </div>

            </div>
        </div>

        <div class="job-content">
            <div class="main-content">
                <div class="section">
                    <h2>Job Description</h2>
                    <p>We are looking for a skilled <strong
                            style="color:#3498db;"><?= htmlspecialchars($jobDes[0]['job_title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>
                        who has expertise in
                        <strong><?= htmlspecialchars($jobDes[0]['job_skillset'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong> To join our growing team...
                    </p>
                    <br>
                    <ul class="job-description-list">
                        <?php
                        $description = $jobDes[0]['job_description'];

                        // Example enhancement (adjust keywords as needed)
                        $description = htmlspecialchars($description);
                        $description = str_replace("Responsibilities", "<span class='highlight-blue'><strong>Responsibilities</strong></span>", $description);
                        $description = str_replace("Requirements", "<span class='highlight-blue'><strong>Requirements</strong></span>", $description);
                        $description = str_replace("•", "<li>", $description); // convert bullets to list items
                        $description = nl2br($description); // convert newlines to <br>

                        // Close open <li> tags after each line break
                        $description = preg_replace('/<li>(.*?)<br\s*\/?>/i', '<li>$1</li>', $description);

                        echo $description;
                        ?>
                    </ul>

                </div>

                <div class="section">
                    <h2>Requirements</h2>
                    <ul class="job-req-list">
                        <?php
                        $requirements = htmlspecialchars($jobDes[0]['job_req']);

                        // Highlight keywords (add more if needed)
                        $requirements = str_replace("Qualifications", "<span class='highlight-blue'><strong>Qualifications</strong></span>", $requirements);
                        $requirements = str_replace("Experience", "<span class='highlight-blue'><strong>Experience</strong></span>", $requirements);
                        $requirements = str_replace("•", "<li>", $requirements); // Convert bullet points to <li>
                        $requirements = nl2br($requirements); // Convert newlines to <br>

                        // Properly close each <li> tag
                        $requirements = preg_replace('/<li>(.*?)<br\s*\/?>/i', '<li>$1</li>', $requirements);

                        echo $requirements;
                        ?>
                    </ul>

                </div>

                <div class="section">
                    <h2>Benefits</h2>
                    <ul class="job-benefits-list">
                        <?php
                        $benefits = htmlspecialchars($jobDes[0]['job_benefits']);

                        // Highlight keywords
                        $benefits = str_replace("Benefits", "<span class='highlight-blue'><strong>Benefits</strong></span>", $benefits);
                        $benefits = str_replace("Perks", "<span class='highlight-blue'><strong>Perks</strong></span>", $benefits);
                        $benefits = str_replace("•", "<li>", $benefits); // Convert bullets to list items
                        $benefits = nl2br($benefits); // Convert newlines to <br>

                        // Close each <li> after <br>
                        $benefits = preg_replace('/<li>(.*?)<br\s*\/?>/i', '<li>$1</li>', $benefits);

                        echo $benefits;
                        ?>
                    </ul>

                </div>
            </div>

            <div class="sidebar">
                <div class="section">
                    <h2>Job Overview</h2>
                    <div class="meta-item" style="margin-bottom: 15px;">
                        <i class="fas fa-calendar"></i>
                        <span>Posted: <?= htmlspecialchars($jobDes[0]['PostDate'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span>
                    </div>
                    <div class="meta-item" style="margin-bottom: 15px;">
                        <i class="fas fa-users"></i>
                        <span>Vacancy: <?php
                                        echo htmlspecialchars($deptCode[0]['vacancy'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                                        ?> </span>
                    </div>
                    <div class="meta-item" style="margin-bottom: 15px;">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Salary: <?= htmlspecialchars($jobDes[0]['salary_range'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> (Negotiable)</span>
                    </div>
                </div>

                <?php if (!empty($jobDes[0]['bdjobs_link'])): ?>
                    <a href="<?php echo htmlspecialchars($jobDes[0]['bdjobs_link']); ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="bdjobs-button">Bdjobs ↗</a>
                <?php endif; ?>
                <?php if ($jobDes[0]['apply_enabled'] ?? 1): ?>
                    <button class="apply-button" onclick="openApplyModal()">Apply Now</button>
                <?php else: ?>
                    <button class="apply-button apply-button-disabled" disabled>Apply Now (Closed)</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Application Modal -->
    <div class="modal" id="applyModal">
        <div class="modal-content">
            <h2>Apply for Senior Software Engineer</h2>
            <form method="POST" id="applicationForm" action="../../sendingMail.php" enctype="multipart/form-data">
                <div class="form-group">

                    <!-- Hidden input fields to send values -->
                    <input type="hidden" name="job_id" value="<?php echo $jobId ?>">

                    <input type="hidden" name="job_code" value="<?php echo $jobCode ?>">

                    <input type="hidden" value="<?= htmlspecialchars($jobDes[0]['job_title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" name="job-title">


                    <label>Full Name</label>
                    <input name="fullname" type="text" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input name="email" type="email" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input name="phone" type="tel" required>
                </div>
                <div class="form-group">
                    <label>Cover Letter</label>
                    <textarea name="mailbody" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <label>Upload CV</label>
                    <input name="cvfile" type="file" accept=".pdf" required>
                </div>
                <button type="submit" class="apply-button">Submit Application</button>
            </form>
        </div>
    </div>

    <script>
    function openApplyModal() {
        document.getElementById('applyModal').style.display = 'block';
        document.getElementById('applyModal').style.overflowY = 'auto';

    }

    function closeApplyModal() {
        document.getElementById('applyModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('applyModal');
        if (event.target === modal) {
            closeApplyModal();
        }
    }
    </script>
</body>

</html>