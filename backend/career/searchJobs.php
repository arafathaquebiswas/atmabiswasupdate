<?php
include '../Database/db.php';

$database = new Db();

$connection = $database->connect();

$searchItem = trim($_GET['searchItem']);

$search = '%' . str_replace(['%', '_'], ['/%', '/_'], $searchItem) . '%';


$sql = "SELECT * FROM jobs WHERE job_title LIKE :search";

$stmt = $connection->prepare($sql);

$stmt->bindParam(":search", $search, PDO::PARAM_STR);

$stmt->execute();

$res = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Search - ATMABISWAS</title>
    <link rel="icon" type="image/png" href="../../LOGO/NGO_logo_monogram.png">
    <link rel="stylesheet" href="css/avjobs.css">
</head>

<body>
    <?php include '../../Navbar.php'; ?>

    <div class="container">
        <div class="search-section">
            <input type="text" id="searchInput" placeholder="Search job titles or companies...">
            <select id="locationFilter">
                <option value="">All Locations</option>
                <option value="New York">New York</option>
                <option value="London">London</option>
                <option value="Tokyo">Tokyo</option>
                <option value="Remote">Remote</option>
            </select>
            <button class="clear-filters" onclick="clearFilters()">Clear Filters</button>
        </div>
        <?php

        foreach ($res as $r) {

            echo "<div class='jobs-container' id='jobsContainer'>";
            echo "    <!-- Job listings will be here -->";
            echo "    <div class='job-card'>";
            echo "<a href='jobdes.php?id=" . htmlspecialchars($r['job_id']) . "&deptCode=" . htmlspecialchars($r['job_code']) . "' class='job-title'>" . htmlspecialchars($r['job_title']) . "</a>";
            echo "        <p class='company'>" . "ATMABISWAS" . "</p>";
            echo "        <p class='location'>" . htmlspecialchars($r['job_location'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</p>";
            echo "        <p class='salary'>" . htmlspecialchars($r['salary_range'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</p>";
            echo "        <div class='tags'>";
            echo "            <span class='tag'>Full-time</span>";
            $skills = explode(",", $r['job_skillset']);
            foreach ($skills as $skill) {
                echo " <span class='tag'>" . htmlspecialchars($skill, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</span>";
            }

            echo "        </div>";
            echo '<a class="apply-btn-a" href="jobdes.php?id=' . htmlspecialchars($r['job_id']) . '&deptCode=' . htmlspecialchars($r['job_code']) . '">
        View Details
      </a>';
            echo "    </div>";
            echo "</div>";
        }
        ?>
    </div>

    <script>
        function filterJobs() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const locationFilter = document.getElementById('locationFilter').value;
            const jobCards = document.querySelectorAll('.job-card');

            jobCards.forEach(card => {
                const title = card.querySelector('.job-title').textContent.toLowerCase();
                const company = card.querySelector('.company').textContent.toLowerCase();
                const location = card.querySelector('.location').textContent;

                const matchesSearch = title.includes(searchInput) || company.includes(searchInput);
                const matchesLocation = locationFilter === '' || location === locationFilter;

                card.style.display = matchesSearch && matchesLocation ? 'block' : 'none';
            });
        }

        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('locationFilter').value = '';
            filterJobs();
        }

        document.getElementById('searchInput').addEventListener('input', filterJobs);
        document.getElementById('locationFilter').addEventListener('change', filterJobs);
    </script>
</body>

</html>