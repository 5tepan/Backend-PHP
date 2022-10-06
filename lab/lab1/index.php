<!DOCTYPE HTML>
<html lang="ru">
<head>
    <meta charset = "UTF-8">
</head>
<body>
    <h1>Калькулятор</h1>
    <form action="" method="post" class="calculate_form">
        <input type="text" name="number1" value="<?= $_POST['number1'] ?? '' ?>">
	<select class="operations" name="operation">
	    <option value='плюс'> + </option>
	    <option value='минус'> - </option>
	    <option value="умножение"> * </option>
	    <option value="деление"> / </option>
	</select>
	<input type="text" name="number2" value="<?php echo $_POST['number2'] ?? '' ?>">	
	<input class="submit_form" type="submit" name="submit" value="Получить ответ">
	</form>
</body>
</html>

<?php
if(isset($_POST['submit'])){
	$number1 = $_POST['number1'];
	$number2 = $_POST['number2'];
	$operation = $_POST['operation'];
	
	// ошибки
	if(!$operation || ('0' != !$number1 && $number1) || ('0' != !$number2 && $number2)) {
		$error_result = 'Не все поля заполнены!';
	}
    else {
	    
		if(!is_numeric($number1) || !is_numeric($number2)) {
			$error_result = 'Введите числа!';
		}
		else 
        switch($operation){
			case 'плюс':
			    $result = $number1 + $number2;
			    break;
			case 'минус':
			    $result = $number1 - $number2;
			    break;
			case 'умножение':
			    $result = $number1 * $number2;
			    break;
			case 'деление':
			    if('0' == $number2)
			    	$error_result = 'На ноль делить нельзя!';
			    else
			       $result = $number1 / $number2;
			    break;    
		}
	    
	}
    if(isset($error_result)){
    	echo "<div class='error_text'>Ошибка: $error_result</div>";
    }	
    else {
	    echo "<div class='answer_text'>Ответ: $result</div>";
    }
}
?>
