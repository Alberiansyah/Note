<?php
require __DIR__ . '/../koneksi/koneksi.php';

class Functions
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // CREATE: Tambah note baru dengan filter duplikat
    public function createNote($nama, $note)
    {
        // Pisahkan setiap baris untuk menghindari duplikat
        $newLines = array_filter(array_map('trim', explode("\n", $note)));

        // Hapus duplikat antar baris
        $uniqueLines = array_unique($newLines);

        // Gabungkan kembali ke dalam satu string
        $cleanNote = implode("\n", $uniqueLines);

        // Simpan note yang sudah dibersihkan ke database
        $stmt = $this->pdo->prepare("INSERT INTO tb_notes (nama, note) VALUES (:nama, :note)");
        $stmt->execute([':nama' => $nama, ':note' => $cleanNote]);
        return $this->pdo->lastInsertId();
    }

    // READ: Ambil semua note
    public function readNotes()
    {
        $stmt = $this->pdo->query("SELECT * FROM tb_notes ORDER BY nama ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE: Edit note dengan filter duplikat
    public function updateNote($id, $nama, $newNote)
    {
        // Pisahkan setiap baris untuk menghindari duplikat
        $newLines = array_filter(array_map('trim', explode("\n", $newNote)));

        // Hapus duplikat antar baris
        $uniqueLines = array_unique($newLines);

        // Gabungkan kembali ke dalam satu string
        $cleanNote = implode("\n", $uniqueLines);

        $stmt = $this->pdo->prepare("UPDATE tb_notes SET nama = :nama, note = :note WHERE id = :id");
        $stmt->execute([
            ':nama' => $nama,
            ':note' => $cleanNote,
            ':id' => $id
        ]);

        return $stmt->rowCount();
    }

    // DELETE: Hapus note
    public function deleteNote($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM tb_notes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    // Ambil note by ID
    public function getNoteById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tb_notes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function makeLinksClickable($text)
    {
        $pattern = '/(https?:\/\/[^\s]+)/';
        $replacement = '<a href="$1" target="_blank" style="text-decoration: none;">$1</a>';
        return preg_replace($pattern, $replacement, $text);
    }
}
