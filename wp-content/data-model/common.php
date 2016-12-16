<?php

# http://php.su
# https://jsonformatter.curiousconcept.com/
	
/********************************
	!! ПРОВЕРЬ КОДИРОВКУ ФАЙЛА !!
				UTF-8 без BOM

	!! 	  ВКЛЮЧИ ОШИБКИ В PHP 	   !!
********************************/

error_reporting (E_ALL);
ini_set('error_reporting', E_ALL);
if (!ini_get('display_errors')) 
	ini_set('display_errors', '1');

# установка ответа и кодировка страницы
header("Content-Type: text/html; charset=UTF8"); 

# пробигаем по всем параметрам и "защищаемся" от SQL инъекции
# теперь фильтровать нужно только цифры, строки уже прошли проверку
if (isset($_REQUEST)) {
	foreach ($_REQUEST as &$val) {
		$val = filter_str($val);
	}
}
# магическая функция параметры превращает в переменные
extract ($_REQUEST);

// *****************************************************************************************
// ** ОТВЕТЫ КЛИЕНТУ ***********************************************************************
function msgOK() {
# положительный ответ
	echo '{"status":"OK"}';
	exit;
}

function msgJSON($arr) {
# положительный ответ c JSON
    echo '{"status":"OK","data":'.json_result($arr).'}';
}

function okJSON($arr) {
# положительный ответ c JSON
    return '{"status":"OK","data":'.json_result($arr).'}';
}

function errorJSON($msg) {
# ответ об ошибке в JSON
    return '{"status":"ERROR","data":"'.$msg.'"}';
}

function msgErr($msg) {
# отрицательный ответ, с описанием ошибки
    echo '{"status":"ERROR","data":"'.$msg.'"}';
}

// ** ФИЛЬТРЫ ПАРАМЕТРОВ *******************************************************************
function filter_str($value) {
# фильтруем строки, защита от SQL инъекции
	global $db; # так указываются глобальные переменные
	if (!$db)
		return addslashes($value);
	else
		return mysqli_real_escape_string($db, $value);
}

function filter_int($value) {
# фильтруем числа	
	return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
}

function filter_flt($value) {
# фильтруем дробные
	return filter_var ($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
}

// ** РАЗНОЕ *******************************************************************************
function json_result($arr) {
# формируем JSON из массива данных 
  return json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}


function passGenerator($min = 6, $max = 9) {
# генератор пароля
	$pass = '';
	$chars = "1234567890qwertyuioplkjhgfdsazxcvbnmMNBVCXZASDFGHJKLPOIUYTREWQ";
	$count_char_pass = rand($min, $max);
	for ($i = 0; $i < $count_char_pass; $i++) {
		$numberChar = rand(0, iconv_strlen($chars, 'UTF-8')-1);
		$pass .= $chars[$numberChar];
	}
	return $pass;
}

function uniqueStr() {
# генерируется уникальная строка, в md5
# используется для сохранения имён картинок / url
	return md5(rand(1000,9999).time()); 
}
	
// ** РАБОТАЕМ С MYSQL ********************************************************************
function getLastID() {
# возвращает последний добавленный ID в базу
	global $db;
	return mysqli_insert_id($db);
}

function selectQuery($sql) {
# функция для SELECT запросов, возвращает массив данных
	global $db; # так указываются глобальные переменные
	$result = mysqli_query($db, $sql) or msgErr(mysqli_error($db));
	$arr = array();
	if (mysqli_num_rows($result) > 0) {
		while($r = mysqli_fetch_assoc($result)) {
			$arr[] = $r;
		}
	}
	return $arr;
}

function queryComplete($sql) {
# функция для UPDATE/INSERT запросов
# используется когда нужно просто получить положительный ответ выполнения запроса
# отправляется сразу клиенту
	global $db; # так указываются глобальные переменные
	$result = mysqli_query($db, $sql) or msgErr(mysqli_error($db));
	if (mysqli_affected_rows($db) > 0)
		echo msgOK();
}

function queryResult($sql) {
# функция для UPDATE/INSERT запросов
# используется когда после запроса идут еще действия с проверкой IF ()	
	global $db; # так указываются глобальные переменные
	$result = mysqli_query($db, $sql) or msgErr(mysqli_error($db), true);
	if (mysqli_affected_rows($db) > 0) 
		return true;
	else 
		return false;
}