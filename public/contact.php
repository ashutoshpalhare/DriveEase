<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

if (!is_post()) { redirect(url('index.php#contact')); }
csrf_check();
[$errors, $c] = validate($_POST, ['name' => 'required|min:2|max:100', 'email' => 'required|email|max:100', 'message' => 'required|min:5|max:1000']);
if ($errors) {
    flash('danger', implode(' ', $errors));
} else {
    db_exec('INSERT INTO feedback (name, e_mail, message) VALUES (?, ?, ?)', [$c['name'], $c['email'], $c['message']]);
    flash('success', 'Thanks! Your message has been sent.');
}
redirect(url('index.php#contact'));
