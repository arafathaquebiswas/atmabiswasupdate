<?php
session_start();
include '../Database/db.php';

if (isset($_SESSION['username'])) {
    $database = new Db();
    $connection = $database->connect();

    $sql = "SELECT * FROM jobs";

    $stmt = $connection->prepare($sql);
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: ../login/loging.php");
}



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Jobs - ATMABISWAS</title>
    <link rel="stylesheet" href="css/updatejobs.css">
    <link rel="stylesheet" href="css/admin-sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../images/logo/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .toggle-row { display:flex; align-items:center; gap:8px; margin-top:10px; }
        .toggle-row .toggle-label { font-size:.78rem; color:#64748b; }
        .toggle-switch { position:relative; display:inline-block; width:40px; height:22px; flex-shrink:0; }
        .toggle-switch input { opacity:0; width:0; height:0; }
        .slider { position:absolute; cursor:pointer; inset:0; background:#cbd5e1; transition:.3s; border-radius:22px; }
        .slider:before { position:absolute; content:""; height:16px; width:16px; left:3px; bottom:3px; background:#fff; transition:.3s; border-radius:50%; }
        .toggle-switch input:checked + .slider { background:#22c55e; }
        .toggle-switch input:checked + .slider:before { transform:translateX(18px); }
        .toggle-status { font-size:.75rem; font-weight:600; }
        .toggle-status.on  { color:#16a34a; }
        .toggle-status.off { color:#dc2626; }
    </style>
</head>

<body class="bg-gray-50">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar-container">
            <?php include 'sidebar.php' ?>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header Section -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-left">
                        <h1 class="page-title">
                            <i class="fas fa-briefcase"></i>
                            Job Management
                        </h1>
                        <p class="page-subtitle">Manage and update job postings</p>
                    </div>
                    <div class="header-right">
                        <div class="stats-summary">
                            <div class="stat-item">
                                <span class="stat-number"><?php echo count($res); ?></span>
                                <span class="stat-label">Total Jobs</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jobs Section -->
            <section class="jobs-section">
                <div class="job-grid">
                    <?php
                    foreach ($res as $r) {
                        $endDate = new DateTime($r['deadline']);
                        $currentDate = new DateTime();
                        $interval = $currentDate->diff($endDate);
                        $remainingDates = $interval->days;
                        $isActive = $endDate > $currentDate;
                    ?>

                        <div class="job-card <?php echo $isActive ? 'active' : 'expired'; ?>">
                            <div class="job-card-header">
                                <div class="job-status">
                                    <?php if ($isActive): ?>
                                        <span class="status-badge active">
                                            <i class="fas fa-circle"></i> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge expired">
                                            <i class="fas fa-circle"></i> Expired
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="job-id">#<?php echo htmlspecialchars($r['job_id']); ?></div>
                            </div>

                            <div class="job-card-body">
                                <h3 class="job-title">
                                    <?php if ($isActive): ?>
                                        <a href="../career/jobdes.php?id=<?php echo htmlspecialchars($r['job_id']); ?>&deptCode=<?php echo htmlspecialchars($r['job_code']); ?>">
                                            <?php echo htmlspecialchars($r['job_title']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="expired-title"><?php echo htmlspecialchars($r['job_title']); ?></span>
                                    <?php endif; ?>
                                </h3>

                                <div class="job-details">
                                    <div class="detail-item">
                                        <i class="fas fa-building"></i>
                                        <span><?php echo htmlspecialchars($r['job_dept']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span><?php echo htmlspecialchars($r['salary_range']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-user-tie"></i>
                                        <span><?php echo htmlspecialchars($r['job_experience']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-briefcase"></i>
                                        <span>For jobs in <?php echo htmlspecialchars($r['job_dept']); ?> sector</span>
                                    </div>
                                </div>

                                <div class="job-deadline">
                                    <?php if ($isActive): ?>
                                        <div class="deadline-info active">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo $remainingDates; ?> days remaining</span>
                                        </div>
                                    <?php else: ?>
                                        <div class="deadline-info expired">
                                            <i class="fas fa-times-circle"></i>
                                            <span>Application closed</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="job-card-footer">
                                <div class="admin-actions">
                                    <a href="updatejob.php?id=<?php echo htmlspecialchars($r['job_id']); ?>&deptCode=<?php echo htmlspecialchars($r['job_code']); ?>"
                                        class="btn btn-update">
                                        <i class="fas fa-edit"></i> Update
                                    </a>
                                    <a href="deletejob.php?id=<?php echo htmlspecialchars($r['job_id']); ?>&deptCode=<?php echo htmlspecialchars($r['job_code']); ?>"
                                        onclick="return confirm('Are you sure you want to delete this job?')"
                                        class="btn btn-delete">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                                <?php $applyEnabled = ($r['apply_enabled'] ?? 1) ? true : false; ?>
                                <div class="toggle-row">
                                    <span class="toggle-label">Apply Now:</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" class="apply-toggle"
                                            data-jobid="<?php echo (int)$r['job_id']; ?>"
                                            <?php echo $applyEnabled ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                    <span class="toggle-status <?php echo $applyEnabled ? 'on' : 'off'; ?>">
                                        <?php echo $applyEnabled ? 'Enabled' : 'Disabled'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                    <?php } ?>

                </div>
            </section>

        </div>
    </div>

    <script>
        // Refresh jobs functionality
        function refreshJobs() {
            location.reload();
        }

        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const jobCards = document.querySelectorAll('.job-card');

            // Animate cards on load
            jobCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Add hover effects
            jobCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
    <script src="js/dashboard.js"></script>
    <script>
    document.querySelectorAll('.apply-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const jobId   = this.dataset.jobid;
            const enabled = this.checked ? 1 : 0;
            const status  = this.closest('.toggle-row').querySelector('.toggle-status');
            const formData = new FormData();
            formData.append('job_id',       jobId);
            formData.append('apply_enabled', enabled);
            fetch('Actions/toggle_apply.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        status.textContent = enabled ? 'Enabled' : 'Disabled';
                        status.className   = 'toggle-status ' + (enabled ? 'on' : 'off');
                    } else {
                        this.checked = !this.checked;
                    }
                })
                .catch(() => { this.checked = !this.checked; });
        });
    });
    </script>
</body>

</html>