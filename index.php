<?php
$dbFile = __DIR__ . '/bookmarks.sqlite';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec('CREATE TABLE IF NOT EXISTS bookmarks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    section TEXT NOT NULL,
    title TEXT NOT NULL,
    url TEXT NOT NULL,
    position INTEGER NOT NULL
)');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $section = trim($_POST['section']);
        $title = trim($_POST['title']);
        $url = trim($_POST['url']);
        if ($section && $title && $url) {
            $stmt = $pdo->prepare('SELECT COALESCE(MAX(position),0)+1 FROM bookmarks WHERE section = ?');
            $stmt->execute([$section]);
            $pos = $stmt->fetchColumn();
            $stmt = $pdo->prepare('INSERT INTO bookmarks (section, title, url, position) VALUES (?, ?, ?, ?)');
            $stmt->execute([$section, $title, $url, $pos]);
        }
    } elseif (isset($_POST['update'])) {
        $id = (int)$_POST['id'];
        $section = trim($_POST['section']);
        $title = trim($_POST['title']);
        $url = trim($_POST['url']);
        if ($id && $section && $title && $url) {
            $stmt = $pdo->prepare('UPDATE bookmarks SET section=?, title=?, url=? WHERE id=?');
            $stmt->execute([$section, $title, $url, $id]);
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare('DELETE FROM bookmarks WHERE id=?');
        $stmt->execute([$id]);
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$stmt = $pdo->query('SELECT * FROM bookmarks ORDER BY section, position');
$bookmarks = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $bookmarks[$row['section']][] = $row;
}

$editBookmark = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM bookmarks WHERE id = ?');
    $stmt->execute([$id]);
    $editBookmark = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bookmark Hub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Bookmarks</h1>
    <?php foreach ($bookmarks as $section => $items): ?>
        <h2><?= htmlspecialchars($section) ?></h2>
        <ol>
            <?php foreach ($items as $item): ?>
                <li>
                    <a href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['title']) ?></a>
                    <form method="post" class="inline">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <button type="submit" name="delete">Delete</button>
                    </form>
                    <a href="?edit=<?= $item['id'] ?>">Edit</a>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endforeach; ?>

    <?php if ($editBookmark): ?>
        <h2>Edit Bookmark</h2>
        <form method="post">
            <input type="hidden" name="id" value="<?= $editBookmark['id'] ?>">
            <input type="text" name="section" value="<?= htmlspecialchars($editBookmark['section']) ?>" placeholder="Section" required>
            <input type="text" name="title" value="<?= htmlspecialchars($editBookmark['title']) ?>" placeholder="Title" required>
            <input type="url" name="url" value="<?= htmlspecialchars($editBookmark['url']) ?>" placeholder="URL" required>
            <button type="submit" name="update">Update</button>
        </form>
    <?php endif; ?>

    <h2>Add Bookmark</h2>
    <form method="post">
        <input type="text" name="section" placeholder="Section" required>
        <input type="text" name="title" placeholder="Title" required>
        <input type="url" name="url" placeholder="URL" required>
        <button type="submit" name="add">Add</button>
    </form>
</body>
</html>
