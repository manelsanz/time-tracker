<?php

putenv("DATABASE_URL=postgres://quseqbwvkworot:30e79c7081ea6450af9114986e1ef8b76894f5e8953b6249f60e1c76b9d80bcb@ec2-46-137-187-23.eu-west-1.compute.amazonaws.com:5432/d1vdgilkilim74");

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

switch ($_SERVER['REQUEST_METHOD']) {
    case "GET":
        $query = $pdo->query("SELECT * FROM tasks ORDER BY id DESC");
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        break;
        
    case "POST":
        $status = $_POST['status'];
        $date = $_POST['date'];

        if ($status == 1) {
            // START
            $name = $_POST['name'];
            // Find the task by name and filter only today
            $query = $pdo->prepare("SELECT * FROM tasks WHERE name = ? LIMIT 1");
            $query->execute([ $name ]);
            // If found: Get the task
            $result = $query->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                // If not found: Create a new task
                $query = $pdo->prepare("INSERT INTO tasks (name, elapsed, created_date) values (?,?,?)");
                $query->execute([$name, 0, $date]);
                $result = [
                    'id' => $pdo->lastInsertId(),
                    'name' => $name,
                    'elapsed' => 0,
                    'created_date' => $date,
                    'exist' => false
                ];
            } else {
                $result['exist'] = true;
            }
            // Insert new period
            $query = $pdo->prepare("INSERT INTO periods (task_id, start_date) values (?,?)");
            $query->execute([$result['id'], $date]);

        } else {
            // STOP
            $id = $_POST['id'];
            $elapsed = $_POST['elapsed'];

            // Find the task
            $query = $pdo->prepare("SELECT * FROM tasks WHERE id = ? LIMIT 1");
            $query->execute([$id]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            $total_elapsed = $result['elapsed'] + $elapsed;

            // Find last period.
            $query = $pdo->prepare("SELECT p.id FROM periods p JOIN tasks t ON t.id = p.task_id WHERE p.task_id = ? ORDER BY p.start_date DESC LIMIT 1");
            $query->execute([$id]);
            $result_period = $query->fetch(PDO::FETCH_ASSOC);

            // Update period stop_date.
            $query = $pdo->prepare("UPDATE periods SET stop_date = ? WHERE id = ?");
            $query->execute([$date, $result_period['id']]);

            // Update task with elapsed time added
            $query = $pdo->prepare("UPDATE tasks SET elapsed = ? WHERE id = ?");
            $query->execute([$total_elapsed, $result['id']]);

            $result['elapsed'] = $total_elapsed;
        }

        break;
    default:
        header('HTTP/1.0 405 Not Found');
        die();
        break;
}

header('Content-type: application/json');
echo json_encode($result);

exit(0);
