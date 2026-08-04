<?php
include '../Database/db.php';
session_start();
if (!isset($_SESSION['username'])) {

  header("Location: ../login/login.php");
  exit();
}

$db = new Db();
$connection = $db->connect();

$coverid = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : null;

if ($coverid === null) {
    echo "Invalid press post ID.";
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Press Cover Image Upload — ATMABISWAS Admin</title>
  <link rel="icon" type="image/png" href="../images/logo/logo.png">
  <script src="https://cdn.tailwindcss.com"></script>

  <link rel="stylesheet" href="css/uploadfile.css">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

</head>

<body class="bg-gray-100 overflow-x-hidden">
  <div class="flex h-screen">
    <div class="flex overflow-y-hidden">
      <?php include 'sidebar.php' ?>
    </div>
    <!-- Main Content -->
    <div class="flex justify-center h-screen">
      <!-- Content Area -->
      <div class="upload-container w-screen h-screen">
        <form action="../../blogimg_process.php?id=<?= $coverid ?>" method="POST" enctype="multipart/form-data">

          <div class="upload-section image-upload">
            <div class="mb-3">
              <i class="bi bi-image fs-1 text-primary"></i>
              <h4 class="my-3">Upload Image</h4>
              <p class="text-muted">Supported formats: JPG, JPEG
                (Max 2MB)</p>
              <label for="imageUpload" class="btn mt-2 btn-primary px-2">
                Choose Image
                <input type="file" id="imageUpload" name="image_file" class="file-input"
                  accept=".jpg, .jpeg, .png">
              </label>
              <div class="preview-container" id="imagePreview">
                <img src="#" class="img-thumbnail mt-2" alt="Image preview" style="max-height: 200px;">
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">News Image Title</label>
            <input class="form-control" id="description" name="img_title"
              placeholder="Add Image Title..." />
          </div>

          <div class="mb-3">
      <label for="blog_source" class="form-label">News Source</label>
        <input type="url" class="form-control" id="blog_source" name="blog_source"
          placeholder="Enter the source URL (e.g., https://example.com)" />

          </div>


          <button type="submit" class="btn btn-success w-100 py-2">
            <i class="bi bi-cloud-upload me-2"></i>Upload Files
          </button>
        </form>
      </div>

    </div>
  </div>
  <script src="js/dashboard.js"></script>



  <script>
    document.getElementById('imageUpload').addEventListener('change', function(e) {
      const preview = document.getElementById('imagePreview');
      const file = e.target.files[0];

      if (file) {
        preview.style.display = 'block';
        const reader = new FileReader();

        reader.onload = function(event) {
          preview.querySelector('img').src = event.target.result;
        }

        reader.readAsDataURL(file);
      }
    });
  </script>

</body>

</html>