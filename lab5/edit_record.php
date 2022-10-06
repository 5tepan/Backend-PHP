<?php
	include './db.php';
    $DECLARED_PRIORITY_FIELDS = ['low', 'medium', 'high'];

    $id_to_edit = isset($_POST['id_to_edit']) && !empty($_POST['id_to_edit']) ? (int)($_POST['id_to_edit']) : null;

    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $data_start_date = isset($_POST['data_start_date']) ? $_POST['data_start_date'] : '';
    $data_start_time = isset($_POST['data_start_time']) ? $_POST['data_start_time'] : '';
    $data_end_date = isset($_POST['data_end_date']) ? $_POST['data_end_date'] : '';
    $data_end_time = isset($_POST['data_end_time']) ? $_POST['data_end_time'] : '';
    $priority = isset($_POST['priority']) ? $_POST['priority'] : '';

    if (!in_array($priority, $DECLARED_PRIORITY_FIELDS)) {
        $priority = '';
    }

 	$data_start = "$data_start_date $data_start_time";
    $data_end = "$data_end_date $data_end_time";
    $params = [];
    $updateSql = '';
    $flag = 0;

    if ($description != '') {
    	if ($flag > 0) {
    		$updateSql .= ', description = :description';
    	} else {
    		$updateSql .= 'description = :description';
    	}
    	$params[':description'] = $description;
    	$flag++;
    }
    if ($priority != '') {
    	if ($flag > 0) {
    		$updateSql .= ', priority = :priority';
    	} else {
    		$updateSql .= 'priority = :priority';
    	}
    	$params[':priority'] = $priority;
    	$flag++;
    }
    if ($data_start != ' ') {
    	if ($flag > 0) {
    		$updateSql .= ', data_start = :data_start';
    	} else {
    		$updateSql .= 'data_start = :data_start';
    	}
    	$params[':data_start'] = $data_start;
    }
    if ($data_end != ' ') {
    	if ($flag > 0) {
    		$updateSql .= ', data_end = :data_end';
    	} else {
    		$updateSql .= 'data_end = :data_end';
    	}
    	$params['data_end'] = $data_end;
    	$flag++;
    }

    $hasFilledField = $description != '' || $data_start_date != '' || $data_start_time != '' || $data_end_date != '' || $data_end_time != '' || $priority != '';

    if (!is_null($id_to_edit) && $hasFilledField) {
    	try {
	        $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
	        $sql = $db->prepare("UPDATE todo SET $updateSql WHERE id_task = :id_task");
	        $params[':id_task'] = $id_to_edit;
	        $sql->execute($params);
    	}
     	catch(PDOException $e) {  
        	echo $e->getMessage();  
        	file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
    	}
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit</title>
    <link rel="stylesheet" type="text/css" href="./styles/edit_record.css">
</head>
<body>
	<div class="info">
		Редактирование поля с id = <?= $id_to_edit?>
	</div>
	<form class="edit_record_form" action="./edit_record.php" method="POST">
		<input style="display: none" type="number" name="id_to_edit" value="<?= $id_to_edit ?>">
		<div class="container">
			<span>Description</span>
			<div class="container__inner">
				<textarea type="text" name="description" placeholder="Descrition"></textarea>
			</div>
		</div>
		<div class="container">
			<span>Data Start</span>
			<div class="container__inner">
				<input type="date" name="data_start_date" placeholder="Data start" />			
				<input type="time" name="data_start_time" placeholder="Data start" />			
			</div>
		</div>
		<div class="container">
			<span>Data End</span>
			<div class="container__inner">
				<input type="date" name="data_end_date" placeholder="Data end" />			
				<input type="time" name="data_end_time" placeholder="Data end" />			
			</div>
		</div>
		<div class="container">
			<span>Priority</span>
			<div class="container__inner">
				<select name="priority">
					<option selected value="low">Low</option>
					<option value="medium">Medium</option>
					<option value="high">High</option>
				</select>		
			</div>
		</div>
		<input class="submit_btn" type="submit" value="Отредактировать" />
	</form>
	<a href="./index.php">На главную</a>
</body>