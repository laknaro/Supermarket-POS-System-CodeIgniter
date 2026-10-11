<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /** @var list<string> */
    protected $helpers = ['url', 'form'];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * Render one of the original frontend templates (frontend/views/*.php).
     * Supplies the variables the original pages and sidebar expect.
     */
    protected function page(string $view, array $data = []): string
    {
        $session = session();
        $role    = (string) $session->get('role');
        $isCash  = strcasecmp($role, 'Cashier') === 0;
        $isInv   = strcasecmp($role, 'Inventory') === 0 || strcasecmp($role, 'Inventory Staff') === 0;

        $shared = [
            'username'          => (string) ($session->get('username') ?? ''),
            'userRole'          => $role,
            'employeeId'        => (string) ($session->get('employee_id') ?? ''),
            'branch'            => (string) ($session->get('branch') ?? config('Store')->defaultBranch),
            'currentPage'       => basename($this->request->getUri()->getPath()),
            'userDisplay'       => (string) ($session->get('username') ?? ''),
            'roleDisplay'       => $role,
            'isCashier'         => $isCash,
            'isInventory'       => $isInv,
            'isAdminOrManager'  => in_array($role, ['Admin', 'Manager'], true),
            'lowStockBadgeCount' => (new \App\Models\ProductModel())->lowStockCount(),
            'message'           => (string) ($session->getFlashdata('message') ?? ''),
            'messageType'       => (string) ($session->getFlashdata('messageType') ?? ''),
        ];

        return view($view, array_merge($shared, $data));
    }

    /** Message shown by the original page banner on the next page load. */
    protected function flash(string $message, string $type = 'success'): void
    {
        session()->setFlashdata(['message' => $message, 'messageType' => $type]);
    }
}
