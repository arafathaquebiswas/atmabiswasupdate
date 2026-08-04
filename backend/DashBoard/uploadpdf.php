<?php
include '../Database/db.php';
session_start();
if (!isset($_SESSION['username'])) {

    header("Location: ../login/loging.php");
    exit();
}

$db = new Db();
$connection = $db->connect();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Upload PDF - ATMABISWAS</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/uploadfile.css">
    <link rel="stylesheet" href="css/admin-sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <!-- Header Section -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-left">
                        <h1 class="page-title">
                            <i class="fas fa-file-pdf"></i>
                            Upload PDF
                        </h1>
                        <p class="page-subtitle">Upload PDF documents and notices</p>
                    </div>
                </div>
            </div>

            <!-- Upload Section -->
            <section class="upload-section">
                <div class="upload-container">
                    <form action="../../uploadpdf_process.php" method="POST" enctype="multipart/form-data" class="upload-form">
                        <div class="form-header">
                            <h2 class="form-title">
                                <i class="fas fa-cloud-upload-alt"></i>
                                Upload PDF Document
                            </h2>
                            <p class="form-description">Upload PDF documents and notices for the website</p>
                        </div>

                        <div class="upload-area">
                            <div class="upload-zone pdf-upload-zone">
                                <div class="upload-icon">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <h3 class="upload-title">Choose PDF File</h3>
                                <p class="upload-info">Max file size: 10MB</p>
                                <label for="pdfUpload" class="upload-btn pdf-btn">
                                    <i class="fas fa-folder-open"></i>
                                    Browse PDF Files
                                    <input type="file" id="pdfUpload" name="pdf_file" class="file-input"
                                        accept="application/pdf" required>
                                </label>
                                <div class="preview-container pdf-preview" id="pdfPreview">
                                    <div class="pdf-preview-content">
                                        <i class="fas fa-file-pdf"></i>
                                        <p class="filename"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-body">
                            <div class="form-group">
                                <label for="pdf_title" class="form-label">
                                    <i class="fas fa-heading"></i>
                                    PDF Title
                                </label>
                                <input
                                    type="text"
                                    id="pdf_title"
                                    name="pdf_title"
                                    class="form-input"
                                    placeholder="Enter PDF title..."
                                    required />
                            </div>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i>
                                Upload PDF
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
    <script src="js/dashboard.js"></script>



    <script>
        // PDF preview functionality
        document.getElementById('pdfUpload').addEventListener('change', function(e) {
            const preview = document.getElementById('pdfPreview');
            const file = e.target.files[0];

            if (file) {
                preview.style.display = 'block';
                preview.querySelector('.filename').textContent = file.name;
            }
        });

        // Drag and drop functionality
        const uploadZone = document.querySelector('.pdf-upload-zone');

        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');

            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type === 'application/pdf') {
                document.getElementById('pdfUpload').files = files;
                document.getElementById('pdfUpload').dispatchEvent(new Event('change'));
            }
        });

        // Form animations
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.upload-form');
            form.style.opacity = '0';
            form.style.transform = 'translateY(20px)';

            setTimeout(() => {
                form.style.transition = 'all 0.5s ease';
                form.style.opacity = '1';
                form.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>

</body>

</html>