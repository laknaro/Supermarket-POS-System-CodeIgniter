<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\DashboardModel;

class Dashboard extends BaseController
{
    public function index(): string
    {
        return $this->page('dashboard', (new DashboardModel())->all());
    }
}
