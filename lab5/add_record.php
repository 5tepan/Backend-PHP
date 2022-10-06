<?php
    include './db.php';
    $DECLARED_PRIORITY_FIELDS = ['low', 'medium', 'high'];

    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $data_start_date = isset($_POST['data_start_date']) ? $_POST['data_start_date'] : '';
    $data_start_time = isset($_POST['data_start_time']) ? $_POST['data_start_time'] : '';
    $data_end_date = isset($_POST['data_end_date']) ? $_POST['data_end_date'] : '';
    $data_end_time = isset($_POST['data_end_time']) ? $_POST['data_end_time'] : '';
    $priority = isset($_POST['priority']) ? $_POST['priority'] : '';

    if (!in_array($priority, $DECLARED_PRIORITY_FIELDS)) {
        $priority = '';
    }

    $data_start = "$data_start_date $data_start_time";
    $data_end = "$data_end_date  $data_end_time";
    $params = [];
    $insertSql = '';
    $flag = 0;

    $insertSql = "(description, data_start, data_end, priority) VALUES (:description, :data_start, :data_end, :priority)";
    $params[':description'] = $description;
    $params[':data_start'] = $data_start;
    $params[':data_end'] = $data_end;
    $params['priority'] = $priority;

    $hasEmptyField = $description == '' || $data_start_time == '' || $data_start_date == '' || $data_end_time == '' || $data_end_date == '' || $priority == '';

   if (!$hasEmptyField) {
   		try {
	        $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
	        $sql = $db->prepare("INSERT INTO todo $insertSql");
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
    <title>Add</title>
    <link rel="stylesheet" type="text/css" href="./styles/add_record.css">
</head>
<body>
	<div class="info">
		<?= $hasEmptyField ? 'Заполните все поля!' : ''?>
	</div>
	<form class="add_record_form" action="./add_record.php" method="POST">
		<div class="container <?= $description == '' ? 'error' : '' ?>">
			<span>Description</span>
			<div class="container__inner">
				<textarea type="text" name="description" placeholder="Descrition"><?= $description ?? ''?></textarea>
			</div>
		</div>
		<div class="container <?= ($data_start_date == '' || $data_start_time == '') ? 'error' : '' ?>">
			<span>Data Start</span>
			<div class="container__inner">
				<input type="date" name="data_start_date" placeholder="Data start" value="<?= $data_start_date ?? '' ?>" />			
				<input type="time" name="data_start_time" placeholder="Data start" value="<?= $data_start_time ?? '' ?>" />			
			</div>
		</div>
		<div class="container <?= ($data_start_date == '' || $data_end_time == '') ? 'error' : '' ?>">
			<span>Data End</span>
			<div class="container__inner">
				<input type="date" name="data_end_date" placeholder="Data end" value="<?= $data_end_date ?? '' ?>" />			
				<input type="time" name="data_end_time" placeholder="Data end" value="<?= $data_end_time ?? '' ?>" />			
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
		<input class="submit_btn" type="submit"/>
	</form>
	<a href="./index.php">На главную</a>
</body>