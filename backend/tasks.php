<?php

// putenv("DATABASE_URL=postgres://jfsawnnq:MNZJAGslJffM_blSmP5hiwW-kPGpJxOP@balarama.db.elephantsql.com:5432/jfsawnnq");
// putenv("DATABASE_URL=postgres://quseqbwvkworot:30e79c7081ea6450af9114986e1ef8b76894f5e8953b6249f60e1c76b9d80bcb@ec2-46-137-187-23.eu-west-1.compute.amazonaws.com:5432/d1vdgilkilim74");

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

// echo $_SERVER['REQUEST_URI'];

switch ($_SERVER['REQUEST_METHOD']) {
    case "GET":
        $stmt = $pdo->prepare("SELECT * FROM tasks ");

        break;
    case "POST":
        //TODO
        break;
    default:
        header('HTTP/1.0 405 Not Found');
        die();
        break;
}

// echo $task_id;
// $stmt->execute([":task_id" => $task_id]);
// $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = :id");
// if ($id = $_GET['id']) {
//     $stmt->bindValue(':id', $id);
// }
$stmt->execute();
$tasks = $stmt->fetchAll();

// echo var_dump($task);
header('Content-type: application/json');
echo json_encode(array_values($tasks));
