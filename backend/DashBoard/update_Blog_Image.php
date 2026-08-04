<?php
require_once '../Database/db.php';
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: ../login/loging.php");
  exit();
}

// Use the centralized database connection
$connection = getDB();

$coverid = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : null;

if ($coverid === null) {
  echo "Invalid blog ID.";
  exit();
}

// Get blog details for better context
$blogQuery = "SELECT blog_title, cover_img FROM blogs WHERE blog_id = ?";
$blogStmt = $connection->prepare($blogQuery);
$blogStmt->execute([$coverid]);
$blogData = $blogStmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Update Press Cover Image — ATMABISWAS Admin</title>
  <link rel="icon" type="image/png" href="../images/logo/logo.png">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/admin-sidebar.css">
  <link rel="stylesheet" href="css/update-blog-image.css">
</head>

<body>
  <div class="dashboard-layout">
    <!-- Sidebar -->
    <div class="sidebar-container">
      <?php include 'sidebar.php' ?>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Page Header -->
      <div class="page-header">
        <div class="header-content">
          <div class="header-left">
            <h1><i class="fas fa-image"></i> Update Press Cover Image</h1>
            <p class="page-subtitle">Update the cover image for your press post</p>
          </div>

        </div>
      </div>

      <!-- Content Container -->
      <div class="content-container">
        <div class="update-form-wrapper">
          <!-- Blog Info Card -->
          <div class="blog-info-card">
            <div class="blog-info-header">
              <i class="fas fa-newspaper"></i>
              <h3>Press Information</h3>
            </div>
            <div class="blog-info-content">
              <div class="info-item">
                <span class="info-label">Press ID:</span>
                <span class="info-value">#<?= $coverid ?></span>
              </div>
              <?php if ($blogData): ?>
                <div class="info-item">
                  <span class="info-label">Current Title:</span>
                  <span class="info-value"><?= htmlspecialchars($blogData['blog_title']) ?></span>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Update Form -->
          <form action="../../blogimg_process.php?id=<?= $coverid ?>" method="POST" enctype="multipart/form-data" class="update-form">
            <!-- Image Upload Section -->
            <div class="form-section">
              <div class="section-header">
                <i class="fas fa-upload"></i>
                <h3>Upload New Image</h3>
              </div>

              <div class="upload-area" id="uploadArea">
                <div class="upload-content">
                  <i class="fas fa-cloud-upload-alt"></i>
                  <h4>Choose an image file</h4>
                  <p>Drag and drop your image here, or click to browse</p>
                  <span class="file-types">Supported: JPG, JPEG, PNG (Max 2MB)</span>
                </div>
                <input type="file" id="imageUpload" name="image_file" class="file-input" accept=".jpg, .jpeg, .png" required>
              </div>

              <!-- Image Preview -->
              <div class="preview-section" id="imagePreview" style="display: none;">
                <h4>Image Preview</h4>
                <div class="preview-container">
                  <img src="#" alt="Image preview" class="preview-image">
                  <div class="preview-info">
                    <span class="file-name" id="fileName"></span>
                    <span class="file-size" id="fileSize"></span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Form Fields Section -->
            <div class="form-section">
              <div class="section-header">
                <i class="fas fa-edit"></i>
                <h3>Press Details</h3>
              </div>

              <div class="form-group">
                <label for="description" class="form-label">
                  <i class="fas fa-heading"></i>
                  Image Title
                </label>
                <input
                  type="text"
                  id="description"
                  name="img_title"
                  class="form-input"
                  placeholder="Enter a descriptive title for the image..."
                  required />
                <small class="form-help">This title will be used as alt text and for accessibility</small>
              </div>

              <div class="form-group">
                <label for="blog_source" class="form-label">
                  <i class="fas fa-link"></i>
                  Source URL
                </label>
                <input
                  type="url"
                  id="blog_source"
                  name="blog_source"
                  class="form-input"
                  placeholder="https://example.com/news-article" />
                <small class="form-help">Optional: Link to the original source of the image</small>
              </div>
            </div>

            <!-- Submit Section -->
            <div class="form-section submit-section">
              <button type="submit" class="btn btn-primary btn-submit">
                <i class="fas fa-cloud-upload-alt"></i>
                Update Press Cover Image
              </button>


            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="js/dashboard.js"></script>

  <script>
    // File upload handling
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('imageUpload');
    const imagePreview = document.getElementById('imagePreview');
    const previewImage = document.querySelector('.preview-image');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');

    // Drag and drop functionality
    uploadArea.addEventListener('dragover', (e) => {
      e.preventDefault();
      uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
      uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
      e.preventDefault();
      uploadArea.classList.remove('dragover');
      const files = e.dataTransfer.files;
      if (files.length > 0) {
        fileInput.files = files;
        handleFileSelect(files[0]);
      }
    });

    // Click to upload
    uploadArea.addEventListener('click', () => {
      fileInput.click();
    });

    // File input change
    fileInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file) {
        handleFileSelect(file);
      }
    });

    function handleFileSelect(file) {
      // Validate file type
      const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
      if (!validTypes.includes(file.type)) {
        alert('Please select a valid image file (JPG, JPEG, or PNG)');
        return;
      }

      // Validate file size (2MB)
      if (file.size > 2 * 1024 * 1024) {
        alert('File size must be less than 2MB');
        return;
      }

      // Show preview
      const reader = new FileReader();
      reader.onload = function(event) {
        previewImage.src = event.target.result;
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        imagePreview.style.display = 'block';
        uploadArea.classList.add('file-selected');
      };
      reader.readAsDataURL(file);
    }

    function formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Form validation
    document.querySelector('.update-form').addEventListener('submit', function(e) {
      const imageFile = fileInput.files[0];
      const title = document.getElementById('description').value.trim();

      if (!imageFile) {
        e.preventDefault();
        alert('Please select an image file');
        return;
      }

      if (!title) {
        e.preventDefault();
        alert('Please enter an image title');
        return;
      }
    });
  </script>
</body>

</html>