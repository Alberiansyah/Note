<?php
require __DIR__ . '/koneksi/koneksi.php';
require __DIR__ . '/vendor/autoload.php';

$functions = new Functions($pdo);
$notes = $functions->readNotes();

$colors = [
    '#FFCDD2', '#F8BBD0', '#E1BEE7', '#D1C4E9', '#C5CAE9',
    '#BBDEFB', '#B3E5FC', '#B2EBF2', '#B2DFDB', '#C8E6C9',
    '#DCEDC8', '#F0F4C3', '#FFF9C4', '#FFECB3', '#FFE0B2',
    '#FFCCBC', '#D7CCC8', '#CFD8DC', '#FFAB91', '#FFCC80',
    '#FFE082', '#FFF59D', '#E6EE9C', '#A5D6A7', '#80CBC4',
    '#4DB6AC', '#4FC3F7', '#81D4FA', '#90CAF9', '#64B5F6',
    '#9575CD', '#BA68C8', '#F06292', '#E57373', '#A1887F',
    '#F8E1F4', '#F3E5F5', '#F1F8E9', '#E0F7FA', '#FFECB3'
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReD | Note</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="contents/style.css">
</head>

<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <h1 class="logo">ReD | Note</h1>
                <p class="subtitle">Write down whatever is on your mind</p>
            </div>
            <div style="display: flex; gap: 12px; align-items: center;">
                <div class="stats-summary" id="statsSummary">
                    <span><i class="fas fa-file-alt"></i> <b id="totalNotes"><?php echo count($notes); ?></b> notes</span>
                    <span><i class="fas fa-thumbtack"></i> <b id="totalPinned"><?php echo count(array_filter($notes, function($n) { return $n['is_pinned']; })); ?></b> pinned</span>
                    <span><i class="fas fa-star"></i> <b id="totalFav"><?php echo count(array_filter($notes, function($n) { return $n['is_favorite']; })); ?></b> fav</span>
                </div>
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="themeIcon"></i>
                    <span id="themeText">Dark</span>
                </button>
            </div>
        </header>

        <div class="controls">
            <div class="controls-left">
                <div class="search-wrapper">
                    <input type="text" id="search" placeholder="Search notes...">
                    <i class="fas fa-search"></i>
                </div>
                <div class="filter-bar">
                    <button class="filter-btn active" data-filter="all" onclick="setFilter('all')">
                        <i class="fas fa-layer-group"></i> All
                    </button>
                    <button class="filter-btn" data-filter="pinned" onclick="setFilter('pinned')">
                        <i class="fas fa-thumbtack"></i> Pinned
                    </button>
                    <button class="filter-btn" data-filter="fav" onclick="setFilter('fav')">
                        <i class="fas fa-star"></i> Favorites
                    </button>
                </div>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="add.php" class="btn-add">
                    <i class="fas fa-plus"></i>
                    Add Note
                </a>
            </div>
        </div>

        <?php if (empty($notes)): ?>
            <div class="empty-state">
                <i class="fas fa-sticky-note"></i>
                <h3>No notes yet</h3>
                <p>Create your first note</p>
            </div>
        <?php else: ?>
            <div class="notes-grid" id="noteContainer">
                <?php foreach ($notes as $index => $note): ?>
                    <?php
                    $cleanNote = rtrim($note['note']);
                    $lineCount = $cleanNote === '' ? 0 : substr_count($cleanNote, "\n") + 1;
                    $wordCount = $functions->countWords($cleanNote);
                    $charCount = $functions->countChars($cleanNote);
                    $tags = $note['tags'] ? explode(',', $note['tags']) : [];
                    $created = date('d M Y', strtotime($note['created_at']));
                    $updated = date('d M Y', strtotime($note['updated_at']));
                    ?>
                    <div class="note-card-wrapper <?php echo $note['is_pinned'] ? 'pinned' : ''; ?> <?php echo $note['is_favorite'] ? 'fav' : ''; ?>" 
                         style="animation-delay: <?= $index * 0.05 ?>s" 
                         data-tags="<?= htmlspecialchars($note['tags']) ?>"
                         data-name="<?= htmlspecialchars(strtolower($note['nama'])) ?>"
                         data-fav="<?= $note['is_favorite'] ?>">
                        <div class="note-card shadow-sm" style="background-color: <?php echo $colors[$index % count($colors)]; ?>">
                            <div class="note-header">
                                <div class="note-title-row">
                                    <?php if ($note['is_favorite']): ?>
                                        <i class="fas fa-star fav-icon"></i>
                                    <?php endif; ?>
                                    <h5 class="note-title"><?php echo htmlspecialchars($note['nama']); ?></h5>
                                </div>
                                <div class="note-header-right">
                                    <?php if ($note['is_pinned']): ?>
                                        <span class="pin-badge"><i class="fas fa-thumbtack"></i></span>
                                    <?php endif; ?>
                                    <span class="note-line-count"><?= $lineCount ?> line</span>
                                </div>
                            </div>
                            
                            <?php if (!empty($tags)): ?>
                                <div class="note-tags">
                                    <?php foreach ($tags as $tag): ?>
                                        <span class="tag"><?= htmlspecialchars(trim($tag), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="note-content">
                                <p><?php echo nl2br($functions->makeLinksClickable(htmlspecialchars($note['note']))); ?></p>
                            </div>
                            
                            <div class="note-footer">
                                <div class="note-stats">
                                    <span title="Words"><i class="fas fa-font"></i> <?= $wordCount ?></span>
                                    <span title="Characters"><i class="fas fa-text-width"></i> <?= $charCount ?></span>
                                </div>
                                <div class="note-date" title="Created">
                                    <i class="fas fa-calendar-alt"></i> <?= $created ?>
                                </div>
                            </div>
                            
                            <div class="note-actions">
                                <div class="slider-container">
                                    <input type="range" min="1" max="40" value="20" class="link-slider">
                                    <span class="slider-value">20</span>
                                </div>
                                <div class="action-buttons">
                                    <button class="action-btn btn-open" data-note="<?= htmlspecialchars($note['note'], ENT_QUOTES, 'UTF-8') ?>" onclick="openLinks(this)" title="Open Links">
                                        <i class="fas fa-external-link-alt"></i>
                                    </button>
                                    <button class="action-btn btn-pin" onclick="togglePin(<?= $note['id'] ?>)" title="<?= $note['is_pinned'] ? 'Unpin' : 'Pin' ?>">
                                        <i class="fas fa-thumbtack"></i>
                                    </button>
                                    <button class="action-btn btn-fav" onclick="toggleFav(<?= $note['id'] ?>)" title="<?= $note['is_favorite'] ? 'Unfavorite' : 'Favorite' ?>">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <button class="action-btn btn-copy" onclick="copyNote(<?= $note['id'] ?>)" title="Duplicate">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button class="action-btn btn-export" onclick="exportNote(<?= (int)$note['id'] ?>, <?= htmlspecialchars(json_encode($note['nama']), ENT_QUOTES, 'UTF-8') ?>)" title="Export">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <a href="edit.php?id=<?php echo $note['id']; ?>" class="action-btn btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $note['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to delete?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

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
        }

        function updateThemeUI(theme) {
            const icon = document.getElementById('themeIcon');
            const text = document.getElementById('themeText');
            if (theme === 'light') {
                icon.className = 'fas fa-sun';
                text.textContent = 'Light';
            } else {
                icon.className = 'fas fa-moon';
                text.textContent = 'Dark';
            }
        }

        document.getElementById('search').addEventListener('keyup', function() {
            applyFilters();
        });

        function openLinks(button) {
            const noteContent = button.getAttribute('data-note');
            const noteActions = button.closest('.note-actions');
            const slider = noteActions ? noteActions.querySelector('.link-slider') : null;
            
            if (!slider) {
                alert('Slider not found');
                return;
            }
            
            const linkCount = parseInt(slider.value, 10);
            const linkRegex = /https?:\/\/[^\s]+/g;
            const links = noteContent.match(linkRegex) || [];
            const limitedLinks = links.slice(0, linkCount);

            limitedLinks.forEach(link => {
                window.open(link.trim(), '_blank');
            });

            if (limitedLinks.length === 0) {
                alert('No links found in this note.');
            }
        }

        document.querySelectorAll('.link-slider').forEach(slider => {
            const sliderValue = slider.parentElement.querySelector('.slider-value');
            slider.addEventListener('input', () => {
                sliderValue.textContent = slider.value;
            });
        });

        let currentFilter = 'all';

        function setFilter(filter) {
            currentFilter = filter;
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.filter === filter);
            });
            applyFilters();
        }

        function applyFilters() {
            const searchText = document.getElementById('search').value.toLowerCase();
            const notes = document.querySelectorAll('.note-card-wrapper');

            notes.forEach(note => {
                let show = true;

                if (searchText) {
                    const noteText = note.innerText.toLowerCase();
                    if (!noteText.includes(searchText)) show = false;
                }

                if (show && currentFilter === 'pinned') {
                    if (!note.classList.contains('pinned')) show = false;
                }

                if (show && currentFilter === 'fav') {
                    if (note.dataset.fav !== '1') show = false;
                }

                note.style.display = show ? '' : 'none';
            });

            updateStats();
        }

        function togglePin(id) {
            fetch(`ajax.php?action=togglePin&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(() => alert('Failed to toggle pin'));
        }

        function toggleFav(id) {
            fetch(`ajax.php?action=toggleFav&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(() => alert('Failed to toggle favorite'));
        }

        function copyNote(id) {
            fetch(`ajax.php?action=copyNote&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Note duplicated successfully!');
                        location.reload();
                    }
                })
                .catch(() => alert('Failed to duplicate note'));
        }

        function exportNote(id, name) {
            fetch(`ajax.php?action=getNote&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.note) {
                        const blob = new Blob([data.note], { type: 'text/plain' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = name + '.txt';
                        a.click();
                        URL.revokeObjectURL(url);
                    } else {
                        alert('Gagal export: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(err => {
                    alert('Error: ' + err.message);
                });
        }

        function updateStats() {
            const visible = document.querySelectorAll('.note-card-wrapper:not([style*="display: none"])');
            let count = 0;
            let pinned = 0;
            let fav = 0;
            visible.forEach(n => {
                count++;
                if (n.classList.contains('pinned')) pinned++;
                if (n.dataset.fav === '1') fav++;
            });
            document.getElementById('totalNotes').textContent = count;
            document.getElementById('totalPinned').textContent = pinned;
            document.getElementById('totalFav').textContent = fav;
        }
    </script>
</body>

</html>