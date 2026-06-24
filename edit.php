<?php
require __DIR__ . '/koneksi/koneksi.php';
require __DIR__ . '/vendor/autoload.php';

$functions = new Functions($pdo);
$id = $_GET['id'] ?? null;
$note = $functions->getNoteById($id);

if (!$note) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mb_substr(trim($_POST['nama'] ?? ''), 0, 255);
    $newNote = $_POST['note'] ?? '';
    $tags = mb_substr(trim($_POST['tags'] ?? ''), 0, 500);
    $isPinned = $_POST['is_pinned'] ?? 0;
    $isFavorite = $_POST['is_favorite'] ?? 0;

    if (!empty($nama) && !empty($newNote)) {
        $functions->updateNote($id, $nama, $newNote, $tags, $isPinned, $isFavorite);
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Note - ReD</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="contents/vendor/code-mirror/codemirror.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="contents/style.css">
    <style>
        .CodeMirror-editor { height: 380px; }
        .editor-stats {
            display: flex;
            gap: 16px;
            padding: 10px 14px;
            background: var(--bg-secondary);
            border-top: 1px solid var(--border);
            border-radius: 0 0 12px 12px;
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        .editor-stats span {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .editor-stats b {
            color: var(--accent);
        }
    </style>
</head>
<body>

    <div class="container">
        <header class="header">
            <div class="header-left">
                <h1 class="page-title">Edit Note</h1>
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
            <form method="POST" id="noteForm">
                <div class="form-group-inline">
                    <div class="form-group">
                        <label for="nama">Note Name</label>
                        <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($note['nama']) ?>" placeholder="Note name..." required>
                    </div>
                    <div class="form-group">
                        <label for="tags">Tags</label>
                        <div class="tags-input" id="tagsContainer">
                            <input type="text" id="tagsInput" placeholder="Add tag...">
                        </div>
                        <input type="hidden" id="tags" name="tags" value="<?php echo htmlspecialchars($note['tags']); ?>">
                        <input type="hidden" name="is_pinned" value="<?php echo $note['is_pinned'] ?? 0; ?>">
                        <input type="hidden" name="is_favorite" value="<?php echo $note['is_favorite'] ?? 0; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Note Content</label>
                    
                    <div class="editor-toolbar">
                        <input type="text" id="searchInput" placeholder="Search text...">
                        <button type="button" id="prevBtn" class="btn-nav" title="Previous">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" id="nextBtn" class="btn-nav" title="Next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <span id="searchCount" class="search-count"></span>
                    </div>

                    <div class="editor-wrapper" style="border-radius: 0; margin-top: -1px;">
                        <textarea id="note" name="note" style="display:none;"><?= htmlspecialchars($note['note']) ?></textarea>
                        <div id="editor" class="CodeMirror-editor"></div>
                    </div>
                    <div class="editor-stats">
                        <span><i class="fas fa-font"></i> <b id="wordCount">0</b> words</span>
                        <span><i class="fas fa-text-width"></i> <b id="charCount">0</b> chars</span>
                        <span><i class="fas fa-align-left"></i> <b id="lineCount">0</b> lines</span>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-submit success">
                        <i class="fas fa-save"></i>
                        Save Changes
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
            lineWrapping: true,
            mode: 'text/plain',
            value: document.getElementById('note').value,
            gutters: ["CodeMirror-linenumbers", "duplicate-gutter"]
        });

        function updateStats() {
            const text = editor.getValue();
            const words = text.trim() ? text.trim().split(/\s+/).length : 0;
            const chars = text.length;
            const lines = text ? text.split('\n').length : 0;
            document.getElementById('wordCount').textContent = words;
            document.getElementById('charCount').textContent = chars;
            document.getElementById('lineCount').textContent = lines;
        }

        function checkDuplicates(cm) {
            const lines = cm.getValue().split('\n');
            const map = {};
            cm.getAllMarks().forEach(m => m.clear());
            cm.clearGutter('duplicate-gutter');
            lines.forEach((line, i) => {
                const key = line.trim();
                if (!key) return;
                if (!map[key]) map[key] = [];
                map[key].push(i);
            });
            Object.keys(map).forEach(text => {
                if (map[text].length > 1) {
                    const original = map[text][0];
                    markLine(cm, original, text, 'line-original', original);
                    map[text].slice(1).forEach(i => {
                        markLine(cm, i, text, 'line-duplicate', original);
                    });
                }
            });
        }

        function markLine(cm, line, text, className, targetLine) {
            cm.markText({ line, ch: 0 }, { line, ch: text.length }, { className: className });
            const marker = document.createElement('div');
            marker.innerHTML = '●';
            marker.style.cssText = 'color: ' + (className === 'line-original' ? '#3b82f6' : '#ef4444') + '; font-size: 10px; cursor: pointer;';
            marker.onclick = () => {
                cm.focus();
                cm.setCursor({ line: targetLine, ch: 0 });
                cm.scrollIntoView({ line: targetLine, ch: 0 }, 150);
            };
            cm.setGutterMarker(line, 'duplicate-gutter', marker);
        }

        let searchMarks = [], searchResults = [], searchIndex = -1;

        function clearSearch() {
            searchMarks.forEach(m => m.clear());
            searchMarks = [];
            searchResults = [];
            searchIndex = -1;
            searchCount.innerText = '';
        }

        function doSearch(keyword) {
            clearSearch();
            if (!keyword) return;
            editor.getValue().split('\n').forEach((line, i) => {
                let pos = line.indexOf(keyword);
                while (pos !== -1) {
                    searchResults.push({ line: i, ch: pos });
                    searchMarks.push(editor.markText({ line: i, ch: pos }, { line: i, ch: pos + keyword.length }, { className: 'search-highlight' }));
                    pos = line.indexOf(keyword, pos + keyword.length);
                }
            });
            searchCount.innerText = searchResults.length ? `${searchResults.length} results` : 'Not found';
        }

        function jumpTo(idx) {
            if (!searchResults.length) return;
            searchIndex = (idx + searchResults.length) % searchResults.length;
            const pos = searchResults[searchIndex];
            editor.focus();
            editor.setCursor(pos);
            editor.scrollIntoView(pos, 150);
        }

        document.getElementById('noteForm').addEventListener('submit', () => {
            const lines = editor.getValue().split('\n');
            const lastIndex = {};
            const cleanedLines = [];
            lines.forEach((line, i) => {
                const clean = line.replace(/\s+$/g, '');
                const key = clean.trim();
                if (key) lastIndex[key] = i;
                cleanedLines.push(clean);
            });
            const result = [];
            cleanedLines.forEach((line, i) => {
                const key = line.trim();
                if (key && lastIndex[key] === i) result.push(line);
            });
            document.getElementById('note').value = result.join('\n');
        });

        editor.on('change', () => {
            note.value = editor.getValue();
            checkDuplicates(editor);
            updateStats();
        });

        searchInput.addEventListener('input', e => doSearch(e.target.value));
        nextBtn.onclick = () => jumpTo(searchIndex + 1);
        prevBtn.onclick = () => jumpTo(searchIndex - 1);
        searchInput.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); jumpTo(searchIndex + 1); } });

        function trimTrailingSpaces(cm) {
            const lines = cm.getValue().split('\n');
            const cleaned = lines.map(line => line.replace(/\s+$/g, ''));
            cm.setValue(cleaned.join('\n'));
        }

        const tagInput = document.getElementById('tagsInput');
        const tagsContainer = document.getElementById('tagsContainer');
        const tagsHidden = document.getElementById('tags');
        let tags = tagsHidden.value ? tagsHidden.value.split(',').filter(t => t.trim()) : [];

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

        window.onload = () => {
            trimTrailingSpaces(editor);
            checkDuplicates(editor);
            updateStats();
            renderTags();
            updateEditorTheme(savedTheme);
            editor.focus();
            const lastLine = editor.lastLine();
            editor.setCursor({ line: lastLine, ch: editor.getLine(lastLine).length });
            editor.scrollIntoView({ line: lastLine, ch: editor.getLine(lastLine).length }, 150);
        };
    </script>
</body>
</html>