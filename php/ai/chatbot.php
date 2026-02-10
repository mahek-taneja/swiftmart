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
		CURLOPT_TIMEOUT => 30,
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $payload,
		CURLOPT_HTTPHEADER => [
			'Content-Type: application/json',
			'Accept: application/json'
		],
		CURLOPT_FAILONERROR => false, // Don't fail on HTTP errors, let us handle them
		CURLOPT_SSL_VERIFYPEER => false // For localhost
	]);
	$response = curl_exec($ch);
	$err = curl_error($ch);
	$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	// Handle errors gracefully
	if ($err) {
		// Try to parse response even if curl reports error (might have fallback response)
		if (!empty($response)) {
			$data = json_decode($response, true);
			if (is_array($data) && isset($data['answer'])) {
				return $data; // Return fallback response if available
			}
		}
		// If Flask is not running, provide a helpful error
		if (strpos($err, 'Connection refused') !== false || strpos($err, 'failed to connect') !== false) {
			return [
				'answer' => 'Flask service is not running. Please start it with: cd ai && python app.py',
				'error' => 'Cannot connect to Flask service on port ' . $port,
				'model' => 'error'
			];
		}
		return ['error' => 'AI chat request failed: ' . $err];
	}
	
	// Parse response
	if (empty($response)) {
		return ['error' => 'Empty response from Flask service'];
	}
	
	$data = json_decode($response, true);
	if (!is_array($data)) {
		return ['error' => 'Invalid JSON response: ' . substr($response, 0, 100)];
	}
	
	// Check HTTP status code
	if ($code < 200 || $code >= 300) {
		// Try to get answer even from error responses
		if (isset($data['answer'])) {
			return $data;
		}
		return ['error' => 'AI chat HTTP ' . $code . ': ' . ($data['error'] ?? 'Unknown error')];
	}
	
	// Always return answer if present, even if there's a warning
	if (isset($data['answer'])) {
		return $data;
	}
	
	return ['error' => 'Invalid AI response: no answer field. Response: ' . json_encode($data)];
}