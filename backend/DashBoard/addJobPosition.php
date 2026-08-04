<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login/loging.php");
    exit();
}

include '../Database/db.php';
$db         = new Db();
$connection = $db->connect();

// Load existing sectors for datalist suggestions
$sectorStmt = $connection->prepare("SELECT sector_name FROM sectors ORDER BY sector_name ASC");
$sectorStmt->execute();
$sectors = $sectorStmt->fetchAll(PDO::FETCH_ASSOC);

// Load existing job positions for reference table
$posStmt = $connection->prepare("SELECT JobTitle, JobCode FROM jobcodes ORDER BY JobTitle ASC");
$posStmt->execute();
$existingPositions = $posStmt->fetchAll(PDO::FETCH_ASSOC);

$successMsg = $_GET['success'] ?? '';
$errorMsg   = $_GET['error']   ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Job Position - ATMABISWAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/uploadfile.css">
    <link rel="stylesheet" href="css/admin-sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="../images/logo/logo.png">
    <style>
        .form-section { padding: 24px; }
        .form-container {
            max-width: 640px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            padding: 32px;
        }
        .form-title { font-size: 1.2rem; font-weight: 600; color: #1e293b; margin-bottom: 4px; }
        .form-description { font-size: .875rem; color: #64748b; margin-bottom: 24px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: .875rem; font-weight: 500; color: #374151; margin-bottom: 6px; }
        .form-input, .form-select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: .9rem;
            outline: none;
            transition: border-color .2s;
        }
        .form-input:focus, .form-select:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
        .hint { font-size: .8rem; color: #94a3b8; margin-top: 4px; }
        .btn-submit {
            display: inline-flex; align-items: center; gap: 8px;
            background: #4f46e5; color: #fff; padding: 10px 22px;
            border: none; border-radius: 8px; font-size: .9rem;
            cursor: pointer; transition: background .2s;
        }
        .btn-submit:hover { background: #4338ca; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: .875rem; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .positions-table { margin-top: 36px; }
        .positions-table h3 { font-size: 1rem; font-weight: 600; color: #1e293b; margin-bottom: 12px; }
        .positions-table table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        .positions-table th { background: #f8fafc; padding: 10px 12px; text-align: left; border-bottom: 2px solid #e2e8f0; color: #475569; }
        .positions-table td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .positions-table tr:hover td { background: #f8fafc; }
        .code-badge {
            display: inline-block; background: #ede9fe; color: #6d28d9;
            padding: 2px 8px; border-radius: 4px; font-size: .8rem; font-weight: 500;
        }
        .empty-hint { color: #94a3b8; font-style: italic; font-size: .85rem; }
    </style>
</head>
<body class="bg-gray-50">
<div class="dashboard-container">
    <div class="sidebar-container">
        <?php include 'sidebar.php' ?>
    </div>

    <div class="main-content">
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <h1 class="page-title"><i class="fas fa-briefcase"></i> Add Job Position</h1>
                    <p class="page-subtitle">Add new job positions and sectors to the system</p>
                </div>
                <div class="header-actions">
                    <a href="createjob.php" class="btn btn-outline">
                        <i class="fas fa-plus"></i> Create Job Post
                    </a>
                </div>
            </div>
        </div>

        <section class="form-section">
            <div class="form-container">

                <?php if ($successMsg === 'added'): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> Job position added successfully.</div>
                <?php elseif ($successMsg === 'exists'): ?>
                    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> That job position already exists.</div>
                <?php elseif ($errorMsg === 'empty'): ?>
                    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Job Position Title is required.</div>
                <?php elseif ($errorMsg === 'db'): ?>
                    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> A database error occurred. Please try again.</div>
                <?php endif; ?>

                <form action="../addJob_processing.php" method="POST" class="job-form" id="addJobForm">
                    <h2 class="form-title"><i class="fas fa-plus-circle" style="color:#4f46e5"></i> New Job Position</h2>
                    <p class="form-description">The Job Position Title is required. Sector is optional — pick an existing one or type a new name to create it.</p>

                    <!-- Job Title -->
                    <div class="form-group">
                        <label for="jobtitle" class="form-label">
                            <i class="fas fa-briefcase" style="color:#4f46e5"></i> Job Position Title <span style="color:red">*</span>
                        </label>
                        <input
                            type="text"
                            id="jobtitle"
                            name="jobtitle"
                            class="form-input"
                            placeholder="e.g. Field Officer, Microfinance Officer"
                            required />
                    </div>

                    <!-- Sector (datalist — suggests existing, allows new) -->
                    <div class="form-group">
                        <label for="jobsector" class="form-label">
                            <i class="fas fa-building" style="color:#4f46e5"></i> Job Sector <span style="color:#94a3b8">(optional)</span>
                        </label>
                        <input
                            type="text"
                            id="jobsector"
                            name="jobsector"
                            class="form-input"
                            list="sectors-list"
                            placeholder="Select an existing sector or type a new one..." />
                        <datalist id="sectors-list">
                            <?php foreach ($sectors as $s): ?>
                                <option value="<?= htmlspecialchars($s['sector_name']) ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <p class="hint">Existing sectors are shown as suggestions. Type a new name to add a new sector.</p>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus"></i> Add Job Position
                    </button>
                </form>

                <!-- Existing job positions table -->
                <div class="positions-table">
                    <h3><i class="fas fa-list" style="color:#4f46e5"></i> Existing Job Positions</h3>
                    <?php if (empty($existingPositions)): ?>
                        <p class="empty-hint">No job positions added yet.</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Job Position Title</th>
                                    <th>Job Code</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($existingPositions as $i => $pos): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= htmlspecialchars($pos['JobTitle']) ?></td>
                                        <td><span class="code-badge"><?= htmlspecialchars($pos['JobCode']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

            </div>
        </section>
    </div>
</div>

<script src="js/dashboard.js"></script>
<script>
    document.getElementById('addJobForm').addEventListener('submit', function (e) {
        const title = document.getElementById('jobtitle').value.trim();
        if (title === '') {
            e.preventDefault();
            alert('Job Position Title is required.');
        }
    });
</script>
</body>
</html>
