<?php
declare(strict_types=1);

require_once __DIR__ . '/submission-receipt-support.php';

$form = isset($_GET['form']) && is_string($_GET['form']) ? $_GET['form'] : '';
$result = isset($_GET['result']) && is_string($_GET['result']) ? $_GET['result'] : '';

$messages = [
    'contact' => [
        'label' => 'Contact message',
        'success' => [
            'title' => 'Thanks for reaching out.',
            'message' => 'Your message has been received by the Teznet Technologies team. We appreciate you taking the time to tell us about your needs.',
            'status' => 'Message received',
            'steps' => [
                ['title' => 'Our team reviews your message', 'detail' => 'We’ll route your enquiry to the team best placed to help.'],
                ['title' => 'We’ll get back to you', 'detail' => 'Look out for a reply using the contact details you provided.'],
                ['title' => 'We’ll discuss the right next step', 'detail' => 'We can answer questions and talk through a solution that fits your goals.'],
            ],
            'link' => 'contact.html',
            'link_text' => 'Send another message',
        ],
        'error' => [
            'title' => 'Your message wasn’t sent.',
            'message' => 'We couldn’t save your message this time. Please check the required fields and try again.',
            'status' => 'Action needed',
            'steps' => [
                ['title' => 'Review your details', 'detail' => 'Make sure your email address and message are complete.'],
                ['title' => 'Try submitting again', 'detail' => 'Your message is only recorded after a successful submission.'],
            ],
            'link' => 'contact.html',
            'link_text' => 'Return to the contact form',
        ],
    ],
    'application' => [
        'label' => 'Teznet Hub application',
        'success' => [
            'title' => 'Your application is in.',
            'message' => 'Thank you for your interest in Teznet Hub. We’ve received your application and supporting documents.',
            'status' => 'Application received',
            'steps' => [
                ['title' => 'Our team reviews your application', 'detail' => 'We’ll consider the information and documents you provided.'],
                ['title' => 'We’ll be in touch if there’s a suitable next step', 'detail' => 'Please keep an eye on the email address and phone number in your application.'],
                ['title' => 'Keep your contact details available', 'detail' => 'If we need any clarification, we’ll use the details you supplied.'],
            ],
            'link' => 'opportunities.html',
            'link_text' => 'Explore Teznet Hub',
        ],
        'error' => [
            'title' => 'Your application wasn’t submitted.',
            'message' => 'We couldn’t complete the application. Check the required information and documents, then try again.',
            'status' => 'Action needed',
            'steps' => [
                ['title' => 'Check the required fields', 'detail' => 'Make sure your contact details and application answers are complete.'],
                ['title' => 'Review your documents', 'detail' => 'Confirm the required files are attached and meet the upload requirements.'],
            ],
            'link' => 'opportunities.html#application',
            'link_text' => 'Return to the application',
        ],
    ],
    'booking' => [
        'label' => 'Consultation request',
        'success' => [
            'title' => 'Your consultation request is received.',
            'message' => 'Thanks for your interest in working with Teznet. We’ve received your request and the time you selected.',
            'status' => 'Request received',
            'steps' => [
                ['title' => 'We’ll check the requested time', 'detail' => 'Our team will review availability for your selected date and time.'],
                ['title' => 'We’ll contact you to confirm', 'detail' => 'We’ll use the email or phone number you provided if we need to adjust the time.'],
                ['title' => 'We’ll discuss your project', 'detail' => 'Once confirmed, we can learn more about your needs and recommend next steps.'],
            ],
            'link' => 'booking.html',
            'link_text' => 'Make another booking',
        ],
        'error' => [
            'title' => 'Your consultation request wasn’t sent.',
            'message' => 'We couldn’t save your request this time. Please check the details and selected date and time, then try again.',
            'status' => 'Action needed',
            'steps' => [
                ['title' => 'Check your booking details', 'detail' => 'Make sure the date, time, time zone, and required contact fields are selected.'],
                ['title' => 'Submit the request again', 'detail' => 'Your consultation is not booked until our team contacts you to confirm.'],
            ],
            'link' => 'booking.html',
            'link_text' => 'Return to booking',
        ],
    ],
];

$bookingServices = [
    'Network design & installation',
    'Fiber optic & connectivity',
    'Wireless networks & Wi-Fi',
    'Network monitoring & IT support',
    'Network Security',
    'Web Development',
    'Software Development',
    'Android Development',
    'iOS Development',
    'Mobile App Maintenance',
    'App Store Optimization',
    'SEO Optimization',
    'Media Production',
    'Event Livestream',
    'Graphic Design & Creative Content',
    'Event photography',
    'Event videography',
    'Drone Aerial Footage',
    'Event Recording',
    'Posters & flyers',
    'Branding & Logo Design',
    'Others',
];

$submittedFields = [
    'contact' => [
        'network-design-installation' => 'Network design & installation',
        'fiber-connectivity' => 'Fiber optic & connectivity',
        'wireless-wifi' => 'Wireless networks & Wi-Fi',
        'network-security-operations' => 'Network security & managed operations',
        'monitoring-it-support' => 'Network monitoring & IT support',
        'website-app-development' => 'Website & application development',
        'school-management-system' => 'School management system',
        'mobile-ecommerce' => 'Mobile apps & e-commerce',
        'cloud-devops' => 'Cloud & DevOps',
        'digital-marketing-seo' => 'Digital marketing & SEO',
        'photography-video' => 'Photography & videography',
        'live-production-streaming' => 'Live production & streaming',
        'branding-design' => 'Branding & graphic design',
        'technology-training' => 'Technology training & mentorship',
        'internships-career-development' => 'Internships & career development',
        'general-enquiry' => 'General enquiry / not sure',
    ],
    'booking' => array_combine($bookingServices, $bookingServices),
    'application' => [
        'network-design-installation' => 'Teznet Networks — Network design & installation',
        'fiber-connectivity' => 'Teznet Networks — Fiber optic & connectivity',
        'wireless-wifi' => 'Teznet Networks — Wireless networks & Wi-Fi',
        'network-security-operations' => 'Teznet Networks — Network security & managed operations',
        'monitoring-it-support' => 'Teznet Networks — Network monitoring & IT support',
        'website-app-development' => 'Teznet Digital — Website & application development',
        'school-management-system' => 'Teznet Digital — School management system',
        'mobile-ecommerce' => 'Teznet Digital — Mobile apps & e-commerce',
        'cloud-devops' => 'Teznet Digital — Cloud & DevOps',
        'digital-marketing-seo' => 'Teznet Digital — Digital marketing & SEO',
        'photography-video' => 'Teznet Media — Photography & videography',
        'live-production-streaming' => 'Teznet Media — Live production & streaming',
        'branding-design' => 'Teznet Media — Branding & graphic design',
    ],
];

$validResult = isset($messages[$form][$result]);
$isSuccess = $validResult && $result === 'success';
$receiptToken = '';
if ($isSuccess && isset($_GET['receipt']) && is_string($_GET['receipt'])) {
    $receipt = get_submission_receipt($_GET['receipt']);
    if ($receipt !== null && $receipt['form'] === $form) {
        $receiptToken = $_GET['receipt'];
    }
}
$submittedField = '';
if ($isSuccess && isset($_GET['field']) && is_string($_GET['field'])) {
    $submittedField = $submittedFields[$form][$_GET['field']] ?? '';
}
$submittedFieldTitle = [
    'contact' => 'Service of interest',
    'booking' => 'Requested service',
    'application' => 'Area applied for',
][$form] ?? 'Selected field';
$page = $validResult
    ? array_merge(['label' => $messages[$form]['label']], $messages[$form][$result])
    : [
        'label' => 'Form submission',
        'title' => 'We couldn’t confirm your submission.',
        'message' => 'This confirmation link is incomplete or no longer valid. Return to the relevant form to check your submission status.',
        'status' => 'Status unavailable',
        'steps' => [
            ['title' => 'Return to the form', 'detail' => 'If you’re unsure whether your submission went through, contact our team before sending it again.'],
        ],
        'link' => 'contact.html',
        'link_text' => 'Contact Teznet',
    ];

http_response_code($validResult ? ($isSuccess ? 200 : 400) : 404);
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, max-age=0');

if ($form === 'application' && $result === 'error' && isset($_GET['reason']) && is_string($_GET['reason'])) {
    $reason = trim($_GET['reason']);
    if ($reason !== '' && strlen($reason) <= 500 && preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $reason) !== 1) {
        $page['message'] = $reason;
    }
}

function escape_html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= escape_html($page['title']) ?> | Teznet Technologies</title>
  <link rel="icon" type="image/png" href="Favicon/Favicon.png">
  <link rel="stylesheet" href="site-layout.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
  <style>
    :root {
      color-scheme: light;
      --status-ink: #0b1c30;
      --status-muted: #566273;
      --status-red: #8e0019;
      --status-line: #e5e9ef;
    }

    * { box-sizing: border-box; }

    body.status-page {
      min-height: 100vh;
      margin: 0;
      padding: 0;
      background:
        radial-gradient(ellipse at 86% 12%, rgba(210, 187, 255, 0.26), transparent 28rem),
        radial-gradient(ellipse at 8% 68%, rgba(242, 189, 69, 0.12), transparent 27rem),
        #f7f8fb;
      color: var(--status-ink);
      font: 16px/1.6 Inter, Arial, sans-serif;
    }

    .status-shell { width: min(calc(100% - 2.5rem), 55rem); margin: 0 auto; padding: 2rem 0; }
    .status-page .home-site-header__mark { overflow: hidden; border-color: #fff; border-radius: 50%; background: #fff; padding: 3px; }
    .status-page .home-site-header__mark img { display: block; width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }

    .status-card { overflow: hidden; border: 1px solid rgba(198, 198, 205, 0.55); border-radius: 1.4rem; background: #fff; box-shadow: 0 24px 70px rgba(11, 28, 48, 0.1); }
    .status-card__hero { position: relative; overflow: hidden; padding: clamp(2rem, 7vw, 3.75rem) clamp(1.4rem, 7vw, 4rem) 2rem; text-align: center; }
    .status-card__hero::before { position: absolute; top: -9rem; left: 50%; width: 28rem; height: 20rem; border-radius: 50%; background: <?= $isSuccess ? 'rgba(32, 192, 92, 0.08)' : 'rgba(142, 0, 25, 0.06)' ?>; content: ""; transform: translateX(-50%); }
    .status-icon { position: relative; display: grid; width: 4.5rem; height: 4.5rem; place-items: center; margin: 0 auto 1.25rem; border: 1px solid <?= $isSuccess ? '#ccebd8' : '#f1d6d7' ?>; border-radius: 50%; background: <?= $isSuccess ? '#effaf3' : '#fff4f3' ?>; color: <?= $isSuccess ? '#167343' : '#a12622' ?>; font-size: 2rem; font-weight: 700; }
    .status-eyebrow { position: relative; display: inline-flex; align-items: center; gap: 0.45rem; margin-bottom: 0.8rem; border: 1px solid <?= $isSuccess ? '#d2eadb' : '#efd9d9' ?>; border-radius: 999px; background: <?= $isSuccess ? '#f2faf5' : '#fff7f6' ?>; padding: 0.35rem 0.75rem; color: <?= $isSuccess ? '#246b43' : '#8b3131' ?>; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.025em; }
    .status-eyebrow__dot { width: 0.45rem; height: 0.45rem; border-radius: 50%; background: currentColor; }
    .status-card h1 { position: relative; max-width: 42rem; margin: 0 auto 0.85rem; color: var(--status-ink); font: 700 clamp(1.8rem, 5vw, 2.55rem)/1.16 Montserrat, Inter, sans-serif; letter-spacing: -0.035em; }
    .status-card__intro { position: relative; max-width: 39rem; margin: 0 auto; color: var(--status-muted); font-size: 1rem; line-height: 1.75; }

    .status-summary { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.8rem; margin: 0 clamp(1.4rem, 7vw, 4rem) 2rem; border: 1px solid var(--status-line); border-radius: 0.9rem; background: #fbfcfe; padding: 1rem 1.15rem; }
    .status-summary--with-field { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .status-summary__item { min-width: 0; }
    .status-summary__item + .status-summary__item { border-left: 1px solid var(--status-line); padding-left: 1.15rem; }
    .status-summary__label { display: block; margin-bottom: 0.2rem; color: #738092; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; }
    .status-summary__value { display: block; overflow-wrap: anywhere; color: var(--status-ink); font-size: 0.9rem; font-weight: 700; }
    .status-summary__value--success { color: #167343; }
    .status-summary__value--error { color: #a12622; }
    .status-summary__item--field { border: 1px solid #b7e5c7; border-radius: 0.75rem; background: #effaf3; padding: 0.75rem !important; }
    .status-summary__item + .status-summary__item--field { border-left: 1px solid #b7e5c7; padding-left: 0.75rem !important; }
    .status-summary__item--field .status-summary__label { color: #167343; }
    .status-summary__field-content { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.55rem; }
    .status-summary__item--field .status-summary__value { color: #0b1f3a; }
    .status-summary__receipt { display: inline-flex; min-height: 2rem; flex: 0 0 auto; align-items: center; justify-content: center; gap: 0.35rem; border: 1px solid #168447; border-radius: 0.5rem; background: #fff; padding: 0.35rem 0.55rem; color: #126b3a; font-size: 0.72rem; font-weight: 700; line-height: 1.2; text-decoration: none; transition: background-color 160ms ease, color 160ms ease; }
    .status-summary__receipt:hover { background: #168447; color: #fff; }
    .status-summary__receipt svg { width: 1rem; height: 1rem; flex: 0 0 auto; fill: currentColor; }

    .status-next { border-top: 1px solid #edf0f4; padding: 1.8rem clamp(1.4rem, 7vw, 4rem) 0; text-align: center; }
    .status-next h2 { margin: 0 0 1rem; color: var(--status-ink); font: 700 1.1rem/1.35 Montserrat, Inter, sans-serif; }
    .status-steps { display: grid; width: min(100%, 38rem); gap: 0; margin: 0 auto; padding: 0; list-style: none; text-align: left; }
    .status-step { position: relative; display: grid; grid-template-columns: 2rem minmax(0, 1fr); gap: 0.9rem; padding-bottom: 1.25rem; }
    .status-step:not(:last-child)::after { position: absolute; top: 2rem; bottom: 0.2rem; left: 0.96rem; width: 1px; background: var(--status-line); content: ""; }
    .status-step__number { z-index: 1; display: grid; width: 2rem; height: 2rem; place-items: center; border: 1px solid #e6d9dc; border-radius: 50%; background: #fff; color: var(--status-red); font-size: 0.75rem; font-weight: 700; }
    .status-step__title { display: block; margin: 0.1rem 0 0.18rem; color: var(--status-ink); font-size: 0.9rem; font-weight: 700; }
    .status-step__detail { margin: 0; color: var(--status-muted); font-size: 0.84rem; line-height: 1.6; }

    .status-help { width: fit-content; max-width: calc(100% - 2.8rem); margin: 0 auto 1.8rem; border: 1px solid #eee6d4; border-radius: 0.85rem; background: #fffaf0; padding: 1.1rem; }
    .status-help__heading { margin: 0 0 0.2rem; color: var(--status-ink); font: 700 0.95rem/1.4 Montserrat, Inter, sans-serif; }
    .status-help__intro { margin: 0 0 0.9rem; color: #5d5544; font-size: 0.82rem; line-height: 1.55; }
    .status-contact-grid { display: flex; flex-wrap: wrap; align-items: flex-start; gap: 0.5rem; }
    .status-contact-link { display: flex; width: fit-content; max-width: 100%; min-width: 0; flex: 0 1 auto; align-items: center; gap: 0.5rem; border: 1px solid transparent; border-radius: 0.65rem; padding: 0.55rem 0.6rem; color: #fff; text-decoration: none; box-shadow: 0 3px 8px rgba(11, 28, 48, 0.1); transition: background-color 160ms ease, box-shadow 160ms ease, transform 160ms ease; }
    .status-contact-link:hover { box-shadow: 0 6px 14px rgba(11, 28, 48, 0.16); transform: translateY(-2px); }
    .status-contact-link--call { background: #0b1f3a; }
    .status-contact-link--call:hover { background: #07162a; }
    .status-contact-link--email { background: #a4001d; }
    .status-contact-link--email:hover { background: #790016; }
    .status-contact-link--whatsapp { background: #20a85a; }
    .status-contact-link--whatsapp:hover { background: #168447; }
    .status-contact-link__icon { display: grid; width: 2rem; height: 2rem; flex: 0 0 auto; place-items: center; border: 1px solid rgba(255, 255, 255, 0.28); border-radius: 50%; background: rgba(255, 255, 255, 0.14); }
    .status-contact-link__icon svg { display: block; width: 1rem; height: 1rem; fill: currentColor; }
    .status-contact-link__copy { display: flex; min-width: 0; flex-direction: column; gap: 0.1rem; }
    .status-contact-link__label { color: rgba(255, 255, 255, 0.82); font-size: 0.62rem; font-weight: 700; letter-spacing: 0.07em; text-transform: uppercase; }
    .status-contact-link__value { overflow-wrap: anywhere; color: #fff; font-size: 0.72rem; font-weight: 700; line-height: 1.35; }
    .status-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.75rem; border-top: 1px solid #edf0f4; padding: 1.45rem clamp(1.4rem, 7vw, 4rem) 1.8rem; }
    .status-button { display: inline-flex; min-height: 2.9rem; align-items: center; justify-content: center; gap: 0.5rem; border: 1px solid #0b1f3a; border-radius: 0.65rem; background: #0b1f3a; padding: 0.72rem 1.1rem; color: #fff; font-size: 0.88rem; font-weight: 700; text-align: center; text-decoration: none; transition: background-color 160ms ease, transform 160ms ease; }
    .status-button:hover { background: #07162a; transform: translateY(-1px); }
    .status-button--secondary { border-color: #168447; background: #168447; color: #fff; }
    .status-button--secondary:hover { background: #106b39; }
    .status-button:focus-visible, .status-brand:focus-visible, .status-contact-link:focus-visible, .status-summary__receipt:focus-visible { outline: 3px solid #712ae2; outline-offset: 3px; }
    @media (max-width: 520px) {
      .status-shell { width: min(calc(100% - 1.7rem), 55rem); padding-top: 1.1rem; }
      .status-card { border-radius: 1rem; }
      .status-card__hero { padding-bottom: 1.55rem; }
      .status-summary { grid-template-columns: 1fr; gap: 0.55rem; padding: 0.85rem; }
      .status-summary__item + .status-summary__item { border-top: 1px solid var(--status-line); border-left: 0; padding-top: 0.55rem; padding-left: 0; }
      .status-summary__value { font-size: 0.8rem; }
      .status-contact-grid { flex-direction: column; }
      .status-help { max-width: calc(100% - 1.7rem); padding: 0.9rem; }
      .status-contact-link { gap: 0.55rem; padding: 0.55rem 0.65rem; }
      .status-actions { flex-direction: column; }
      .status-button { width: 100%; }
    }

    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { scroll-behavior: auto !important; transition-duration: 0.01ms !important; }
    }
  </style>
</head>
<body class="status-page">
  <header class="home-site-header">
    <div class="home-site-header__inner">
      <a class="home-site-header__brand" href="index.html" aria-label="Teznet Technologies home">
        <span class="home-site-header__mark" aria-hidden="true"><img src="Favicon/Favicon.png" alt=""></span>
        <span class="home-site-header__name">TEZNET<small>TECHNOLOGIES</small></span>
      </a>
      <button id="mobile-menu-button" class="home-site-header__toggle" type="button" aria-expanded="false" aria-controls="mobile-menu" aria-label="Toggle navigation">
        <span class="material-symbols-outlined text-[24px]">menu</span>
      </button>
      <nav class="home-site-header__nav" aria-label="Main navigation">
        <a href="about.html">About</a>
        <a href="networks.html">Networks</a>
        <a href="Digital.html">Digital</a>
        <a href="media.html">Media</a>
        <a href="opportunities.html">Teznet Hub</a>
        <a href="contact.html">Contact</a>
        <a href="booking.html">Book Now</a>
      </nav>
    </div>
    <div id="mobile-menu" class="home-site-header__mobile hidden">
      <a href="about.html">About</a>
      <a href="networks.html">Networks</a>
      <a href="Digital.html">Digital</a>
      <a href="media.html">Media</a>
      <a href="opportunities.html">Teznet Hub</a>
      <a href="contact.html">Contact</a>
      <a href="booking.html">Book Now</a>
    </div>
  </header>
  <div class="status-shell">
    <main class="status-card">
      <section class="status-card__hero" aria-labelledby="status-title">
        <div class="status-icon" aria-hidden="true"><?= $isSuccess ? '&#10003;' : '!' ?></div>
        <span class="status-eyebrow"><span class="status-eyebrow__dot" aria-hidden="true"></span><?= $isSuccess ? 'Successfully submitted' : 'Submission update' ?></span>
        <h1 id="status-title"><?= escape_html($page['title']) ?></h1>
        <p class="status-card__intro"><?= escape_html($page['message']) ?></p>
      </section>

      <div class="status-summary<?= $submittedField !== '' ? ' status-summary--with-field' : '' ?>" aria-label="Submission summary">
        <div class="status-summary__item">
          <span class="status-summary__label">Submission</span>
          <span class="status-summary__value"><?= escape_html($page['label']) ?></span>
        </div>
        <div class="status-summary__item">
          <span class="status-summary__label">Status</span>
          <span class="status-summary__value <?= $isSuccess ? 'status-summary__value--success' : 'status-summary__value--error' ?>"><?= escape_html($page['status']) ?></span>
        </div>
        <?php if ($submittedField !== ''): ?>
          <div class="status-summary__item status-summary__item--field">
            <span class="status-summary__label"><?= escape_html($submittedFieldTitle) ?></span>
            <div class="status-summary__field-content">
              <span class="status-summary__value"><?= escape_html($submittedField) ?></span>
              <?php if ($receiptToken !== ''): ?>
                <a class="status-summary__receipt" href="submission-receipt.php?token=<?= escape_html($receiptToken) ?>" aria-label="Download PDF receipt for this submission">
                  <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 20h14a1 1 0 0 0 1-1v-3h-2v2H6v-2H4v3a1 1 0 0 0 1 1Zm6-3h2V8h4l-5-5-5 5h4v9Z"/></svg>
                  <span>Download PDF receipt</span>
                </a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php elseif ($receiptToken !== ''): ?>
          <div class="status-summary__item">
            <span class="status-summary__label">Receipt</span>
            <a class="status-summary__receipt" href="submission-receipt.php?token=<?= escape_html($receiptToken) ?>" aria-label="Download PDF receipt for this submission">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 20h14a1 1 0 0 0 1-1v-3h-2v2H6v-2H4v3a1 1 0 0 0 1 1Zm6-3h2V8h4l-5-5-5 5h4v9Z"/></svg>
              <span>Download PDF receipt</span>
            </a>
        <?php endif; ?>
      </div>

      <section class="status-next" aria-labelledby="next-steps-title">
        <h2 id="next-steps-title"><?= $isSuccess ? 'What happens next' : 'What you can do next' ?></h2>
        <ol class="status-steps">
          <?php foreach ($page['steps'] as $index => $step): ?>
            <li class="status-step">
              <span class="status-step__number" aria-hidden="true"><?= $index + 1 ?></span>
              <div>
                <strong class="status-step__title"><?= escape_html($step['title']) ?></strong>
                <p class="status-step__detail"><?= escape_html($step['detail']) ?></p>
              </div>
            </li>
          <?php endforeach; ?>
        </ol>
      </section>

      <aside class="status-help" aria-label="Contact Teznet Technologies">
        <h2 class="status-help__heading">Need help or have a question?</h2>
        <p class="status-help__intro">Choose the easiest way to get in touch with our team.</p>
        <div class="status-contact-grid">
          <a class="status-contact-link status-contact-link--call" href="tel:+256781856006">
            <span class="status-contact-link__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.56 3.57.56.55 0 1 .45 1 1V20c0 .55-.45 1-1 1C10.61 21 3 13.39 3 4c0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.36.02.77-.25 1.03l-2.2 2.19Z"/></svg></span>
            <span class="status-contact-link__copy"><span class="status-contact-link__label">Call us</span><span class="status-contact-link__value">+256 (781) 856-006</span></span>
          </a>
          <a class="status-contact-link status-contact-link--email" href="mailto:fastnetworkug@gmail.com">
            <span class="status-contact-link__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 4H4a2 2 0 0 0-2 2v.4l10 6.25 10-6.25V6a2 2 0 0 0-2-2Zm2 4.76-9.47 5.92a1 1 0 0 1-1.06 0L2 8.76V18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8.76Z"/></svg></span>
            <span class="status-contact-link__copy"><span class="status-contact-link__label">Email</span><span class="status-contact-link__value">fastnetworkug@gmail.com</span></span>
          </a>
          <a class="status-contact-link status-contact-link--whatsapp" href="https://wa.me/256781856006" target="_blank" rel="noopener noreferrer">
            <span class="status-contact-link__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12.04 2a9.85 9.85 0 0 0-8.47 14.88L2 22l5.3-1.53A9.9 9.9 0 1 0 12.04 2Zm0 17.98a8.1 8.1 0 0 1-4.13-1.13l-.3-.18-3.15.91.92-3.07-.2-.32a8.1 8.1 0 1 1 6.86 3.79Zm4.45-6.07c-.24-.12-1.43-.7-1.65-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1.02-.38-1.94-1.2-.72-.64-1.2-1.44-1.34-1.68-.14-.24-.02-.37.1-.49.1-.1.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.2-.46-.4-.4-.54-.4h-.46c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.6 4.12 3.64.58.25 1.03.4 1.38.51.58.18 1.1.15 1.51.09.46-.07 1.43-.58 1.63-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28Z"/></svg></span>
            <span class="status-contact-link__copy"><span class="status-contact-link__label">WhatsApp</span><span class="status-contact-link__value">Chat with our team</span></span>
          </a>
        </div>
      </aside>

      <nav class="status-actions" aria-label="Next actions">
        <a class="status-button" href="<?= escape_html($page['link']) ?>"><?= escape_html($page['link_text']) ?> <span aria-hidden="true">→</span></a>
        <a class="status-button status-button--secondary" href="index.html">Back to home</a>
      </nav>
    </main>

  </div>
  <footer class="about-footer">
    <div class="about-footer__identity">
      <a class="about-footer__brand about-footer__brand--wordmark" href="index.html" aria-label="Teznet Technologies home"><span>TEZNET</span><small>Technologies</small></a>
      <p class="about-footer__description">Powering digital futures through integrated networks, media, and education.</p>
    </div>
    <nav class="about-footer__links" aria-label="Footer navigation">
      <a href="about.html">About Us</a>
      <a href="networks.html">Networks</a>
      <a href="Digital.html">Digital</a>
      <a href="media.html">Media</a>
      <a href="booking.html">Bookings</a>
      <a href="opportunities.html">Opportunities</a>
      <a href="contact.html">Contact</a>
      <a href="terms.html#privacy-data">Privacy Policy</a>
      <a href="terms.html">Terms of Service</a>
    </nav>
    <div class="about-footer__extras">
      <a class="about-footer__social-link" href="https://www.youtube.com/@teznetmedia" target="_blank" rel="noopener noreferrer" aria-label="Teznet Media on YouTube" title="Teznet Media on YouTube">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8ZM9.6 15.6V8.4L15.8 12l-6.2 3.6Z"/></svg>
      </a>
      <span class="about-footer__social-link about-footer__social-link--placeholder about-footer__social-link--facebook" role="img" aria-label="Facebook (link coming soon)" title="Facebook (link coming soon)">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M13.5 21v-8h2.7l.4-3h-3.1V8.1c0-.9.3-1.5 1.6-1.5h1.7V3.9c-.3 0-1.4-.1-2.6-.1-2.6 0-4.3 1.6-4.3 4.5V10H7v3h2.9v8h3.6Z"/></svg>
      </span>
      <span class="about-footer__social-link about-footer__social-link--placeholder about-footer__social-link--instagram" role="img" aria-label="Instagram (link coming soon)" title="Instagram (link coming soon)">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill-rule="evenodd" d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm5 3a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm5.5-3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z"/></svg>
      </span>
      <span class="about-footer__social-link about-footer__social-link--placeholder about-footer__social-link--linkedin" role="img" aria-label="LinkedIn (link coming soon)" title="LinkedIn (link coming soon)">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5.2 3a2.2 2.2 0 1 0 0 4.4 2.2 2.2 0 0 0 0-4.4ZM3.3 9h3.8v12H3.3V9Zm6.1 0H13v1.6h.1A4.1 4.1 0 0 1 16.8 8c4 0 4.7 2.6 4.7 6V21h-3.8v-6.2c0-1.5 0-3.4-2.1-3.4s-2.4 1.6-2.4 3.3V21H9.4V9Z"/></svg>
      </span>
      <span class="about-footer__social-link about-footer__social-link--placeholder about-footer__social-link--tiktok" role="img" aria-label="TikTok (link coming soon)" title="TikTok (link coming soon)">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19.6 6.2a5.8 5.8 0 0 1-3.8-1.4v9.1a6.3 6.3 0 1 1-5.5-6.2v3.4a2.9 2.9 0 1 0 2.1 2.8V2h3.4a5.8 5.8 0 0 0 3.8 3.8v.4Z"/></svg>
      </span>
      <span class="about-footer__social-link about-footer__social-link--placeholder about-footer__social-link--x" role="img" aria-label="X (link coming soon)" title="X (link coming soon)">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M18.9 2H22l-6.8 7.8L23.2 22h-6.3L12 14.6 5.5 22H2.4l7.3-8.4L1.9 2h6.5l4.5 6.8L18.9 2Zm-1.1 18h1.7L7.4 3.9H5.6L17.8 20Z"/></svg>
      </span>
    </div>
    <span>© <?= date('Y') ?> Teznet Technologies. All rights reserved.</span>
  </footer>
  <script src="site-layout.js?v=2" defer></script>
</body>
</html>
