<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('user_id')) {
            return null;
        }

        if (str_starts_with(trim($request->getUri()->getPath(), '/'), 'api/')) {
            return service('response')->setStatusCode(401)->setJSON([
                'success' => false,
                'status'  => 'error',
                'message' => 'Session expired. Please log in again.',
            ]);
        }

        return redirect()->to('/');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
