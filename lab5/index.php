<?php
    include './db.php';
    const DEFAULT_COUNT_VIEW = 5;
    const DEFAULT_PAGE = 1;
    $DECLARED_ORDER_FIELDS = ['id_task', 'description', 'data_start', 'data_end', 'priority'];
    $DECLARED_PRIORITY_FIELDS = ['low', 'medium', 'high'];

    $page = isset($_GET['page']) ? (int)$_GET['page'] : DEFAULT_PAGE;
    $count_view = isset($_GET['count_view']) ? (int)$_GET['count_view'] : DEFAULT_COUNT_VIEW;
    $checkboxes = isset($_POST['check']) ? $_POST['check'] : [];

    if ($count_view <= 0) {
        $count_view = DEFAULT_COUNT_VIEW;
    }
    if ($page <= 0) {
        $page = DEFAULT_PAGE;
    }

    $id_task = isset($_GET['id_task']) && !empty($_GET['id_task']) ? (int)($_GET['id_task']) : null;
    $description = $_GET['description'] ?? '';
    $data_start = $_GET['data_start'] ?? '';
    $data_end = $_GET['data_end'] ?? '';
    $priority = $_GET['priority'] ?? '';
    $order_field = $_GET['order_field'] ?? 'id_task';
    $order_type = isset($_GET['order_type']) ? ($_GET['order_type'] == "DESC" ? "DESC" : "ASC") : "ASC";

    $id_to_delete = isset($_GET['id_to_delete']) && !empty($_GET['id_to_delete']) ? (int)($_GET['id_to_delete']) : null;

    $params = [];
    $whereSql = '';
    $flag = 0;

    if (!empty($checkboxes)) {
        try {
            $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            $taskIDS = '';
            foreach ($checkboxes as $key => $taskID) {
                $taskIDS .= $taskID . ', ';
            }
            $taskIDS = trim($taskIDS, ", ");
            $sql = $db->prepare("DELETE from todo WHERE id_task IN ($taskIDS)");
            $sql->execute();
        } catch(PDOException $e) {  
            echo $e->getMessage();  
            file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
        }
    }

    if (!is_null($id_to_delete)) {
        try {
            $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            $sth = $db->prepare("DELETE FROM todo WHERE id_task = :id_to_delete");
            $sth->bindParam(':id_to_delete', $id_to_delete, PDO::PARAM_INT);
            $sth->execute();
        } catch(PDOException $e) {  
            echo $e->getMessage();  
            file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
        }
    }

    if (!in_array($order_field, $DECLARED_ORDER_FIELDS)) {
        $order_field = 'id_task';
    }
    if (!in_array($priority, $DECLARED_PRIORITY_FIELDS)) {
        $priority = '';
    }

    if ($id_task) {
        $whereSql .= 'WHERE id_task = :id_task';
        $params[':id_task'] = $id_task;
        $flag++;
    }
    if ($description != '') {
        if ($flag > 0) {
            $whereSql .= ' AND description = :description';
        } else {
            $whereSql .= 'WHERE description = :description';
        }
        $params[':description'] = $description;
        $flag++;
    }
    if ($priority != '') {
        if ($flag > 0) {
            $whereSql .= ' AND priority = :priority';
        } else {
            $whereSql .= 'WHERE priority = :priority';
        }
        $params[':priority'] = $priority;
        $flag++;
    }

    if ($data_start != '' && $data_end != '') {
        if ($flag > 0) {
            $whereSql .= ' AND data_start >= :data_start AND data_start <= :data_end AND data_end >= :data_start AND data_end <= :data_end';
        } else {
            $whereSql .= 'WHERE data_start >= :data_start AND data_start <= :data_end AND data_end >= :data_start AND data_end <= :data_end';
        }
        $params[':data_start'] = $data_start;
        $params[':data_end'] = $data_end;
        $flag++;
    } else if ($data_start != '' && $data_end == '') {
        if ($flag > 0) {
            $whereSql .= ' AND data_start >= :data_start AND data_end >= :data_start';
        } else {
            $whereSql .= 'WHERE data_start >= :data_start AND data_end >= :data_start';
        }
        $params[':data_start'] = $data_start;
        $flag++;
    } else if ($data_start == '' && $data_end != '') {
        if ($flag > 0) {
            $whereSql .= ' AND data_end <= :data_end AND data_end <= :data_end';
        } else {
            $whereSql .= 'WHERE data_end <= :data_end AND data_end <= :data_end';
        }
        $params[':data_end'] = $data_end;
        $flag++;
    }


    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);

        $total_results;
        if ($whereSql != '') {
            $total_results = $db->prepare("SELECT COUNT(*) FROM todo $whereSql");            
        } else {
            $total_results = $db->prepare("SELECT COUNT(*) FROM todo");            
        }
        $total_results->execute($params);
        $total_results = ($total_results->fetchAll())[0][0];

        $total_pages = ceil($total_results / $count_view);

        if ($page > $total_pages) {
            $page = $total_pages;
        }

        $start = ($page - 1) * $count_view;
        
        $stmt;
        if ($whereSql != '') {
            $stmt = $db->prepare("SELECT * FROM todo $whereSql ORDER BY $order_field $order_type LIMIT $start, $count_view");
        } else {
            $stmt = $db->prepare("SELECT * FROM todo ORDER BY $order_field $order_type LIMIT $start, $count_view");
        }

        
        $stmt->execute($params);
        $results = $stmt->fetchAll();
    }
    catch(PDOException $e) {  
        $results = []; 
        file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 6</title>
    <link rel="stylesheet" type="text/css" href="./styles/index.css">
</head>
<body>
    <div class="content-inner">
        <a class="add_record" href="./add_record.php">Добавить запись</a>
        <form action="./index.php" class="filter-form">
            <div class="container">
                <span>count_view</span>
                <select name="count_view">
                    <option <?= ($count_view == 5 ? 'selected' : '');?> value="5">5</option>
                    <option <?= ($count_view == 15 ? 'selected' : '');?> value="15">15</option>
                    <option <?= ($count_view == 20 ? 'selected' : '');?> value="20">20</option>
                    <option <?= ($count_view == 30 ? 'selected' : '');?> value="30">30</option>
                </select>
            </div>
            <div class="container">
                <span>id_task</span>
                <input 
                    type="number" 
                    placeholder="id_task" 
                    name="id_task" 
                    value="<?= $id_task ?? '' ?>" 
                />
            </div>
            <div class="container">
                <span>description</span>
                <input 
                    type="text" 
                    placeholder="description" 
                    name="description" 
                    value="<?= $description ?? '' ?>" 
                />
            </div>
            <div class="container">
                <span>data_start</span>
                <input 
                    type="text" 
                    placeholder="data_start"
                    name="data_start" 
                    value="<?= $data_start ?>" 
                />
            </div>
            <div class="container">
                <span>data_end</span>
                <input 
                    type="text" 
                    placeholder="data_end" 
                    name="data_end" 
                    value="<?= $data_end ?>"
                />
            </div>
            <div class="container">
                <span>priority</span>
                <select name="priority">
                    <option value=""></option>
                    <option <?= ($priority == 'low' ? 'selected' : '');?> value="low">low</option>
                    <option <?= ($priority == 'medium' ? 'selected' : '');?> value="medium">medium</option>
                    <option <?= ($priority == 'high' ? 'selected' : '');?> value="high">high</option>
                </select>
            </div>
            <div class="container">
                <span>order_type</span>
                <select name="order_type">
                    <option <?= ($order_type == 'ASC' ? 'selected' : '');?> value="ASC">ПО возр</option>
                    <option <?= ($order_type == 'DESC' ? 'selected' : '');?> value="DESC">ПО убыв</option>
                </select>
            </div>
            <div class="container">
                <span>order_field</span>
                <select name="order_field">
                    <option <?= ($order_field == 'id_task' ? 'selected' : '');?> value="id_task">id_task</option>
                    <option <?= ($order_field == 'description' ? 'selected' : '');?> value="description">description</option>
                    <option <?= ($order_field == 'data_start' ? 'selected' : '');?> value="data_start">data_start</option>
                    <option <?= ($order_field == 'data_end' ? 'selected' : '');?> value="data_end">data_end</option>
                    <option <?= ($order_field == 'priority' ? 'selected' : '');?> value="priority">priority</option>
                </select>
            </div>
            <input type="submit" value="Отфильтровать" />
        </form>
        <form method="POST" action="./index.php">
        <table class="todo-table">
        <?php 
                $linkString = "";
                if (!empty($id_task)) {
                    $linkString .= "&id_task=$id_task";
                }
                if (!empty($description)) {
                    $linkString .= "&description=$description";
                }
                if (!empty($data_start)) {
                    $linkString .= "&data_start=$data_start";
                }
                if (!empty($data_end)) {
                    $linkString .= "&data_end=$data_end";
                }
                if (!empty($priority)) {
                    $linkString .= "&priority=$priority";
                }
                if (!empty($order_type)) {
                    $linkString .= "&order_type=$order_type";
                }
            ?>
            <?= (empty($results)) ? 'Нет записей' : '' ?>
            <tr>
                <th></th>
                <th id="id_task" class="mainTr">id_task</th>
                <th id="description" class="mainTr">description</th>
                <th id="data_start" class="mainTr">data_start</th>
                <th id="data_end" class="mainTr">data_end</th>
                <th id="priority" class="mainTr">priority</th>
                <th>delete_action</th>
                <th>edit_action</th>
            </tr>          
            <?php foreach ($results as $key => $value) : ?>
            <tr>
                <th>
                    <input type='checkbox' name='check[]' value=<?= $value['id_task'];?> />
                </th>
                <th><?= $value['id_task'] ?></th>
                <th><?= $value['description'] ?></th>
                <th><?= $value['data_start'] ?></th>
                <th><?= $value['data_end'] ?></th>
                <th><?= $value['priority'] ?></th>
                <th>
                    <a href="<?= '?page='.$page . '&count_view='.$count_view . $linkString . '&order_field='.$order_field . '&id_to_delete='.$value['id_task'];?>">Удалить</a>
                </th>
                <th>
                    <form action="./edit_record.php" method="POST">
                        <input type="hidden" type="text" name="id_to_edit" value="<?= $value['id_task'] ?>">
                        <input type="hidden" name="description" value="<?= $value['description'] ?>">
                        <input type="hidden" name="priority" value="<?= $value['priority'] ?>">
                        <input type="submit" value="Редактировать">
                    </form>
                </th>
            </tr>
            <?php endforeach; ?>
                <input type="submit" class="delete_records" name="check" value="Удалить записи" />
            </form>
        </table>
        <ul class="page_list">
            <?php for($i = 1; $i <= $total_pages; $i++) :?>
                <li class="<?= $page==$i ? 'active' : ''; ?>">
                    <a href="<?= '?page='.$i . '&count_view='.$count_view . $linkString . '&order_field='.$order_field;?>"><?= $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </div>
</body>
</html>