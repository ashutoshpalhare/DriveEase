<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';

$user = require_role('admin');
$errors = [];
$old = [];

if (is_post()) {
    csrf_check();
    [$errors, $old] = validate($_POST, [
        'driver_name' => 'required|min:2|max:100',
        'dl_number' => 'required|min:5|max:40',
        'driver_phone' => 'required|phone',
        'driver_address' => 'required|min:5|max:255',
        'driver_gender' => 'required|in:Male,Female,Other',
    ]);

    if (!$errors && db_value('SELECT COUNT(*) FROM driver WHERE dl_number = ?', [strtoupper($old['dl_number'])])) {
        $errors['dl_number'] = 'This driving licence number is already registered.';
    }

    if (!$errors) {
        db_exec(
            'INSERT INTO driver (driver_name, dl_number, driver_phone, driver_address, driver_gender, client_username, driver_availability, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)',
            [
                trim($old['driver_name']),
                strtoupper(trim($old['dl_number'])),
                trim($old['driver_phone']),
                trim($old['driver_address']),
                $old['driver_gender'],
                $user['id'],
                'yes',
            ]
        );
        flash('success', 'Driver added to your fleet.');
        redirect(url('enterdriver.php'));
    }
}

$drivers = db_all(
    'SELECT driver_name, dl_number, driver_phone, driver_address, driver_gender, driver_availability FROM driver WHERE client_username = ? ORDER BY driver_name',
    [$user['id']]
);

render_header('Driver management', 'drivers');
?>
<div class="container py-4">
  <div class="mb-4">
    <p class="text-uppercase small text-secondary fw-semibold mb-1">Fleet operations</p>
    <h1 class="h2 mb-1">Driver management</h1>
    <p class="text-secondary mb-0">Add drivers and manage your fleet roster.</p>
  </div>

  <?php if ($errors): ?>
    <div class="alert alert-danger" role="alert">
      <strong>Please correct the following:</strong>
      <ul class="mb-0 mt-2">
        <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-5">
      <section class="card border-0 shadow-sm">
        <div class="card-body p-4">
          <h2 class="h5 mb-3">Add a driver</h2>
          <form method="post" action="<?= e(url('enterdriver.php')) ?>" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
              <label class="form-label" for="driver_name">Full name</label>
              <input class="form-control" id="driver_name" name="driver_name" maxlength="100" required value="<?= e($old['driver_name'] ?? '') ?>">
              <?php if (isset($errors['driver_name'])): ?><div class="form-text text-danger"><?= e($errors['driver_name']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
              <label class="form-label" for="dl_number">Driving licence number</label>
              <input class="form-control text-uppercase" id="dl_number" name="dl_number" maxlength="40" required value="<?= e($old['dl_number'] ?? '') ?>">
              <?php if (isset($errors['dl_number'])): ?><div class="form-text text-danger"><?= e($errors['dl_number']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
              <label class="form-label" for="driver_phone">Phone</label>
              <input class="form-control" id="driver_phone" name="driver_phone" type="tel" maxlength="20" required value="<?= e($old['driver_phone'] ?? '') ?>">
              <?php if (isset($errors['driver_phone'])): ?><div class="form-text text-danger"><?= e($errors['driver_phone']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
              <label class="form-label" for="driver_address">Address</label>
              <textarea class="form-control" id="driver_address" name="driver_address" rows="3" maxlength="255" required><?= e($old['driver_address'] ?? '') ?></textarea>
              <?php if (isset($errors['driver_address'])): ?><div class="form-text text-danger"><?= e($errors['driver_address']) ?></div><?php endif; ?>
            </div>
            <div class="mb-4">
              <label class="form-label" for="driver_gender">Gender</label>
              <select class="form-select" id="driver_gender" name="driver_gender" required>
                <option value="">Select gender</option>
                <?php foreach (['Male', 'Female', 'Other'] as $gender): ?>
                  <option value="<?= e($gender) ?>" <?= ($old['driver_gender'] ?? '') === $gender ? 'selected' : '' ?>><?= e($gender) ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['driver_gender'])): ?><div class="form-text text-danger"><?= e($errors['driver_gender']) ?></div><?php endif; ?>
            </div>
            <button class="btn btn-brand w-100" type="submit"><i class="fa-solid fa-user-plus me-2"></i>Add driver</button>
          </form>
        </div>
      </section>
    </div>

    <div class="col-lg-7">
      <section class="card border-0 shadow-sm">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h5 mb-0">Your drivers</h2>
            <span class="badge text-bg-light"><?= count($drivers) ?> total</span>
          </div>
          <?php if (!$drivers): ?>
            <div class="text-center py-5">
              <i class="fa-solid fa-id-card fa-2x text-secondary mb-3"></i>
              <h3 class="h6">No drivers added yet</h3>
              <p class="text-secondary small mb-0">Add your first driver using the form.</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table align-middle">
                <thead><tr><th>Name / licence</th><th>Contact</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($drivers as $driver): ?>
                  <tr>
                    <td>
                      <div class="fw-semibold"><?= e($driver['driver_name']) ?></div>
                      <div class="small text-secondary"><?= e($driver['dl_number']) ?></div>
                      <div class="small text-secondary"><?= e($driver['driver_gender']) ?></div>
                    </td>
                    <td>
                      <div><?= e($driver['driver_phone']) ?></div>
                      <div class="small text-secondary"><?= e($driver['driver_address']) ?></div>
                    </td>
                    <td><span class="badge <?= ($driver['driver_availability'] ?? '') === 'yes' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e(ucfirst((string) ($driver['driver_availability'] ?? 'unknown'))) ?></span></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>
</div>
<?php render_footer(); ?>
