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

$stmt = $pdo->query("SELECT * FROM tasks ORDER BY id DESC LIMIT 1");
$task = $stmt->fetch();

// echo var_dump($task);
return json_encode($var_dump);
