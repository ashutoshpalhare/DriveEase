<?php
declare(strict_types=1);

/**
 * Fast, database-free regression checks for critical rental lifecycle safeguards.
 * Run with: php tests/lifecycle_static_test.php
 */
$passed = 0;
$failed = 0;

function check(string $label, bool $condition): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        fwrite(STDOUT, "PASS {$label}\n");
    } else {
        $failed++;
        fwrite(STDERR, "FAIL {$label}\n");
    }
}

function source(string $path): string
{
    $fullPath = dirname(__DIR__) . '/' . $path;
    if (!is_file($fullPath)) {
        throw new RuntimeException("Required source file missing: {$path}");
    }
    return (string) file_get_contents($fullPath);
}

function contains_all(string $haystack, array $needles): bool
{
    foreach ($needles as $needle) {
        if (!str_contains($haystack, $needle)) {
            return false;
        }
    }
    return true;
}

$migration = source('database/migrate.php');
$booking = source('public/bookingconfirm.php');
$cancellation = source('public/cancelbooking.php');
$returnForm = source('public/returncar.php');
$returnHandler = source('public/printbill.php');
$adminRentals = source('public/admin-rentals.php');
$vehicles = source('public/entercar.php');
$drivers = source('public/enterdriver.php');

fwrite(STDOUT, "Migration compatibility\n");
check('booking cancellation columns are migrated', contains_all($migration, ["'cancelled_at'", "'cancel_reason'"]));
check('vehicle and driver active flags are migrated', contains_all($migration, ["add_col('cars', 'is_active'", "add_col('driver', 'is_active'"]));
check('payment ledger is created by migration', str_contains($migration, 'CREATE TABLE IF NOT EXISTS payments'));

fwrite(STDOUT, "\nBooking safeguards\n");
check('booking submission is POST-only and CSRF-protected', contains_all($booking, ['if (!is_post())', 'csrf_check();']));
check('inactive cars and drivers cannot be booked', contains_all($booking, ['c.is_active = 1', 'd.is_active = 1']));
check('booking rejects overlapping blocking statuses', str_contains($booking, 'BLOCKING_STATUSES'));
check('cancellation is POST-only and CSRF-protected', contains_all($cancellation, ['if (!is_post())', 'csrf_check();']));
check('cancellation checks customer ownership and confirmed status', contains_all($cancellation, ['customer_username', 'confirmed']));

fwrite(STDOUT, "\nReturn and payment safeguards\n");
check('return form requires an active, unreturned trip', contains_all($returnForm, ["booking_status'] !== 'active'", "return_status'] !== 'NR'"]));
check('return handler locks the booking and verifies customer ownership', contains_all($returnHandler, ['FOR UPDATE', 'customer_username', 'csrf_check();']));
check('return handler accepts only active trips', contains_all($returnHandler, ["booking_status'] !== 'active'", "return_status'] !== 'NR'"]));
check('payment recording is scoped to the fleet owner', contains_all($adminRentals, ['JOIN clientcars cc ON cc.car_id = r.car_id', "cc.client_username = ?"]));
check('payments require a completed, returned rental', contains_all($adminRentals, ["booking_status'] !== 'completed'", "return_status'] !== 'R'"]));
check('payments cannot exceed the outstanding balance', str_contains($adminRentals, '$amount > $outstanding'));
check('payment ledger and booking balance update together', contains_all($adminRentals, ['INSERT INTO payments', 'UPDATE rentedcars SET amount_paid = ?, payment_status = ?']));

fwrite(STDOUT, "\nFleet management safeguards\n");
check('vehicle creation is admin-only and CSRF-protected', contains_all($vehicles, ["require_role('admin')", 'csrf_check();']));
check('vehicle registration uses prepared duplicate check', contains_all($vehicles, ['car_nameplate = ?', 'db_exec(']));
check('driver creation is admin-only and CSRF-protected', contains_all($drivers, ["require_role('admin')", 'csrf_check();']));
check('driver listing is scoped to the fleet owner', str_contains($drivers, 'WHERE client_username = ?'));

fwrite(STDOUT, "\nResult: {$passed} passed, {$failed} failed\n");
exit($failed === 0 ? 0 : 1);
