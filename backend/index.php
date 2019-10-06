<?php

$db = parse_url(getenv("DATABASE_URL"));

$pdo = new PDO("pgsql:" . sprintf(
    "host=%s;port=%s;user=%s;password=%s;dbname=%s",
    $db["host"],
    $db["port"],
    $db["user"],
    $db["pass"],
    ltrim($db["path"], "/")
));

$task_id = null;

switch ($_SERVER['REQUEST_URI']) {
    case "/task/1":
        $task_id = 1;

    case "/task/2":
        $task_id = 2;
        break;
    default:
        header('HTTP/1.0 404 Not Found');
        break;
}

$stmt = $pdo->query("SELECT * FROM tasks WHERE id=?");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

// echo var_dump($task);
header('Content-type: application/json');
echo json_encode($task);
