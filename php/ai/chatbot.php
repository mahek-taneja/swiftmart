<?php
// php/ai/chatbot.php
require_once __DIR__ . '/../../includes/config.php';

function ai_chat(string $message, bool $include_context = true): array {
	$message = trim($message);
	if ($message === '') return ['error' => 'Empty message'];

	$port = getenv('FLASK_PORT');
	if (!$port) { $port = '5055'; }
	$url = "http://127.0.0.1:$port/chat";

	$payload = json_encode([
		'message' => $message,
		'include_context' => $include_context
	]);

	$ch = curl_init($url);
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $payload,
		CURLOPT_HTTPHEADER => [
			'Content-Type: application/json',
			'Accept: application/json'
		]
	]);
	$response = curl_exec($ch);
	$err = curl_error($ch);
	$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($err) return ['error' => 'AI chat request failed: ' . $err];
	if ($code < 200 || $code >= 300) return ['error' => 'AI chat HTTP ' . $code];
	$data = json_decode($response, true);
	return is_array($data) ? $data : ['error' => 'Invalid AI response'];
}