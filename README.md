# ReD | Note

A web-based note-taking app with automatic duplicate line detection, written in vanilla PHP.

## Features

### Notes
- Create notes (manual input or `.txt` file upload)
- Edit notes with CodeMirror editor (line numbers, line wrapping)
- Delete notes
- Duplicate notes
- Export notes as `.txt`

### Duplicate Detection
- Duplicate lines are automatically removed on save
- Visual marking in the editor: blue line (original), red line (duplicate)
- Clickable gutter markers to jump to the original line

### Organization
- **Pin** — pin important notes to the top
- **Favorite** — mark favorite notes with a star icon
- **Tags** — category labels with chip input (Enter/comma to add)
- **Filter** — show all / pinned / favorites
- **Search** — real-time text filtering

### UI
- Dark/Light theme (toggle + localStorage)
- 40 pastel colors for note cards
- Responsive grid (desktop to mobile)
- Smooth animations
- Custom favicon

### Misc
- Batch-open links from a note (slider 1-40)
- Auto-linked URLs
- Live stats: total notes, pinned, favorites (updates on filter)
- Word count, character count, line count per note

## Tech Stack

- **Backend:** PHP 8+ (PDO MySQL)
- **Database:** MySQL (`redNotes`)
- **Frontend:** Vanilla CSS, Font Awesome 6
- **Editor:** CodeMirror (minified, self-hosted)

## Database

Table `tb_notes`:
- `id` — INT AUTO_INCREMENT PRIMARY KEY
- `nama` — note title
- `note` — note body (TEXT)
- `is_pinned` — TINYINT(1) DEFAULT 0
- `is_favorite` — TINYINT(1) DEFAULT 0
- `tags` — VARCHAR(255), comma-separated
- `created_at` — DATETIME
- `updated_at` — DATETIME

## Installation

1. Clone into `C:\laragon\www\Note\` (or `htdocs`)
2. Create the MySQL database: `CREATE DATABASE redNotes;`
3. Create the table:
   ```sql
   CREATE TABLE tb_notes (
       id INT AUTO_INCREMENT PRIMARY KEY,
       nama VARCHAR(255) NOT NULL,
       note TEXT NOT NULL
   );
   ```
4. Copy `koneksi.sample.php` to `koneksi/koneksi.php` and adjust the credentials:
   ```php
   <?php
   $host = "localhost";
   $dbname = "redNotes";
   $user   = "root";
   $pass   = "";
   ```
5. Open `http://localhost/Note/` in your browser

## Security

- `koneksi/koneksi.php` (contains database credentials) is gitignored
- Use `koneksi.sample.php` as a template to create your own config
- Never commit database credentials to the repository
