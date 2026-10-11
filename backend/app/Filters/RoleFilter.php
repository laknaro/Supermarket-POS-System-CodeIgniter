<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Route usage: ['filter' => 'role:Admin,Manager'] */
class RoleFilter implements FilterInterface
{
    /** Landing page per role (same as the original login redirects). */
    public static function homeFor(string $role): string
    {
        if (strcasecmp($role, 'Cashier') === 0) {
            return 'pos.php';
        }
        if (strcasecmp($role, 'Inventory') === 0 || strcasecmp($role, 'Inventory Staff') === 0) {
            return 'products.php';
        }

        return 'dashboard.php';
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $role    = (string) session()->get('role');
        $allowed = array_map('trim', $arguments ?? []);

        if (in_array($role, $allowed, true)) {
            return null;
        }

        if (str_starts_with(trim($request->getUri()->getPath(), '/'), 'api/')) {
            return service('response')->setStatusCode(403)->setJSON([
                'success' => false,
                'status'  => 'error',
                'message' => 'You do not have permission to do this.',
            ]);
        }

        return redirect()->to(self::homeFor($role));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
