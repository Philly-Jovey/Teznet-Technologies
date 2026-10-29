<?php
declare(strict_types=1);

$form = isset($_GET['form']) && is_string($_GET['form']) ? $_GET['form'] : '';
$result = isset($_GET['result']) && is_string($_GET['result']) ? $_GET['result'] : '';

$messages = [
    'contact' => [
        'success' => [
            'title' => 'Message received',
            'message' => 'Thank you for contacting Teznet Technologies. Our team will get back to you.',
            'link' => 'contact.html',
            'link_text' => 'Return to Contact',
        ],
        'error' => [
            'title' => 'Your message was not sent',
            'message' => 'We could not send your message. Please check your details and try again. If the problem continues, email fastnetworkug@gmail.com.',
            'link' => 'contact.html',
            'link_text' => 'Try Contact again',
        ],
    ],
    'application' => [
        'success' => [
            'title' => 'Application received',
            'message' => 'Thank you for applying to Teznet Technologies. Our team will review your application and contact you if there is a suitable next step.',
            'link' => 'opportunities.html',
            'link_text' => 'Return to Teznet Hub',
        ],
        'error' => [
            'title' => 'Your application was not submitted',
            'message' => 'We could not submit your application. Please review the required fields and documents, then try again. If the problem continues, email fastnetworkug@gmail.com.',
            'link' => 'opportunities.html#application',
            'link_text' => 'Try the application again',
        ],
    ],
    'booking' => [
        'success' => [
            'title' => 'Consultation request received',
            'message' => 'Thank you for your request. Our team will contact you to confirm a suitable date and time.',
            'link' => 'booking.html',
            'link_text' => 'Return to Booking',
        ],
        'error' => [
            'title' => 'Your request was not submitted',
            'message' => 'We could not submit your consultation request. Please check your details and try again. If the problem continues, contact us by email.',
            'link' => 'booking.html',
            'link_text' => 'Try Booking again',
        ],
    ],
];

$validResult = isset($messages[$form][$result]);
$page = $validResult ? $messages[$form][$result] : $messages['contact']['error'];
$isSuccess = $validResult && $result === 'success';
http_response_code($validResult ? ($isSuccess ? 200 : 400) : 404);
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, max-age=0');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= htmlspecialchars($page['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> | Teznet Technologies</title>
  <link rel="stylesheet" href="site-layout.css">
  <style>
    body { min-height: 100vh; display: grid; place-items: center; box-sizing: border-box; margin: 0; padding: 1.5rem; background: #f8f9ff; color: #0b1c30; font: 16px/1.6 Inter, Arial, sans-serif; }
    main { width: min(100%, 38rem); box-sizing: border-box; padding: clamp(1.5rem, 5vw, 3rem); border: 1px solid #dce9ff; border-radius: 1rem; background: #fff; box-shadow: 0 12px 36px rgba(11, 28, 48, 0.1); text-align: center; }
    .status-mark { display: grid; width: 3.5rem; height: 3.5rem; place-items: center; margin: 0 auto 1rem; border-radius: 50%; background: <?= $isSuccess ? '#e8f7ee' : '#fff0ef' ?>; color: <?= $isSuccess ? '#167343' : '#a12622' ?>; font-size: 1.75rem; font-weight: 700; }
    h1 { margin: 0 0 0.75rem; font-size: clamp(1.5rem, 5vw, 2rem); line-height: 1.2; }
    p { margin: 0 0 1.5rem; color: #455468; }
    a { display: inline-block; border-radius: 0.5rem; background: #0b1f3a; padding: 0.75rem 1.25rem; color: #fff; font-weight: 600; text-decoration: none; }
    a:focus-visible { outline: 3px solid #712ae2; outline-offset: 3px; }
  </style>
</head>
<body>
  <main>
    <div class="status-mark" aria-hidden="true"><?= $isSuccess ? '&#10003;' : '!' ?></div>
    <h1><?= htmlspecialchars($page['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars($page['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <a href="<?= htmlspecialchars($page['link'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= htmlspecialchars($page['link_text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
  </main>
</body>
</html>
