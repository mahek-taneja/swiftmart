<?php
// php/ai/forecast.php
require_once __DIR__ . '/../../includes/config.php';

function ai_forecast(array $options = []): array {
	$port = getenv('FLASK_PORT');
	if (!$port) { $port = '5055'; }
	$horizon = isset($options['horizon']) ? (int)$options['horizon'] : 90;
	if (!in_array($horizon, [30,60,90], true)) { $horizon = 90; }
	$vendorId = isset($options['vendor_id']) ? (int)$options['vendor_id'] : null;
	$q = http_build_query(array_filter([
		'horizon' => $horizon,
		'vendor_id' => $vendorId
	], fn($v) => $v !== null));
	$url = "http://127.0.0.1:$port/forecast" . ($q ? ("?".$q) : "");

	$ch = curl_init($url);
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 25,
		CURLOPT_HTTPHEADER => ['Accept: application/json']
	]);
	$response = curl_exec($ch);
	$err = curl_error($ch);
	$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($err) {
		return ['error' => 'AI forecast request failed: ' . $err];
	}
	if ($code < 200 || $code >= 300) {
		return ['error' => 'AI forecast HTTP ' . $code];
	}
	$data = json_decode($response, true);
	if (!is_array($data)) {
		return ['error' => 'Invalid AI response'];
	}
	return $data;
}