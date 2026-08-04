function formatText(tag) {
  const editor = document.getElementById("editor");
  const selection = window.getSelection();

  if (selection.rangeCount > 0) {
    const range = selection.getRangeAt(0);

    if (editor.contains(range.commonAncestorContainer)) {
      const selectedText = range.extractContents();
      const wrapper = document.createElement(tag);
      wrapper.appendChild(selectedText);
      range.insertNode(wrapper);

      range.setStartAfter(wrapper);
      range.setEndAfter(wrapper);
      selection.removeAllRanges();
      selection.addRange(range);
    }
  }

  editor.focus();
}

function changeColor(color) {
  document.execCommand("foreColor", false, color);
}

function alignText(alignType) {
  const editor = document.getElementById("editor");
  const selection = window.getSelection();

  if (selection.rangeCount > 0 && editor.contains(selection.anchorNode)) {
    const range = selection.getRangeAt(0);
    let container = range.startContainer;

    // Go up to the nearest block element
    while (container && container !== editor && container.nodeType === 3) {
      container = container.parentNode;
    }

    if (container && container !== editor) {
      container.style.textAlign = alignType;
    }
  }
  editor.focus();
}

function changeFont(font) {
  document.execCommand("fontName", false, font);
}

function changeFontSize(size) {
  document.execCommand("fontSize", false, size);
}

function createLink() {
  const url = prompt("Enter the URL:");
  if (url) document.execCommand("createLink", false, url);
}

function triggerUpload() {
  document.getElementById("imageUpload").click();
}

document.getElementById("imageUpload").addEventListener("change", function (e) {
  handleImage(e.target.files[0]);
});

function handleDragOver(e) {
  e.preventDefault();
  e.target.classList.add("drag-over");
}

function handleDragLeave(e) {
  e.target.classList.remove("drag-over");
}

function handleDrop(e) {
  e.preventDefault();
  e.target.classList.remove("drag-over");
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith("image/")) handleImage(file);
}

function handleImage(file) {
  const reader = new FileReader();
  reader.onload = (e) => {
    const img = document.createElement("img");
    img.src = e.target.result;
    img.style.maxWidth = "100%";
    document.getElementById("editor").appendChild(img);
  };
  reader.readAsDataURL(file);
}

function savePost() {
  const title = document.getElementById("blogTitle").value;
  const category = document.getElementById("blogCategory").value;
  const content = document.getElementById("editor").innerHTML;
  if (!title || !category || !content) {
    alert("Please fill all fields!");
    return;
  }
  const post = {
    id: Date.now(),
    title,
    category,
    content,
    date: new Date().toLocaleString(),
  };
  let posts = JSON.parse(localStorage.getItem("posts") || "[]");
  posts.unshift(post);
  localStorage.setItem("posts", JSON.stringify(posts));
  displayPosts();
  clearEditor();
}

function displayPosts() {
  const posts = JSON.parse(localStorage.getItem("posts") || "[]");
  const preview = document.getElementById("postPreview");
  preview.innerHTML = "";
  posts.forEach((post) => {
    const card = document.createElement("div");
    card.className = "post-card";
    card.innerHTML = `
      <h3>${post.title}</h3>
      <div class="meta">
        <span style="background: #f0f0f0; padding: 0.3rem 0.6rem; border-radius: 5px;">
          ${post.category}
        </span>
        <span>${post.date}</span>
      </div>
      <div>${post.content}</div>
      <button onclick="deletePost(${post.id})">
        <i class="fas fa-trash"></i> Delete
      </button>
    `;
    preview.appendChild(card);
  });
}

function deletePost(id) {
  let posts = JSON.parse(localStorage.getItem("posts") || "[]");
  posts = posts.filter((post) => post.id !== id);
  localStorage.setItem("posts", JSON.stringify(posts));
  displayPosts();
}

function clearEditor() {
  document.getElementById("blogTitle").value = "";
  document.getElementById("blogCategory").value = "";
  document.getElementById("editor").innerHTML = "";
}

displayPosts();
