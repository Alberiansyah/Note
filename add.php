<?php
require __DIR__ . '/koneksi/koneksi.php';
require __DIR__ . '/vendor/autoload.php';

$functions = new Functions($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mb_substr(trim($_POST['nama'] ?? ''), 0, 255);
    $tags = mb_substr(trim($_POST['tags'] ?? ''), 0, 500);
    $note = '';

    if (isset($_POST['manual_note']) && !empty($_POST['manual_note'])) {
        $note = $_POST['manual_note'];
    } elseif (isset($_FILES['file_note']) && $_FILES['file_note']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['file_note']['size'] > 5 * 1024 * 1024) {
            $error = 'File too large. Max 5MB.';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['file_note']['tmp_name']);
            finfo_close($finfo);
            $allowed = ['text/plain', 'text/markdown', 'application/octet-stream', 'inode/x-empty'];
            if (!in_array($mime, $allowed)) {
                $error = 'Only .txt files are allowed.';
            } else {
                $file = $_FILES['file_note']['tmp_name'];
                $handle = fopen($file, 'r');
                if ($handle) {
                    while (($line = fgets($handle)) !== false) {
                        $note .= $line;
                    }
                    fclose($handle);
                }
            }
        }
    }

    if (!empty($nama) && !empty($note)) {
        $functions->createNote($nama, $note, $tags);
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Note - ReD</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="contents/vendor/code-mirror/codemirror.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="contents/style.css">
</head>

<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <h1 class="page-title">Add Note</h1>
                <nav class="breadcrumb">
                    <a href="index.php">Home</a>
                    <span>/</span>
                    <span class="current">Add Note</span>
                </nav>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="index.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </a>
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="themeIcon"></i>
                    <span id="themeText">Dark</span>
                </button>
            </div>
        </header>

        <div class="card">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group-inline">
                    <div class="form-group">
                        <label for="nama">Note Name</label>
                        <input type="text" id="nama" name="nama" placeholder="e.g. Coding tutorial..." maxlength="255" required>
                    </div>
                    <div class="form-group">
                        <label for="tags">Tags (separate with commas)</label>
                        <div class="tags-input" id="tagsContainer">
                            <input type="text" id="tagsInput" placeholder="coding, tutorial, js...">
                        </div>
                        <input type="hidden" id="tags" name="tags">
                    </div>
                </div>

                <div class="form-group">
                    <label for="manual_note">Note Content</label>
                    <textarea id="manual_note" name="manual_note" style="display:none;"></textarea>
                    <div class="editor-wrapper" id="editorContainer">
                        <div id="editor"></div>
                    </div>
                    <p class="duplicate-hint">
                        <i class="fas fa-info-circle"></i>
                        Duplicate lines will be removed automatically on save
                    </p>
                </div>

                <div class="form-group">
                    <label>Or Upload .txt File</label>
                    <div class="file-upload">
                        <label for="file_note" class="file-upload-label">
                            <i class="fas fa-file-upload"></i>
                            Choose File
                            <span>(Optional)</span>
                        </label>
                        <input type="file" id="file_note" name="file_note" accept=".txt">
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-save"></i>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="contents/vendor/code-mirror/codemirror.min.js"></script>
    <script>
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeUI(savedTheme);

        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme');
            const newTheme = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeUI(newTheme);
            updateEditorTheme(newTheme);
        }

        function updateThemeUI(theme) {
            document.getElementById('themeIcon').className = theme === 'light' ? 'fas fa-sun' : 'fas fa-moon';
            document.getElementById('themeText').textContent = theme === 'light' ? 'Light' : 'Dark';
        }

        function updateEditorTheme(theme) {
            const cm = document.querySelector('.CodeMirror');
            if (cm) {
                cm.style.background = theme === 'light' ? '#ffffff' : '#0f0f1a';
                cm.style.color = theme === 'light' ? '#1e293b' : '#e2e8f0';
                const gutters = document.querySelector('.CodeMirror-gutters');
                if (gutters) gutters.style.background = theme === 'light' ? '#f1f5f9' : '#1a1a2e';
            }
        }

        const editor = CodeMirror(document.getElementById('editor'), {
            lineNumbers: true,
            mode: 'text/plain',
            lineWrapping: true,
            extraKeys: { "Ctrl-Space": "autocomplete" },
            gutters: ["CodeMirror-linenumbers", "CodeMirror-lint-markers"]
        });

        editor.on('change', (cm) => {
            document.getElementById('manual_note').value = cm.getValue();
            checkDuplicates(cm);
        });

        function checkDuplicates(cm) {
            const content = cm.getValue();
            const lines = content.split('\n');
            const lineOccurrences = {};
            lines.forEach((line, index) => {
                if (!lineOccurrences[line]) lineOccurrences[line] = [];
                lineOccurrences[line].push(index);
            });
            cm.getAllMarks().forEach(mark => mark.clear());
            Object.keys(lineOccurrences).forEach(line => {
                if (lineOccurrences[line].length > 1) {
                    lineOccurrences[line].slice(1).forEach(lineIndex => {
                        cm.markText(CodeMirror.Pos(lineIndex, 0), CodeMirror.Pos(lineIndex, line.length), {
                            className: 'CodeMirror-linebackground-duplicate'
                        });
                    });
                }
            });
        }

        const tagInput = document.getElementById('tagsInput');
        const tagsContainer = document.getElementById('tagsContainer');
        const tagsHidden = document.getElementById('tags');
        let tags = [];

        function renderTags() {
            tagsContainer.querySelectorAll('.tag').forEach(t => t.remove());
            tags.forEach((tag, i) => {
                const tagEl = document.createElement('span');
                tagEl.className = 'tag';
                tagEl.innerHTML = `${tag.trim()} <i class="fas fa-times" onclick="removeTag(${i})"></i>`;
                tagsContainer.insertBefore(tagEl, tagInput);
            });
            tagsHidden.value = tags.join(',');
        }

        tagInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const val = tagInput.value.replace(/,/g, '').trim();
                if (val && !tags.includes(val)) {
                    tags.push(val);
                    tagInput.value = '';
                    renderTags();
                }
            } else if (e.key === 'Backspace' && tagInput.value === '' && tags.length > 0) {
                tags.pop();
                renderTags();
            }
        });

        window.removeTag = (i) => {
            tags.splice(i, 1);
            renderTags();
        };

        updateEditorTheme(savedTheme);

        window.onload = () => {
            document.getElementById('nama').focus();
        };
    </script>
</body>

</html>