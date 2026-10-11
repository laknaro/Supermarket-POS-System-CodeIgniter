<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Friendly message when MySQL is not running (replaces the old config/db.php error page).
 */
class DbCheckFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        try {
            $db = \Config\Database::connect();
            $db->initialize();
            if (! $db->connID) {
                throw new \RuntimeException('No connection');
            }
        } catch (\Throwable $e) {
            $msg = 'Database connection unavailable. Please start MySQL service in XAMPP.';

            $wantsJson = $request->isAJAX()
                || str_contains($request->getHeaderLine('Accept'), 'application/json')
                || str_starts_with(trim($request->getUri()->getPath(), '/'), 'api/');

            if ($wantsJson) {
                return service('response')->setStatusCode(503)->setJSON(['status' => 'error', 'message' => $msg]);
            }

            $dbName = esc((string) env('database.default.database'));
            $host   = esc((string) env('database.default.hostname'));

            return service('response')->setStatusCode(503)->setBody(
                '<div style="font-family:sans-serif;padding:2rem;background:#fee2e2;color:#991b1b;border:1px solid #f87171;border-radius:8px;max-width:600px;margin:40px auto;">'
                . '<h2 style="margin-top:0;">Database Connection Error</h2>'
                . '<p>Unable to connect to MySQL database <strong>' . $dbName . '</strong> on host <strong>' . $host . '</strong>.</p>'
                . '<p><small>Check that Apache &amp; MySQL are running in XAMPP and the credentials in <code>.env</code> are correct.</small></p></div>'
            );
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
