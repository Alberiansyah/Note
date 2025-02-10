<?php
require __DIR__ . '/koneksi/koneksi.php';
require __DIR__ . '/functions/functions.php';

$functions = new Functions($pdo);

$id = $_GET['id'] ?? null;
$note = $functions->getNoteById($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $newNote = $_POST['note'] ?? '';

    if (!empty($nama) && !empty($newNote)) {
        $functions->updateNote($id, $nama, $newNote);
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
    <title>Edit Note</title>
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
        <h1>Edit Note</h1>
        <form method="POST">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama:</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($note['nama']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="note" class="form-label">Note:</label>
                <textarea id="note" name="note" style="display:none;"><?php echo htmlspecialchars($note['note']); ?></textarea>
                <div id="editor"></div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script>
        const editor = CodeMirror(document.getElementById('editor'), {
            lineNumbers: true,
            mode: 'text/plain',
            value: document.getElementById('note').value,
            lineWrapping: true, // Aktifkan line wrapping
            extraKeys: {
                "Ctrl-Space": "autocomplete"
            },
            gutters: ["CodeMirror-linenumbers", "CodeMirror-lint-markers"]
        });

        editor.on('change', (cm) => {
            document.getElementById('note').value = cm.getValue();
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
            // Fokus ke editor CodeMirror
            editor.focus();

            // Set posisi kursor ke akhir teks
            const lastLine = editor.lastLine();
            const lastChar = editor.getLine(lastLine).length;
            editor.setCursor({
                line: lastLine,
                ch: lastChar
            });

            // Scroll ke posisi kursor
            editor.scrollIntoView({
                line: lastLine,
                ch: lastChar
            });

            // Periksa duplikat saat halaman dimuat
            checkDuplicates(editor);
        };
    </script>
</body>

</html>