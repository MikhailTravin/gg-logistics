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
$body = "Заявка на аудит и расчет\n\n";

if (isset($_POST['name']) && !empty($_POST['name'])) {
	$body .= 'Имя: ' . $_POST['name'] . "\n";
}

if (isset($_POST['phone']) && !empty($_POST['phone'])) {
	$body .= 'Телефон/Мессенджер: ' . $_POST['phone'] . "\n";
}

if (empty($_POST['name']) && empty($_POST['phone'])) {
	$body .= "Данные не получены\n";
	$body .= "POST данные: " . print_r($_POST, true);
}

// Тема письма
$subject = '=?UTF-8?B?' . base64_encode('Заявка на аудит и расчет - ' . date('d.m.Y H:i')) . '?=';

// Проверяем, есть ли файл
$hasFile = isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK && $_FILES['file']['size'] > 0;

if ($hasFile) {
	// Если есть файл - отправляем с вложением
	$file = $_FILES['file'];
	$fileContent = file_get_contents($file['tmp_name']);
	$fileName = $file['name'];
	$fileType = mime_content_type($file['tmp_name']) ?: 'application/octet-stream';

	$boundary = md5(uniqid(rand(), true));

	// Заголовки для письма с вложением
	$headers = "From: noreply@gg-logistics.ru\r\n";
	$headers .= "Reply-To: noreply@gg-logistics.ru\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
	$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

	// Формируем письмо
	$message = "--$boundary\r\n";
	$message .= "Content-Type: text/plain; charset=utf-8\r\n";
	$message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
	$message .= $body . "\r\n\r\n";

	// Добавляем вложение
	$message .= "--$boundary\r\n";
	$message .= "Content-Type: $fileType; name=\"" . basename($fileName) . "\"\r\n";
	$message .= "Content-Disposition: attachment; filename=\"" . basename($fileName) . "\"\r\n";
	$message .= "Content-Transfer-Encoding: base64\r\n\r\n";
	$message .= chunk_split(base64_encode($fileContent)) . "\r\n";
	$message .= "--$boundary--";
} else {
	// Если файла нет - отправляем обычное письмо
	$headers = "From: noreply@gg-logistics.ru\r\n";
	$headers .= "Reply-To: noreply@gg-logistics.ru\r\n";
	$headers .= "Content-Type: text/plain; charset=utf-8\r\n";
	$headers .= "Content-Transfer-Encoding: 8bit\r\n";
	$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
	$headers .= "MIME-Version: 1.0\r\n";

	$message = $body;
}

// Отправка письма
if (mail($to_email, $subject, $message, $headers)) {
	echo json_encode(['success' => true, 'message' => 'Данные успешно отправлены!']);
} else {
	echo json_encode(['success' => false, 'message' => 'Ошибка при отправке письма']);
}
