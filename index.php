<?php
$host = 'localhost';
$user = 'root';
$password = '12345678';
$database = 'messages_app'; 

require_once __DIR__ . '/DatabaseHandler.php';
$db = new DatabaseHandler($host, $user, $password, $database); 

$updateResult = "";
$message = null;

if (isset($_POST['edit_note'])) {
    $message_id = $_POST['message_id'];
    $note = $_POST['note'];
    if (!$db ->updateNoteByMessageId($message_id, $note)) {
        echo "Error updating note!";
    }
}

$message_id = $_GET['id'] ?? 1; 
$message = $db ->getMessageById($message_id);

if (!$message) {
    die("Сообщение с ID $message_id не найдено.");
}

$message_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = null;

if ($message_id > 0) {
    $message = $db ->getMessageById($message_id);
    if (!$message) {
        echo "Message not found!";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $db ->handleEditMessage($_POST);
    echo $result; 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_new_message'])) {
        $title = $_POST['title'] ?? '';
        $author = $_POST['author'] ?? '';
        $short_content = $_POST['short_content'] ?? '';
        $full_content = $_POST['full_content'] ?? '';
        
        if (!$db ->insertMessage($title, $author, $short_content, $full_content)) {
            die("Failed to insert message");
        }
    }

    if (isset($_POST['edit_note'])) {
        $message_id = $_POST['message_id'];
        $note = $_POST['note'];
        if (!$db ->updateNoteByMessageId($message_id, $note)) {
            echo "Error updating note!";
        }
    }
}

$messages = $db ->getAllMessagesWithNotes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            margin: 0;
        }
        .container {
            width: 50%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        input, textarea, select, table {
            width: 100%;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>List messages</h1>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Short content</th>
                <th>Full content</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $messages = $db ->getAllMessagesWithNotes();

            if ($messages && $messages->num_rows > 0) {
                while ($row = $messages->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['author']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['short_content']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['full_content']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['note'] ?? 'Нет заметок') . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>Сообщения не найдены</td></tr>"; // Корректируем colspan под 5 колонок
            }
            ?>
        </tbody>
    </table>
</div>

<div class="container">
    <h1>Add New Message</h1>
    <form method="POST">
        <label for="title">Title</label>
        <input id="title" name="title" type="text" required>

        <label for="author">Author</label>
        <input id="author" name="author" type="text" required>

        <label for="short_content">Short Content</label>
        <textarea id="short_content" name="short_content" rows="4" required></textarea>

        <label for="full_content">Full Content</label>
        <textarea id="full_content" name="full_content" rows="6" required></textarea>

        <button type="submit" name="add_new_message">Add Message</button>
    </form>
</div>

<div class="container">
    <h1>Edit Message</h1>
    <form method="POST">
        <input type="hidden" name="message_id" value="<?= htmlspecialchars($message['id'] ?? '') ?>">

        <label for="title">Title</label>
        <input id="title" name="title" type="text" value="<?= htmlspecialchars($message['title'] ?? '') ?>" required>

        <label for="author">Author</label>
        <input id="author" name="author" type="text" value="<?= htmlspecialchars($message['author'] ?? '') ?>" required>

        <label for="short_content">Short Content</label>
        <textarea id="short_content" name="short_content" rows="4" required><?= htmlspecialchars($message['short_content'] ?? '') ?></textarea>

        <label for="full_content">Full Content</label>
        <textarea id="full_content" name="full_content" rows="6" required><?= htmlspecialchars($message['full_content'] ?? '') ?></textarea>

        <button type="submit" name="edit_message">Save Changes</button>
    </form>
</div>


<div class="container">
    <h1>Edit Note</h1>
    <form method="POST">
        <label for="message_id">Message ID</label>
        <select id="message_id" name="message_id" required>
            <?php
            $titles = $db ->getAllTitles();
            while ($row = $titles->fetch_assoc()) {
                $selected = ($row['id'] == $message_id) ? 'selected' : '';
                echo "<option value=\"{$row['id']}\" $selected>{$row['id']} - {$row['title']}</option>";
            }
            ?>
        </select>

        <label for="note">Note</label>
        <textarea id="note" name="note" rows="4" required></textarea>

        <button type="submit" name="edit_note">Save Changes</button>
    </form>
</div>

</body>
</html>
