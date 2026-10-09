<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/cars.php';

$categories = car_categories();
$featured   = cars_search(['sort' => 'price_asc', 'per_page' => 6])['rows'];
$stats = [
    'cars'      => (int) db_value('SELECT COUNT(*) FROM cars WHERE is_active = 1'),
    'drivers'   => (int) db_value('SELECT COUNT(*) FROM driver WHERE is_active = 1'),
    'customers' => (int) db_value('SELECT COUNT(*) FROM customers'),
    'trips'     => (int) db_value("SELECT COUNT(*) FROM rentedcars WHERE booking_status = 'completed'"),
];
$icons = ['Hatchback' => 'fa-car-side', 'Sedan' => 'fa-car', 'SUV' => 'fa-truck-monster', 'MPV' => 'fa-van-shuttle'];
$today = date('Y-m-d');

render_header('Rent a car online', 'home');
?>
<section class="hero">
  <div class="container text-center text-lg-start">
    <div class="row"><div class="col-lg-8">
      <h1 class="display-5">Book the right car, with a driver, in minutes.</h1>
      <p class="lead text-white mb-0 hero-sub">Transparent day and per-km pricing, verified drivers and instant booking confirmation.</p>
    </div></div>
  </div>
</section>

<div class="container">
  <div class="card search-card"><div class="card-body p-3 p-md-4">
    <form action="<?= e(url('cars.php')) ?>" method="get" class="row g-3 align-items-end">
      <div class="col-6 col-md-3">
        <label class="form-label small fw-semibold" for="start">Pickup date</label>
        <input type="date" class="form-control" id="start" name="start" min="<?= e($today) ?>" value="<?= e($today) ?>" data-date-start="#end" required>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label small fw-semibold" for="end">Return date</label>
        <input type="date" class="form-control" id="end" name="end" min="<?= e($today) ?>" value="<?= e($today) ?>" required>
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label small fw-semibold" for="category">Car type</label>
        <select class="form-select" id="category" name="category">
          <option value="">All types</option>
          <?php foreach ($categories as $c): ?><option value="<?= e($c['category']) ?>"><?= e($c['category']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-3 d-grid">
        <button class="btn btn-warning fw-semibold" type="submit"><i class="fa-solid fa-magnifying-glass me-1"></i>Search cars</button>
      </div>
    </form>
  </div></div>
</div>

<section class="container py-5">
  <h2 class="h3 fw-bold mb-4">Browse by category</h2>
  <div class="row g-3">
    <?php foreach ($categories as $c): ?>
      <div class="col-6 col-md-3">
        <a class="card category-tile h-100 shadow-sm" href="<?= e(url('cars.php?category=' . urlencode($c['category']))) ?>">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="icon"><i class="fa-solid <?= e($icons[$c['category']] ?? 'fa-car') ?>"></i></div>
            <div><div class="fw-semibold"><?= e($c['category']) ?></div><div class="small text-muted"><?= (int) $c['n'] ?> cars</div></div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="container pb-4">
  <div class="d-flex justify-content-between align-items-end mb-3">
    <h2 class="h3 fw-bold m-0">Popular cars</h2>
    <a href="<?= e(url('cars.php')) ?>" class="fw-semibold text-decoration-none">View all <i class="fa-solid fa-arrow-right"></i></a>
  </div>
  <div class="row g-4">
    <?php foreach ($featured as $car) { include __DIR__ . '/../includes/car_card.php'; } ?>
  </div>
</section>

<section class="container py-5">
  <h2 class="h3 fw-bold text-center mb-4">How it works</h2>
  <div class="row g-4 text-center">
    <?php foreach ([['Search', 'Pick your dates and car type.', 'fa-magnifying-glass'], ['Book', 'Choose AC or non-AC and a driver.', 'fa-calendar-check'], ['Ride', 'Your driver arrives at pickup time.', 'fa-road'], ['Pay & return', 'Get a clear invoice on return.', 'fa-file-invoice']] as $i => [$t, $d, $ic]): ?>
      <div class="col-6 col-md-3">
        <div class="step-num mx-auto mb-3"><?= $i + 1 ?></div>
        <h3 class="h6 fw-bold"><i class="fa-solid <?= e($ic) ?> text-brand me-1"></i><?= e($t) ?></h3>
        <p class="small text-muted mb-0"><?= e($d) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="bg-brand text-white py-4">
  <div class="container"><div class="row text-center g-3">
    <?php foreach (['cars' => 'Cars', 'drivers' => 'Drivers', 'customers' => 'Customers', 'trips' => 'Trips completed'] as $k => $l): ?>
      <div class="col-6 col-md-3"><div class="display-6 fw-bold"><?= number_format($stats[$k]) ?></div><div class="small text-white-50"><?= e($l) ?></div></div>
    <?php endforeach; ?>
  </div></div>
</section>

<section class="container py-5" id="contact">
  <div class="row justify-content-center"><div class="col-lg-7">
    <h2 class="h3 fw-bold text-center mb-3">Questions or feedback?</h2>
    <form action="<?= e(url('contact.php')) ?>" method="post" class="card border-0 shadow-sm p-3 p-md-4" data-once>
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="c_name">Name</label><input class="form-control" id="c_name" name="name" maxlength="100" required></div>
        <div class="col-md-6"><label class="form-label" for="c_email">Email</label><input type="email" class="form-control" id="c_email" name="email" maxlength="100" required></div>
        <div class="col-12"><label class="form-label" for="c_msg">Message</label><textarea class="form-control" id="c_msg" name="message" rows="3" maxlength="1000" required></textarea></div>
        <div class="col-12 d-grid"><button class="btn btn-brand" type="submit"><i class="fa-solid fa-paper-plane me-1"></i>Send message</button></div>
      </div>
    </form>
  </div></div>
</section>
<?php render_footer();
