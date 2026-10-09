<?php
declare(strict_types=1);

/** Single shared mysqli connection. Exceptions on error; utf8mb4. */
function db(): mysqli
{
    static $conn = null;
    if ($conn === null) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli(
            (string) config('DB_HOST', '127.0.0.1'),
            (string) config('DB_USER', 'root'),
            (string) config('DB_PASS', ''),
            (string) config('DB_NAME', 'carrental'),
            (int) config('DB_PORT', 3306)
        );
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

/** Run a prepared statement; returns the mysqli_stmt (executed). */
function db_run(string $sql, array $params = []): mysqli_stmt
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params === [] ? null : array_values($params));
    return $stmt;
}

/** All rows as associative arrays. */
function db_all(string $sql, array $params = []): array
{
    return db_run($sql, $params)->get_result()->fetch_all(MYSQLI_ASSOC);
}

/** First row or null. */
function db_one(string $sql, array $params = []): ?array
{
    return db_all($sql, $params)[0] ?? null;
}

/** First column of first row (e.g. COUNT/SUM) or null. */
function db_value(string $sql, array $params = []): mixed
{
    $row = db_one($sql, $params);
    return $row ? reset($row) : null;
}

/** INSERT/UPDATE/DELETE; returns affected rows. */
function db_exec(string $sql, array $params = []): int
{
    $stmt = db_run($sql, $params);
    return $stmt->affected_rows;
}

function db_insert_id(): int
{
    return (int) db()->insert_id;
}

/** Wrap work in a transaction; rolls back and rethrows on failure. */
function db_transaction(callable $fn): mixed
{
    $c = db();
    $c->begin_transaction();
    try {
        $result = $fn();
        $c->commit();
        return $result;
    } catch (Throwable $e) {
        $c->rollback();
        throw $e;
    }
}
