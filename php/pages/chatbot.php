<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../ai/chatbot.php';

$answer = null; $error = null; $message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$message = trim($_POST['message'] ?? '');
	$include = isset($_POST['include_context']) ? true : false;
	$res = ai_chat($message, $include);
	if (!empty($res['error'])) $error = $res['error'];
	else $answer = $res['answer'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Chatbot</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
	<h1 class="mb-3">AI Chatbot</h1>
	<form method="post" class="card p-3 mb-3">
		<div class="mb-2">
			<label class="form-label" for="message">Your question</label>
			<textarea class="form-control" id="message" name="message" rows="3" placeholder="Ask about sales, trends, etc."><?= htmlspecialchars($message); ?></textarea>
		</div>
		<div class="form-check mb-2">
			<input class="form-check-input" type="checkbox" id="include_context" name="include_context" checked>
			<label class="form-check-label" for="include_context">Include forecast context</label>
		</div>
		<div>
			<button class="btn btn-primary">Send</button>
		</div>
	</form>
	<?php if ($error): ?>
	<div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
	<?php endif; ?>
	<?php if ($answer): ?>
	<div class="card p-3">
		<div class="small text-muted mb-1">Answer</div>
		<div><?= nl2br(htmlspecialchars($answer)); ?></div>
	</div>
	<?php endif; ?>
</div>
</body>
</html>
