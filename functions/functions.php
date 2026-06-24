<?php
class Functions
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function createNote($nama, $note, $tags = '')
    {
        $newLines = array_filter(array_map('trim', explode("\n", $note)));
        $uniqueLines = array_unique($newLines);
        $cleanNote = implode("\n", $uniqueLines);

        $stmt = $this->pdo->prepare("INSERT INTO tb_notes (nama, note, tags) VALUES (:nama, :note, :tags)");
        $stmt->execute([':nama' => $nama, ':note' => $cleanNote, ':tags' => $tags]);
        return $this->pdo->lastInsertId();
    }

    public function readNotes()
    {
        $stmt = $this->pdo->query("SELECT * FROM tb_notes ORDER BY is_pinned DESC, nama ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateNote($id, $nama, $newNote, $tags = '', $isPinned = 0, $isFavorite = 0)
    {
        $newLines = array_filter(array_map('trim', explode("\n", $newNote)));
        $uniqueLines = array_unique($newLines);
        $cleanNote = implode("\n", $uniqueLines);

        $stmt = $this->pdo->prepare("UPDATE tb_notes SET nama = :nama, note = :note, tags = :tags, is_pinned = :is_pinned, is_favorite = :is_favorite WHERE id = :id");
        $stmt->execute([
            ':nama' => $nama,
            ':note' => $cleanNote,
            ':tags' => $tags,
            ':is_pinned' => $isPinned,
            ':is_favorite' => $isFavorite,
            ':id' => $id
        ]);

        return $stmt->rowCount();
    }

    public function deleteNote($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM tb_notes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    public function getNoteById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tb_notes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function togglePin($id)
    {
        $note = $this->getNoteById($id);
        $newPin = $note['is_pinned'] ? 0 : 1;
        $stmt = $this->pdo->prepare("UPDATE tb_notes SET is_pinned = :is_pinned WHERE id = :id");
        $stmt->execute([':is_pinned' => $newPin, ':id' => $id]);
        return $newPin;
    }

    public function toggleFavorite($id)
    {
        $note = $this->getNoteById($id);
        $newFav = $note['is_favorite'] ? 0 : 1;
        $stmt = $this->pdo->prepare("UPDATE tb_notes SET is_favorite = :is_favorite WHERE id = :id");
        $stmt->execute([':is_favorite' => $newFav, ':id' => $id]);
        return $newFav;
    }

    public function duplicateNote($id)
    {
        $note = $this->getNoteById($id);
        if ($note) {
            $newName = $note['nama'] . ' (Copy)';
            $this->createNote($newName, $note['note'], $note['tags']);
            return true;
        }
        return false;
    }

    public function makeLinksClickable($text)
    {
        return preg_replace_callback(
            '/(https?:\/\/[^\s]+)/',
            function ($m) {
                $url = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
                return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" style="text-decoration: none;">' . $url . '</a>';
            },
            $text
        );
    }

    public function countWords($text)
    {
        $text = trim($text);
        if (empty($text)) return 0;
        return count(preg_split('/\s+/', $text));
    }

    public function countChars($text)
    {
        return mb_strlen($text, 'UTF-8');
    }
}