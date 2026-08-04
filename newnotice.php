<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Clickable PDF Cards</title>
    <style>
    :root {
        --primary-color: #2563eb;
        --primary-light: #3b82f6;
        --secondary-color: #f59e0b;
        --dark-color: #1e293b;
        --light-color: #f8fafc;
        --gray-color: #64748b;
        --card-bg: #ffffff;
        --transition: all 0.3s ease-in-out;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12);
        --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
        --radius-md: 12px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background-color: #f1f5f9;
        color: var(--dark-color);
        font-family: 'Segoe UI', sans-serif;
    }

    header {
        text-align: center;
        background: linear-gradient(90deg, #0a58ca, #176cc6);
        color: white;
        padding: 40px 20px;
    }

    header h1 {
        font-size: 2rem;
        font-weight: 700;
    }

    header p {
        font-size: 1.2rem;
        opacity: 0.9;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .card-link {
        text-decoration: none;
        color: inherit;
        display: block;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-md);
        transition: var(--transition);
        background: var(--card-bg);
        overflow: hidden;
        height: 600px;
        position: relative;
    }

    .card-link:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-4px);
    }

    .card-embed {
        width: 100%;
        height: 100%;
        border: none;
        pointer-events: none;
    }

    .card-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(248, 250, 252, 0.95);
        padding: 1rem;
        text-align: center;
    }

    .card-footer h2 {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }

    .view-btn {
        padding: 0.5rem 1rem;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-size: 0.9rem;
        cursor: pointer;
        transition: var(--transition);
    }

    .view-btn:hover {
        background: var(--primary-light);
    }

    @media (max-width: 768px) {
        .card-link {
            height: 500px;
        }

        header h1 {
            font-size: 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .card-link {
            height: 450px;
        }
    }
    </style>
</head>

<body>

    <header>
        <h1>PDF Viewer Cards</h1>
        <p>Click a card or use the button to view full PDF</p>
    </header>

    <div class="container">
        <div class="grid">

            <!-- Card 3 -->
            <a href="../uploads/pdfs/Notice_Dhonnn_2025-06-04_3.pdf" target="_blank" class="card-link">
                <iframe class="card-embed" src="../uploads/pdfs/Notice_Dhonnn_2025-06-04_3.pdf"></iframe>
                <div class="card-footer">
                    <h2>Document 3</h2>
                </div>
            </a>

        </div>
    </div>

</body>

</html>