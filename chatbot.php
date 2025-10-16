<?php
require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/php/ai/chatbot.php';

$answer = null;
$error = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    $include_context = isset($_POST['include_context']);
    
    if (!empty($message)) {
        $result = ai_chat($message, $include_context);
        if (!empty($result['error'])) {
            $error = $result['error'];
        } else {
            $answer = $result['answer'] ?? '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php render_head('AI Chatbot - SwiftMart'); ?>
    <style>
    .chat-container {
        max-height: 500px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        background-color: #f8f9fa;
    }

    .chat-message {
        margin-bottom: 1rem;
        padding: 0.75rem;
        border-radius: 0.375rem;
    }

    .user-message {
        background-color: #007bff;
        color: white;
        margin-left: 2rem;
    }

    .bot-message {
        background-color: white;
        border: 1px solid #dee2e6;
        margin-right: 2rem;
    }

    .chat-input-container {
        position: sticky;
        bottom: 0;
        background-color: white;
        border-top: 1px solid #dee2e6;
        padding: 1rem;
        margin-top: 1rem;
    }
    </style>
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-robot me-2"></i>AI Sales Assistant
                        </h4>
                        <small>Ask questions about sales trends, forecasts, and analytics</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="chat-container" id="chatContainer">
                            <?php if ($answer): ?>
                            <div class="chat-message user-message">
                                <strong>You:</strong> <?= htmlspecialchars($message) ?>
                            </div>
                            <div class="chat-message bot-message">
                                <strong>AI Assistant:</strong> <?= nl2br(htmlspecialchars($answer)) ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-chat-dots display-4 mb-3"></i>
                                <p>Welcome to SwiftMart AI Assistant!</p>
                                <p>Ask me about sales trends, forecasts, or any analytics questions.</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($error): ?>
                        <div class="alert alert-danger m-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                        <?php endif; ?>

                        <div class="chat-input-container">
                            <form method="post" class="row g-2">
                                <div class="col-12">
                                    <label for="message" class="form-label">Your Question</label>
                                    <textarea class="form-control" id="message" name="message" rows="3"
                                        placeholder="Ask about sales trends, forecasts, or analytics..."
                                        required><?= htmlspecialchars($message) ?></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="include_context"
                                            name="include_context"
                                            <?= isset($_POST['include_context']) ? 'checked' : 'checked' ?>>
                                        <label class="form-check-label" for="include_context">
                                            Include current sales forecast context
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-send me-2"></i>Send Message
                                    </button>
                                    <a href="<?= build_path('/chatbot.php') ?>" class="btn btn-outline-secondary ms-2">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Clear Chat
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-lightbulb me-2"></i>Suggested Questions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <button class="btn btn-outline-primary btn-sm w-100 mb-2"
                                    onclick="setQuestion('What are the current sales trends?')">
                                    What are the current sales trends?
                                </button>
                                <button class="btn btn-outline-primary btn-sm w-100 mb-2"
                                    onclick="setQuestion('How is the forecast looking for next month?')">
                                    How is the forecast looking for next month?
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-outline-primary btn-sm w-100 mb-2"
                                    onclick="setQuestion('Why are sales dropping?')">
                                    Why are sales dropping?
                                </button>
                                <button class="btn btn-outline-primary btn-sm w-100 mb-2"
                                    onclick="setQuestion('What should I focus on to improve sales?')">
                                    What should I focus on to improve sales?
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
    function setQuestion(question) {
        document.getElementById('message').value = question;
        document.getElementById('message').focus();
    }

    // Auto-scroll to bottom of chat
    document.addEventListener('DOMContentLoaded', function() {
        const chatContainer = document.getElementById('chatContainer');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    });
    </script>
</body>

</html>