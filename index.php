<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="ATMABISWAS">
    <title>ATMABISWAS – Official NGO Bangladesh | আত্মবিশ্বাস | Since 1991</title>
    <link rel="icon" type="image/png" href="LOGO/NGO_logo_monogram.png">
    <?php include 'seo.php'; ?>
    <link rel="stylesheet" href="index.css?v=<?php echo filemtime(__DIR__ . '/index.css'); ?>">
    <link rel="stylesheet" href="partners.css?v=<?php echo filemtime(__DIR__ . '/partners.css'); ?>">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-EZVV9DWWY7"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-EZVV9DWWY7');
    </script>
</head>

<body>

    <?php include 'Navbar.php' ?>
    <main>
    <!-- <br> -->
    <?php include 'imageSlider.php' ?>

    <h1 style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;">ATMABISWAS (আত্মবিশ্বাস) – Official NGO Bangladesh, Empowering Rural Communities Since 1991</h1>

    <div class="Numbercontainer">
        <div class="numbers">
            <div class="number-card">
                <div class="number-icon"><i class="fas fa-users"></i></div>
                <h2 id="number1">0</h2>
                <p data-en="Employees" data-bn="কর্মী">Employees</p>
            </div>
            <div class="number-card">
                <div class="number-icon"><i class="fas fa-hands-helping"></i></div>
                <h2 id="number2">0</h2>
                <p data-en="Served Clients" data-bn="সেবাগ্রহীতা">Served Clients</p>
            </div>
            <div class="number-card">
                <div class="number-icon"><i class="fas fa-code-branch"></i></div>
                <h2 id="number3">0</h2>
                <p data-en="Branches" data-bn="শাখা">Branches</p>
            </div>
            <div class="number-card">
                <div class="number-icon"><i class="fas fa-calendar-check"></i></div>
                <h2 id="number4">0</h2>
                <p data-en="Years of Foundation" data-bn="প্রতিষ্ঠার বছর">Years of Foundation</p>
            </div>
        </div>
    </div>
    <div class="sectionalparts">
        <h2 data-en="Our Goals" data-bn="আমাদের লক্ষ্য">Our Goals</h2>
        <p><span data-en="Our mission is to work for progressive social transformation with the aim of institutionalizing a society that place"
                 data-bn="আমাদের লক্ষ্য হলো প্রগতিশীল সামাজিক রূপান্তরের জন্য কাজ করা, যার উদ্দেশ্য এমন একটি সমাজ প্রতিষ্ঠা করা যেখানে">Our mission is to work for progressive social transformation with the aim of institutionalizing a society
            that place</span> <strong data-en="harmony, peace, justice and ecological balance together"
                    data-bn="সম্প্রীতি, শান্তি, ন্যায়বিচার ও পরিবেশগত ভারসাম্য একসাথে বিরাজ করে">harmony, peace, justice and ecological balance together</strong></p>


    </div>


    <div class="sectionalpartsnew">
        <h2 data-en="Latest" data-bn="সাম্প্রতিক">Latest</h2>
        <p><strong data-en="Find the latest news of ATMABISWAS here." data-bn="এখানে আত্মবিশ্বাসের সাম্প্রতিক সংবাদ দেখুন।">Find the latest news of ATMABISWAS here.</strong></p>
    </div>

    <?php include 'test.php' ?>



    <?php include 'joinwithus.php' ?>
    <?php include 'partners.html' ?>

    </main>
    <?php include 'footer.php' ?>



    <script src="index.js?v=<?php echo filemtime(__DIR__ . '/index.js'); ?>"></script>
</body>

</html>