<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

/** Business settings used by the backend. */
class Store extends BaseConfig
{
    /** VAT / NBT rate in percent. The POS screen shows 0%, so keep 0 unless you also change the frontend label. */
    public float $taxRatePercent = 0.0;

    /** Default branch name for new staff accounts. */
    public string $defaultBranch = 'Colombo – Head Office';
}
