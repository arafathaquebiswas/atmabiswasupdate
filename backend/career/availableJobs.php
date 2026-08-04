<?php
include '../Database/db.php';

$database = new Db();
$connection = $database->connect();

$sql = "SELECT * FROM jobs";
$stmt = $connection->prepare($sql);
$stmt->execute();

$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Jobs - ATMABISWAS</title>
    <link rel="stylesheet" href="css/modern-jobs.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../../LOGO/NGO_logo_monogram.png">
</head>

<body>
    <?php include '../../Navbar.php'; ?>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <h1 class="page-title">
                        <i class="fas fa-briefcase"></i>
                        Available Jobs
                    </h1>
                    <p class="page-subtitle">Find your perfect opportunity at ATMABISWAS</p>
                </div>
                <div class="header-right">
                    <div class="stats-summary">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo count($res); ?></span>
                            <span class="stat-label">Open Positions</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-section">
            <div class="search-wrapper">
                <div class="search-input-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" placeholder="Search job titles, skills, or keywords...">
                </div>
                <div class="filter-group">
                    <select id="locationFilter" class="filter-select">
                        <option value="">All Locations</option>
                        <?php
                        $locations = array_unique(array_column($res, 'job_location'));
                        foreach ($locations as $location) {
                            if (!empty($location)) {
                                echo '<option value="' . htmlspecialchars($location) . '">' . htmlspecialchars($location) . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <button class="clear-filters" onclick="clearFilters()">
                        <i class="fas fa-times"></i>
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>



        <!-- Jobs Grid -->
        <div class="jobs-grid" id="jobsContainer">
            <?php if (count($res) === 0): ?>
                <div class="no-jobs">
                    <i class="fas fa-briefcase"></i>
                    <h3>No Jobs Available</h3>
                    <p>Check back later for new opportunities</p>
                </div>
            <?php else: ?>
                <?php foreach ($res as $r): ?>
                    <div class="job-card">
                        <div class="job-header">
                            <h3 class="job-title">
                                <a href="jobdes.php?id=<?php echo htmlspecialchars($r['job_id']); ?>&deptCode=<?php echo htmlspecialchars($r['job_code']); ?>">
                                    <?php echo htmlspecialchars($r['job_title']); ?>
                                </a>
                            </h3>
                            <span class="job-id">#<?php echo htmlspecialchars($r['job_id']); ?></span>
                        </div>

                        <div class="job-company">
                            <i class="fas fa-building"></i>
                            <span>ATMABISWAS</span>
                        </div>

                        <div class="job-details">
                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($r['job_location']); ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-money-bill-wave"></i>
                                <span><?php echo htmlspecialchars($r['salary_range']); ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <span><?php echo htmlspecialchars($r['job_experience']); ?></span>
                            </div>
                        </div>

                        <div class="job-tags">
                            <span class="tag tag-fulltime">Full-time</span>
                            <?php
                            $skills = explode(",", $r['job_skillset']);
                            foreach ($skills as $skill):
                                $skill = trim($skill);
                                if (!empty($skill)):
                            ?>
                                    <span class="tag tag-skill"><?php echo htmlspecialchars($skill); ?></span>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </div>

                        <div class="job-actions">
                            <a href="jobdes.php?id=<?php echo htmlspecialchars($r['job_id']); ?>&deptCode=<?php echo htmlspecialchars($r['job_code']); ?>" class="btn btn-primary">
                                <i class="fas fa-eye"></i>
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterJobs() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const locationFilter = document.getElementById('locationFilter').value;
            const jobCards = document.querySelectorAll('.job-card');

            jobCards.forEach(card => {
                const title = card.querySelector('.job-title a').textContent.toLowerCase();
                const company = card.querySelector('.job-company span').textContent.toLowerCase();
                const location = card.querySelector('.detail-item:first-child span').textContent;

                const matchesSearch = title.includes(searchInput) || company.includes(searchInput);
                const matchesLocation = locationFilter === '' || location === locationFilter;

                card.style.display = matchesSearch && matchesLocation ? 'block' : 'none';
            });
        }

        // Clear all filters
        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('locationFilter').value = '';
            filterJobs();
        }

        // Event listeners
        document.getElementById('searchInput').addEventListener('input', filterJobs);
        document.getElementById('locationFilter').addEventListener('change', filterJobs);
    </script>
</body>

</html>