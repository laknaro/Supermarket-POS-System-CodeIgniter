<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * The original frontend has no CSRF tokens. Instead of editing every form and
 * JavaScript file, the backend adds the token to the HTML it sends:
 *   1. a hidden input inside every <form method="POST">
 *   2. a tiny script that makes fetch() send the X-CSRF-TOKEN header on POST requests
 */
class CsrfInjectFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $type = strtolower($response->getHeaderLine('Content-Type'));
        if ($type !== '' && ! str_contains($type, 'html')) {
            return;   // JSON, CSV ...
        }

        $body = (string) $response->getBody();
        if ($body === '' || stripos($body, '<html') === false) {
            return;
        }

        $name = csrf_token();
        $hash = csrf_hash();

        $field = '<input type="hidden" name="' . esc($name, 'attr') . '" value="' . esc($hash, 'attr') . '">';
        $body  = preg_replace_callback(
            '/<form\b[^>]*\bmethod\s*=\s*(["\']?)post\1[^>]*>/i',
            static fn (array $m): string => $m[0] . $field,
            $body
        ) ?? $body;

        $shim = '<script>(function(){var T=' . json_encode($hash) . ',H=' . json_encode(config('Security')->headerName)
            . ',f=window.fetch;window.fetch=function(u,o){o=o||{};var m=String(o.method||"GET").toUpperCase();'
            . 'if(m!=="GET"&&m!=="HEAD"){var h=new Headers(o.headers||{});if(!h.has(H)){h.set(H,T);}o.headers=h;}'
            . 'return f.call(this,u,o);};})();</script>';

        $body = preg_replace('/<head\b[^>]*>/i', '$0' . $shim, $body, 1) ?? $body;

        $response->setBody($body);
    }
}
