<?php
require __DIR__ . '/koneksi/koneksi.php';
require __DIR__ . '/functions/functions.php';

$functions = new Functions($pdo);
$notes = $functions->readNotes();

$colors = [
    '#FFCDD2',
    '#F8BBD0',
    '#E1BEE7',
    '#D1C4E9',
    '#C5CAE9',
    '#BBDEFB',
    '#B3E5FC',
    '#B2EBF2',
    '#B2DFDB',
    '#C8E6C9',
    '#DCEDC8',
    '#F0F4C3',
    '#FFF9C4',
    '#FFECB3',
    '#FFE0B2',
    '#FFCCBC',
    '#D7CCC8',
    '#CFD8DC',
    '#FFAB91',
    '#FFCC80',
    '#FFE082',
    '#FFF59D',
    '#E6EE9C',
    '#A5D6A7',
    '#80CBC4',
    '#4DB6AC',
    '#4FC3F7',
    '#81D4FA',
    '#90CAF9',
    '#64B5F6',
    '#9575CD',
    '#BA68C8',
    '#F06292',
    '#E57373',
    '#A1887F',
    '#F8E1F4',
    '#F3E5F5',
    '#F1F8E9',
    '#E0F7FA',
    '#FFECB3'
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReD | Note</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        .note-card {
            height: 250px;
            display: flex;
            flex-direction: column;
            padding: 15px;
            border-radius: 5px;
            color: #333;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
            word-wrap: break-word;
            box-sizing: border-box;
        }

        ::-webkit-scrollbar {
            width: 7.5px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
            border-radius: 10px;
        }

        .note-content {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 10px;
        }

        .note-content::-webkit-scrollbar {
            width: 6px;
        }

        .note-content::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 3px;
        }

        .note-actions {
            position: sticky;
            bottom: 0;
            background-color: inherit;
            padding: 5px 0;
            display: flex;
            /* Menggunakan Flexbox */
            align-items: center;
            /* Memastikan elemen sejajar vertikal */
            gap: 10px;
            /* Jarak antara elemen */
        }

        .note-actions .btn {
            border-radius: 8px;
            padding: 5px 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 30px;
            /* Tinggi tombol */
            width: 30px;
            /* Lebar tombol */
        }

        .slider-container {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 3px;
            margin-right: auto;
            /* Posisikan slider ke kiri */
        }

        .slider-container input[type="range"] {
            width: 100%;
            /* Lebar slider */
        }

        .slider-container span {
            font-size: 14px;
            min-width: 20px;
            /* Lebar minimum untuk nilai slider */
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">ReD | Note</h1>

        <div class="text-center mt-5 mb-3">
            <a href="add.php" class="btn btn-primary">Tambah Note</a>
        </div>

        <input type="text" class="form-control mb-4" id="search" placeholder="Cari note...">
    </div>

    <div class="container-fluid">
        <div class="row" id="noteContainer">
            <?php foreach ($notes as $index => $note): ?>
                <div class="col-md-3 mb-4 note-card-wrapper">
                    <div class="note-card shadow-sm" style="background-color: <?php echo $colors[$index % count($colors)]; ?>">
                        <h5><?php echo htmlspecialchars($note['nama']); ?></h5>
                        <div class="note-content">
                            <p><?php echo nl2br($functions->makeLinksClickable(htmlspecialchars($note['note']))); ?></p>
                        </div>
                        <div class="note-actions">
                            <!-- Slider untuk mengatur jumlah link yang dibuka -->
                            <div class="slider-container">
                                <input type="range" min="1" max="40" value="20" class="link-slider">
                                <span class="slider-value">20</span>
                            </div>
                            <!-- Tombol Buka Link -->
                            <button class="btn rounded-circle btn-success btn-sm" data-note="<?php echo htmlspecialchars($note['note']); ?>" onclick="openLinks(this)">
                                <i class="fa fa-external-link-alt"></i>
                            </button>
                            <a href="edit.php?id=<?php echo $note['id']; ?>" class="btn rounded-circle btn-info btn-sm">
                                <i class="text-white fa fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?php echo $note['id']; ?>" class="btn rounded-circle btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="text-white fa fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk mencari note
        document.getElementById('search').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const notes = document.querySelectorAll('.note-card-wrapper');

            notes.forEach(note => {
                const noteText = note.innerText.toLowerCase();
                note.style.display = noteText.includes(searchText) ? '' : 'none';
            });
        });

        // Fungsi untuk membuka link
        function openLinks(button) {
            // Ambil konten note dari atribut data-note
            const noteContent = button.getAttribute('data-note');

            // Ambil nilai dari slider yang sesuai
            const slider = button.parentElement.querySelector('.link-slider');
            const linkCount = parseInt(slider.value, 10);

            // Ambil semua link dari konten note
            const linkRegex = /https?:\/\/[^\s]+/g;
            const links = noteContent.match(linkRegex) || [];

            // Batasi jumlah link yang akan dibuka sesuai nilai slider
            const limitedLinks = links.slice(0, linkCount);

            // Buka setiap link di tab baru
            limitedLinks.forEach(link => {
                window.open(link.trim(), '_blank');
            });

            // Jika tidak ada link, beri pesan
            if (limitedLinks.length === 0) {
                alert('Tidak ada link yang ditemukan di note ini.');
            }
        }

        // Update nilai slider secara real-time
        document.querySelectorAll('.link-slider').forEach(slider => {
            const sliderValue = slider.parentElement.querySelector('.slider-value');
            slider.addEventListener('input', () => {
                sliderValue.textContent = slider.value;
            });
        });
    </script>
</body>

</html>