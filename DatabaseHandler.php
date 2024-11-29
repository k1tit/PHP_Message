<?php
class DatabaseHandler {
    private $mysqli;

    public function __construct($host, $user, $password, $database) {
        $this->mysqli = new mysqli($host, $user, $password, $database);
        if ($this->mysqli->connect_error) {
            die("Connection failed: " . $this->mysqli->connect_error);
        }
    }

    public function getMessageById($message_id) {
        $query = "SELECT * FROM messages WHERE id = ?";
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) {
            die("Ошибка подготовки запроса: " . $this->mysqli->error);
        }
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getAllMessagesWithNotes() {
        $query = "
            SELECT messages.title, 
                   messages.author, 
                   messages.short_content, 
                   messages.full_content, 
                   comments.note
            FROM messages
            LEFT JOIN comments ON messages.id = comments.message_id
        ";
        return $this->mysqli->query($query);
    }

    public function insertMessage($title, $author, $short_content, $full_content) {

        $query = "INSERT INTO messages (title, author, short_content, full_content) VALUES (?, ?, ?, ?)";
        $stmt = $this->mysqli->prepare($query);

        if (!$stmt) {
            die("Ошибка подготовки запроса: " . $this->mysqli->error);
        }
        
        $stmt->bind_param("ssss", $title, $author, $short_content, $full_content);
        
        if ($stmt->execute()) {
            return true;
        } else {
            die("Ошибка выполнения запроса: " . $stmt->error);
        }
    }
    
    public function handleEditMessage($postData) {
        if (!isset($postData['edit_message'])) {
            return "Запрос не содержит данных для редактирования.";
        }

        $message_id = (int)$postData['message_id'];
        $title = $postData['title'] ?? '';
        $author = $postData['author'] ?? '';
        $short_content = $postData['short_content'] ?? '';
        $full_content = $postData['full_content'] ?? '';

        if (empty($title) || empty($author) || empty($short_content) || empty($full_content)) {
            return "Все поля должны быть заполнены.";
        }

        if ($this->updateMessage($message_id, $title, $author, $short_content, $full_content)) {
            return "Сообщение успешно обновлено!";
        }
    
        return "Ошибка при обновлении сообщения.";
    }
      
    public function updateMessage($message_id, $title, $author, $short_content, $full_content) {
        $query = "UPDATE messages SET title = ?, author = ?, short_content = ?, full_content = ? WHERE id = ?";
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) {
            die("Ошибка подготовки запроса: " . $this->mysqli->error);
        }
        $stmt->bind_param("ssssi", $title, $author, $short_content, $full_content, $message_id);
        if (!$stmt->execute()) {
            die("Ошибка выполнения запроса: " . $stmt->error);
        }
        return true;
    }
    
    public function updateNoteByMessageId($message_id, $note) {
        $query = "UPDATE comments SET note = ? WHERE message_id = ?";
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) {
            die("Ошибка подготовки запроса: " . $this->mysqli->error);
        }

        $stmt->bind_param("si", $note, $message_id);
        if (!$stmt->execute()) {
            die("Ошибка выполнения запроса: " . $stmt->error);
        }
        
        return true;
    }

    public function getDistinctAuthors() {
        $query = "SELECT DISTINCT author FROM messages";
        $result = $this->mysqli->query($query);
        if (!$result) {
            die("Ошибка запроса: " . $this->mysqli->error);
        }
        return $result;
    }

    public function getAllTitles() {
        $query = "SELECT id, title FROM messages";
        $result = $this->mysqli->query($query);
        if (!$result) {
            die("Ошибка запроса: " . $this->mysqli->error);
        }
        return $result;
    }
}
?>
