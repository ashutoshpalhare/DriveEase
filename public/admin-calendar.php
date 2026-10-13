<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
$admin = require_role('admin');
render_header('Booking calendar', 'calendar');
?>
<div class="container py-4"><h1 class="h3">Booking calendar</h1></div>
<?php render_footer();