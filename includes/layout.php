<?php
declare(strict_types=1);

function nav_links(): array
{
    $u = current_user();
    $links = [
        'home' => ['Home', 'index.php', 'fa-house'],
        'cars' => ['Cars', 'cars.php', 'fa-car'],
        'faq'  => ['FAQ', 'faq.php', 'fa-circle-question'],
    ];
    // Role pages below point at existing pages until their rebuilt versions land.
    if ($u && $u['role'] === 'customer') {
        $links['trips']   = ['Active rentals', 'prereturncar.php', 'fa-key'];
        $links['history'] = ['History', 'mybookings.php', 'fa-clock-rotate-left'];
    } elseif ($u && $u['role'] === 'admin') {
        $links['fleet']    = ['Fleet', 'entercar.php', 'fa-warehouse'];
        $links['drivers']  = ['Drivers', 'enterdriver.php', 'fa-id-card'];
        $links['rentals']  = ['Rentals', 'clientview.php', 'fa-receipt'];
    }
    return $links;
}

function render_header(string $title, string $active = ''): void
{
    $u = current_user();
    $app = (string) config('APP_NAME', 'DriveEase');
    ?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> · <?= e($app) ?></title>
  <meta name="description" content="<?= e($app) ?> - book self-drive and chauffeur cars online.">
  <link rel="icon" href="<?= e(asset('img/P.png')) ?>">
  <link rel="stylesheet" href="<?= e(asset('vendor/bootstrap/bootstrap.min.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('vendor/fontawesome/css/all.min.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('vendor/sweetalert2/sweetalert2.min.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/driveease.css')) ?>">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-brand sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= e(url('index.php')) ?>"><i class="fa-solid fa-car-side me-2"></i><?= e($app) ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php foreach (nav_links() as $key => [$label, $href, $icon]): ?>
          <li class="nav-item">
            <a class="nav-link<?= $active === $key ? ' active' : '' ?>" href="<?= e(url($href)) ?>"><i class="fa-solid <?= e($icon) ?> me-1"></i><?= e($label) ?></a>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="d-flex align-items-center gap-2">
        <?php if ($u): ?>
          <span class="text-white-50 small d-none d-lg-inline"><i class="fa-solid fa-user me-1"></i><?= e($u['name']) ?> <span class="badge text-bg-light text-uppercase"><?= e($u['role']) ?></span></span>
          <form method="post" action="<?= e(url('logout.php')) ?>" class="m-0">
            <?= csrf_field() ?>
            <button class="btn btn-outline-light btn-sm"><i class="fa-solid fa-right-from-bracket me-1"></i>Logout</button>
          </form>
        <?php else: ?>
          <a class="btn btn-outline-light btn-sm" href="<?= e(url('login.php')) ?>">Log in</a>
          <a class="btn btn-warning btn-sm fw-semibold" href="<?= e(url('register.php')) ?>">Sign up</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<main class="flex-grow-1">
<?php
}

function render_footer(): void
{
    $app = (string) config('APP_NAME', 'DriveEase');
    $flash = flash_pull();
    ?>
</main>
<footer class="bg-dark text-white-50 py-4 mt-5">
  <div class="container d-flex flex-column flex-md-row justify-content-between gap-2 small">
    <div><i class="fa-solid fa-car-side me-1"></i><strong class="text-white"><?= e($app) ?></strong> · Car Rental Management System</div>
    <div>&copy; <?= date('Y') ?> <?= e($app) ?>. All rights reserved.</div>
  </div>
</footer>
<script src="<?= e(asset('vendor/bootstrap/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(asset('vendor/sweetalert2/sweetalert2.all.min.js')) ?>"></script>
<script>window.DE_FLASH = <?= json_encode($flash, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
<?php
}

/** Render a Bootstrap invalid-feedback line for a field. */
function field_error(array $errors, string $field): string
{
    return isset($errors[$field]) ? '<div class="invalid-feedback d-block">' . e($errors[$field]) . '</div>' : '';
}

function car_image(?string $path): string
{
    return $path ? url($path) : asset('img/bg-2.jpg');
}
