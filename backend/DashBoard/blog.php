<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login/loging.php");
    exit();
}

// Redirect to enhanced blog editor
header("Location: blog_enhanced.php");
exit();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ATMABISWAS Press Editor</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="../images/logo/logo.png">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #2ecc71;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 20px;
            color: var(--dark);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            text-align: center;
            margin-bottom: 25px;
            color: var(--primary);
            font-size: 2.5rem;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
            border-bottom: 3px solid var(--secondary);
            padding-bottom: 15px;
        }

        input[type="text"] {
            width: 100%;
            padding: 15px;
            font-size: 1.1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 25px;
            transition: all 0.3s;
            font-family: 'Montserrat', sans-serif;
        }

        input[type="text"]:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
            padding: 15px;
            background: var(--light);
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .toolbar select,
        .toolbar button,
        .toolbar input {
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px 12px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.2s;
        }

        .toolbar button {
            min-width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toolbar select:hover,
        .toolbar button:hover,
        .toolbar input:hover {
            background: var(--secondary);
            color: white;
            border-color: var(--secondary);
        }

        .toolbar input[type="color"] {
            height: 40px;
            padding: 2px;
        }

        .editor-content {
            min-height: 300px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            font-size: 16px;
            line-height: 1.6;
            background: white;
            margin-bottom: 30px;
            transition: all 0.3s;
        }

        .editor-content:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .editor-content[contenteditable="true"]:empty:before {
            content: attr(placeholder);
            color: #aaa;
            display: block;
        }

        .publish-btn {
            background: var(--success);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            justify-content: center;
            font-family: 'Montserrat', sans-serif;
        }

        .publish-btn:hover {
            background: #27ae60;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }

        .publish-btn i {
            font-size: 1.2rem;
        }

        h1[style*="font-size: 1.2rem"] {
            font-family: 'Montserrat', sans-serif;
            margin-bottom: 10px;
            color: var(--primary);
            font-weight: 600;
        }

        .drag-over {
            background-color: rgba(52, 152, 219, 0.1) !important;
            border: 2px dashed var(--secondary) !important;
        }

        .editor-content img {
            max-width: 100%;
            border-radius: 8px;
            margin: 15px 0;
        }

        .toolbar-divider {
            width: 1px;
            background: #ddd;
            margin: 0 5px;
        }

        .hidden {
            display: none;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            background: var(--success);
            color: white;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateX(200%);
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.error {
            background: var(--accent);
        }

        .word-count {
            font-size: 0.9rem;
            color: #777;
            text-align: right;
            margin-top: -20px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card editor-section">
            <h1>ATMABISWAS Press Editor</h1>
            <form action="../blogUpload_process.php" method="POST" onsubmit="return handleFormSubmit(event)">
                <!-- Blog Metadata -->
                <input name="blog_title" type="text" id="blogTitle" placeholder="Blog Title" required>

                <!-- Rich Text Toolbar -->
                <div class="toolbar">
                    <select onchange="changeFont(this.value)">
                        <option value="Arial">Arial</option>
                        <option value="Times New Roman">Times New Roman</option>
                        <option value="Georgia">Georgia</option>
                        <option value="Courier New">Courier New</option>
                        <option value="Verdana">Verdana</option>
                    </select>

                    <select onchange="changeFontSize(this.value)">
                        <option value="1">Small</option>
                        <option value="2">Medium</option>
                        <option value="3" selected>Large</option>
                        <option value="4">Extra Large</option>
                        <option value="5">XXL</option>
                    </select>

                    <div class="toolbar-divider"></div>

                    <!-- Formatting buttons -->
                    <button type="button" onclick="formatText('bold')" title="Bold">
                        <i class="fas fa-bold"></i>
                    </button>
                    <button type="button" onclick="formatText('italic')" title="Italic">
                        <i class="fas fa-italic"></i>
                    </button>
                    <button type="button" onclick="formatText('underline')" title="Underline">
                        <i class="fas fa-underline"></i>
                    </button>

                    <div class="toolbar-divider"></div>

                    <input type="color" onchange="changeColor(this.value)" title="Text Color">

                    <div class="toolbar-divider"></div>

                    <!-- Alignment -->
                    <button type="button" onclick="alignText('left')" title="Align Left">
                        <i class="fas fa-align-left"></i>
                    </button>
                    <button type="button" onclick="alignText('center')" title="Align Center">
                        <i class="fas fa-align-center"></i>
                    </button>
                    <button type="button" onclick="alignText('right')" title="Align Right">
                        <i class="fas fa-align-right"></i>
                    </button>
                    <button type="button" onclick="alignText('justify')" title="Justify">
                        <i class="fas fa-align-justify"></i>
                    </button>

                    <div class="toolbar-divider"></div>

                    <!-- Lists -->
                    <button type="button" onclick="formatText('insertUnorderedList')" title="Bullet List">
                        <i class="fas fa-list-ul"></i>
                    </button>
                    <button type="button" onclick="formatText('insertOrderedList')" title="Numbered List">
                        <i class="fas fa-list-ol"></i>
                    </button>

                    <div class="toolbar-divider"></div>

                    <!-- Indentation -->
                    <button type="button" onclick="formatText('indent')" title="Indent">
                        <i class="fas fa-indent"></i>
                    </button>
                    <button type="button" onclick="formatText('outdent')" title="Outdent">
                        <i class="fas fa-outdent"></i>
                    </button>

                    <div class="toolbar-divider"></div>

                    <!-- Link -->
                    <button type="button" onclick="createLink()" title="Insert Link">
                        <i class="fas fa-link"></i>
                    </button>

                    <!-- Image Upload -->
                    <button type="button" onclick="triggerUpload()" title="Insert Image">
                        <i class="fas fa-image"></i>
                    </button>
                    <input type="file" id="imageUpload" class="hidden" accept="image/*">
                </div>

                <!-- Summary of Blog -->
                <h1 style="font-size: 1.2rem;">Write a<strong> Summary for this News</strong> </h1>
                <div id="summary_editor" contenteditable="true" placeholder="Start writing your summary here..."
                    class="editor-content" ondrop="handleDrop(event)" ondragover="handleDragOver(event)"
                    ondragleave="handleDragLeave(event)" oninput="updateWordCount('summary')">
                </div>
                <div id="summary_word_count" class="word-count">Words: 0</div>

                <!-- Main Blog Content -->
                <h1 style="font-size: 1.2rem;">Start writing News from here</h1>
                <div id="editor" contenteditable="true" placeholder="Start writing your post content here..."
                    class="editor-content" ondrop="handleDrop(event)" ondragover="handleDragOver(event)"
                    ondragleave="handleDragLeave(event)" oninput="updateWordCount('main')">
                </div>
                <div id="main_word_count" class="word-count">Words: 0</div>

                <!-- Hidden inputs for sanitized content -->
                <input type="hidden" id="sanitizedContent" name="blog_content">

                <input type="hidden" id="summary_sanitizedContent" name="summary_content">

                <!-- Publish Button -->
                <button type="submit" class="publish-btn">
                    <i class="fas fa-paper-plane"></i> Publish Post
                </button>
            </form>
        </div>
    </div>

    <div class="notification" id="notification"></div>

    <script>
        // Get the active editor (either summary or main content)
        function getActiveEditor() {
            const summaryEditor = document.getElementById('summary_editor');
            const mainEditor = document.getElementById('editor');

            if (document.activeElement === summaryEditor) return summaryEditor;
            if (document.activeElement === mainEditor) return mainEditor;

            // Default to main editor if none is focused
            return mainEditor;
        }

        // Format text using execCommand
        function formatText(command) {
            const editor = getActiveEditor();
            editor.focus();

            try {
                document.execCommand(command, false, null);
            } catch (e) {
                console.error("Formatting failed:", e);
                showNotification("Formatting failed. Please try again.", true);
            }
        }

        // Change text color
        function changeColor(color) {
            const editor = getActiveEditor();
            editor.focus();

            try {
                document.execCommand("styleWithCSS", false, true);
                document.execCommand("foreColor", false, color);
            } catch (e) {
                console.error("Color change failed:", e);
                showNotification("Color change failed. Please try again.", true);
            }
        }

        // Align text
        function alignText(alignType) {
            const editor = getActiveEditor();
            editor.focus();

            try {
                document.execCommand("styleWithCSS", false, true);
                document.execCommand("justify" + alignType.charAt(0).toUpperCase() + alignType.slice(1), false, null);
            } catch (e) {
                console.error("Alignment failed:", e);
                showNotification("Alignment failed. Please try again.", true);
            }
        }

        // Change font
        function changeFont(font) {
            const editor = getActiveEditor();
            editor.focus();

            try {
                document.execCommand("fontName", false, font);
            } catch (e) {
                console.error("Font change failed:", e);
                showNotification("Font change failed. Please try again.", true);
            }
        }

        // Change font size
        function changeFontSize(size) {
            const editor = getActiveEditor();
            editor.focus();

            try {
                document.execCommand("fontSize", false, size);
            } catch (e) {
                console.error("Font size change failed:", e);
                showNotification("Font size change failed. Please try again.", true);
            }
        }

        // Create link
        function createLink() {
            const editor = getActiveEditor();
            editor.focus();

            const url = prompt("Enter the URL:");
            if (url) {
                try {
                    document.execCommand("createLink", false, url);
                } catch (e) {
                    console.error("Link creation failed:", e);
                    showNotification("Link creation failed. Please try again.", true);
                }
            }
        }

        // Trigger image upload
        function triggerUpload() {
            document.getElementById("imageUpload").click();
        }

        // Handle image upload from file input
        document.getElementById("imageUpload").addEventListener("change", function(e) {
            handleImage(e.target.files[0]);
        });

        // Drag and drop handlers
        function handleDragOver(e) {
            e.preventDefault();
            e.stopPropagation();
            e.target.classList.add("drag-over");
        }

        function handleDragLeave(e) {
            e.preventDefault();
            e.stopPropagation();
            e.target.classList.remove("drag-over");
        }

        function handleDrop(e) {
            e.preventDefault();
            e.stopPropagation();
            e.target.classList.remove("drag-over");

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                if (file && file.type.startsWith("image/")) {
                    handleImage(file);
                }
            }
        }

        // Handle image insertion
        function handleImage(file) {
            const editor = getActiveEditor();
            editor.focus();

            const reader = new FileReader();
            reader.onload = (e) => {
                const img = document.createElement("img");
                img.src = e.target.result;
                img.style.maxWidth = "100%";
                img.style.borderRadius = "8px";
                img.style.margin = "15px 0";

                // Insert at cursor position
                const selection = window.getSelection();
                if (selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);
                    range.insertNode(img);

                    // Add space after image
                    const br = document.createElement("br");
                    range.insertNode(br);

                    // Move cursor after the image
                    const newRange = document.createRange();
                    newRange.setStartAfter(img);
                    newRange.collapse(true);
                    selection.removeAllRanges();
                    selection.addRange(newRange);
                } else {
                    // Fallback if no selection
                    editor.appendChild(img);
                    editor.appendChild(document.createElement("br"));
                }
            };
            reader.readAsDataURL(file);
        }

        // Count words in text
        function countWords(text) {
            // Remove HTML tags and count words
            const cleanText = text.replace(/<[^>]*>/g, ' ').trim();
            return cleanText ? cleanText.split(/\s+/).length : 0;
        }

        // Update word count display
        function updateWordCount(editorType) {
            const editor = editorType === 'summary' ?
                document.getElementById('summary_editor') :
                document.getElementById('editor');

            const wordCountElement = editorType === 'summary' ?
                document.getElementById('summary_word_count') :
                document.getElementById('main_word_count');

            const wordCount = countWords(editor.innerHTML);
            wordCountElement.textContent = `Words: ${wordCount}`;

            if (editorType === 'summary' && wordCount < 100) {
                wordCountElement.classList.add('low');
            } else {
                wordCountElement.classList.remove('low');
            }
        }

        // Sanitize HTML content - preserve allowed tags
        function sanitizeHTML(html) {
            // Create a temporary element to parse HTML
            const temp = document.createElement('div');
            temp.innerHTML = html;

            // Remove disallowed tags but keep their content
            const disallowedTags = ['script', 'style', 'iframe', 'object', 'embed'];
            disallowedTags.forEach(tag => {
                const elements = temp.getElementsByTagName(tag);
                while (elements[0]) {
                    const parent = elements[0].parentNode;
                    while (elements[0].firstChild) {
                        parent.insertBefore(elements[0].firstChild, elements[0]);
                    }
                    parent.removeChild(elements[0]);
                }
            });

            // Return sanitized HTML
            return temp.innerHTML;
        }

        async function handleFormSubmit(e) {
            e.preventDefault();

            // Get and sanitize blog content
            const blogEditor = document.getElementById('editor');
            const blogContent = blogEditor ? blogEditor.innerHTML : 'No Content was added';
            const sanitizedBlogContent = sanitizeHTML(blogContent);
            document.getElementById('sanitizedContent').value = sanitizedBlogContent;

            // Get and sanitize summary content
            const summaryEditor = document.getElementById('summary_editor');
            const summaryContent = summaryEditor ? summaryEditor.innerHTML : 'No Summary was added';
            const sanitizedSummaryContent = sanitizeHTML(summaryContent);
            document.getElementById('summary_sanitizedContent').value = sanitizedSummaryContent;

            // ✅ Now that hidden inputs are updated, collect form data
            const formData = new FormData(e.target);

            try {
                const response = await fetch(e.target.action, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    showNotification('Blog published successfully! Redirecting...');
                    setTimeout(() => {
                        window.location.href = '../DashBoard/blog_image.php?id=' + result.post_id;
                    }, 2000);
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error publishing post: ' + error.message, true);
            }
        }


        // Show notification
        function showNotification(message, isError = false) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = 'notification' + (isError ? ' error' : '') + ' show';

            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Initialize the editor
        window.onload = function() {
            document.getElementById('editor').focus();
            updateWordCount('summary');
            updateWordCount('main');
        };
    </script>
</body>

</html>