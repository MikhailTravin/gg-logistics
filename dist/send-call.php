<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(['success' => false, 'message' => 'Только POST запросы']);
	exit;
}

// Email получателя
$to_email = 'globalshop1@mail.ru';

// Формируем тело письма
$body = "Заявка на звонок\n\n";

if (isset($_POST['name']) && !empty($_POST['name'])) {
	$body .= 'Имя: ' . $_POST['name'] . "\n";
}

if (isset($_POST['phone']) && !empty($_POST['phone'])) {
	$body .= 'Телефон: ' . $_POST['phone'] . "\n";
}

if (empty($_POST['name']) && empty($_POST['phone'])) {
	$body .= "Данные не получены\n";
	$body .= "POST данные: " . print_r($_POST, true);
}

// Тема письма
$subject = '=?UTF-8?B?' . base64_encode('Заявка на звонок - ' . date('d.m.Y H:i')) . '?=';

// Заголовки письма
$headers = "From: noreply@gg-logistics.ru\r\n";
$headers .= "Reply-To: noreply@gg-logistics.ru\r\n";
$headers .= "Content-Type: text/plain; charset=utf-8\r\n";
$headers .= "Content-Transfer-Encoding: 8bit\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// Дополнительные заголовки для уменьшения спама
$headers .= "MIME-Version: 1.0\r\n";

// Отправка письма
if (mail($to_email, $subject, $body, $headers)) {
	echo json_encode(['success' => true, 'message' => 'Данные успешно отправлены!']);
}
