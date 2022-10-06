<?php
    include './db.php';

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $count_view = isset($_GET['count_view']) ? (int)$_GET['count_view'] : 5;

    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);

        $total_results = $db->query("SELECT COUNT(*) FROM todo")->fetchColumn();
        $total_pages = ceil($total_results / $count_view);
        $start = ($page - 1) * $count_view;

        $stmt = $db->prepare("SELECT * FROM todo LIMIT :start, :count_view");
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->bindParam(':count_view', $count_view, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll();
    }
     catch(PDOException $e) {  
        echo $e->getMessage();  
        file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
    }

    function parseDate($date) {
        $splitedDate = explode('-', $date);
        return "$splitedDate[2]-$splitedDate[1]-$splitedDate[0]";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 3</title>
    <link rel="stylesheet" type="text/css" href="./style.css">
</head>
<body>
    <form action="./index.php">
        <select name="count_view">
            <option <?= ($count_view == 5 ? 'selected' : '');?> value="5">5</option>
            <option <?= ($count_view == 15 ? 'selected' : '');?> value="15">15</option>
            <option <?= ($count_view == 20 ? 'selected' : '');?> value="20">20</option>
            <option <?= ($count_view == 30 ? 'selected' : '');?> value="30">30</option>
        </select>
        <input type="submit" value="Применить">
    </form>
    <table class="todo-table">
        <tr>
            <th>id_task</th>
            <th>description</th>
            <th>data_start</th>
            <th>data_end</th>
            <th>priority</th>
        </tr>
        <?php foreach ($results as $key => $value) : ?>
        <tr>
            <th><?= $value['id_task'] ?></th>
            <th><?= $value['description'] ?></th>
            <th><?= parseDate($value['data_start']) ?></th>
            <th><?= parseDate($value['data_end']) ?></th>
            <th><?= $value['priority'] ?></th>
        </tr>
        <?php endforeach; ?>
    </table>
    <ul class="page_list">
        <?php for($i = 1; $i <= $total_pages; $i++) :?>
            <li class="<?= $page==$i ? 'active' : ''; ?>">
                <a href="<?= '?page='.$i . '&count_view='.$count_view; ?>"><?= $i; ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</body>
</html>