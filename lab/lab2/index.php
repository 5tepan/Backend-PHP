<?php
    include './db.php';

    try {

        $DBH = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
        $STH = $DBH->prepare('SELECT * FROM todo');
        
        $STH->execute();
        $STH->setFetchMode(PDO::FETCH_ASSOC);
        
        $todos = $STH->fetchAll();
    }
    catch(PDOException $e) {  
        echo $e->getMessage();  
        file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
    }

    function parseDate($date) {
        $splitedDate = explode("-", $date);
        return "$splitedDate[2]-$splitedDate[1]-$splitedDate[0]";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Lab 2</title>
</head>
<body>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');
        .todo-table {
            border: 3px solid #007cba;
            width: 600px;
            margin: 0 auto;
            border-collapse: collapse;
            font-family: 'Roboto', sans-serif;
        }
        .todo-table th, td {
             border: 1px solid #007cba;
             padding: 5px;
        }
    </style>
    <table class="todo-table">
        <tr>
            <th>id_task</th>
            <th>description</th>
            <th>data_start</th>
            <th>data_end</th>
            <th>priority</th>
        </tr>
        <?php foreach ($todos as $key => $value) : ?>
        <tr>
            <th><?= $value['id_task'] ?></th>
            <th><?= $value['description'] ?></th>
            <th><?= parseDate($value['data_start']) ?></th>
            <th><?= parseDate($value['data_end']) ?></th>
            <th><?= $value['priority'] ?></th>
        </tr>
    <?php endforeach; ?>
    </table>
</body>
</html>