<?php
require __DIR__ . '/koneksi/koneksi.php';
require __DIR__ . '/functions/functions.php';

$functions = new Functions($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $note = '';

    if (isset($_POST['manual_note']) && !empty($_POST['manual_note'])) {
        $note = $_POST['manual_note'];
    } elseif (isset($_FILES['file_note']) && $_FILES['file_note']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file_note']['tmp_name'];

        // Baca file secara streaming untuk menghindari kehabisan memori
        $handle = fopen($file, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $note .= $line;
            }
            fclose($handle);
        } else {
            echo "<script>alert('Gagal membaca file!');</script>";
        }
    }

    if (!empty($nama) && !empty($note)) {
        $functions->createNote($nama, $note);
        header("Location: index.php");
        exit;
    } else {
        echo "<script>alert('Nama dan Note tidak boleh kosong!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Note</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <style>
        #editor {
            overflow: hidden;
            /* Nonaktifkan scrollbar dari elemen parent */
        }

        .CodeMirror {
            border: 1px solid #ddd;
            height: 300px;
            /* Tinggi tetap */
            resize: none;
            /* Nonaktifkan resize */
            overflow: hidden;
            /* Nonaktifkan scrollbar tambahan */
        }

        .CodeMirror-scroll {
            overflow: auto !important;
            /* Pastikan scrollbar CodeMirror muncul */
        }

        .CodeMirror-vscrollbar,
        .CodeMirror-hscrollbar {
            display: block !important;
            /* Pastikan scrollbar vertikal dan horizontal muncul */
        }

        .CodeMirror-linebackground-duplicate {
            background-color: #ffebee;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1>Tambah Note</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama:</label>
                <input type="text" class="form-control" id="nama" name="nama" required>
            </div>
            <div class="mb-3">
                <label for="manual_note" class="form-label">Note:</label>
                <textarea id="manual_note" name="manual_note" style="display:none;"></textarea>
                <div id="editor"></div>
            </div>
            <div class="mb-3">
                <label for="file_note" class="form-label">Atau Unggah File .txt:</label>
                <input type="file" class="form-control" id="file_note" name="file_note" accept=".txt">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script>
        const editor = CodeMirror(document.getElementById('editor'), {
            lineNumbers: true,
            mode: 'text/plain',
            lineWrapping: true,
            extraKeys: {
                "Ctrl-Space": "autocomplete"
            },
            gutters: ["CodeMirror-linenumbers", "CodeMirror-lint-markers"]
        });

        editor.on('change', (cm) => {
            document.getElementById('manual_note').value = cm.getValue();
            checkDuplicates(cm);
        });

        function checkDuplicates(cm) {
            const content = cm.getValue();
            const lines = content.split('\n');

            // Objek untuk melacak kemunculan setiap baris
            const lineOccurrences = {};

            // Hitung kemunculan setiap baris
            lines.forEach((line, index) => {
                if (!lineOccurrences[line]) {
                    lineOccurrences[line] = [];
                }
                lineOccurrences[line].push(index);
            });

            // Hapus semua tanda sebelumnya
            cm.getAllMarks().forEach(mark => mark.clear());

            // Tandai baris duplikat (kecuali kemunculan pertama)
            Object.keys(lineOccurrences).forEach(line => {
                if (lineOccurrences[line].length > 1) {
                    lineOccurrences[line].slice(1).forEach(lineIndex => {
                        cm.markText(
                            CodeMirror.Pos(lineIndex, 0),
                            CodeMirror.Pos(lineIndex, line.length), {
                                className: 'CodeMirror-linebackground-duplicate'
                            }
                        );
                    });
                }
            });
        }

        window.onload = function() {
            document.getElementById('nama').focus();
        };
    </script>
</body>

</html>