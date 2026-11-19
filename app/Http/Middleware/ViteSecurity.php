<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class ViteSecurity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Lewati CSP hanya untuk endpoint JSON/API
        if ($request->is('api/*') || $request->is('auth/*')) {
            return $next($request);
        }

        // Ensure Vite generates a CSP nonce
        Vite::useCspNonce();

        // Get the generated nonce value
        $nonce = Vite::cspNonce();

        // Define your CSP directives
        $cspDirectives = [
            'base-uri' => "'self'",
            'script-src' => "'self' 'unsafe-inline' 'nonce-$nonce' 'strict-dynamic' kit.fontawesome.com cdn.jsdelivr.net",
            'style-src' => "'self' 'unsafe-inline' fonts.googleapis.com cdn.jsdelivr.net kit.fontawesome.com fonts.bunny.net cdnjs.cloudflare.com",
            'connect-src' => "'self' ka-f.fontawesome.com https://api.iconify.design",
            // 'default-src' => "default-src 'self' fonts.gstatic.com cdn.jsdelivr.net",
            'form-action' => "'self'",
            'img-src' => "'self' data: blob:",
            'media-src' => "'self'",
            'object-src' => "'self'",
            'frame-src' => "'self'",
            'frame-ancestors' => "'self'",
            'font-src' => "'self' data: fonts.gstatic.com fonts.googleapis.com *.fontawesome.com cdn.jsdelivr.net fonts.bunny.net",
            'manifest-src' => "'self'",
            'worker-src' => "'self' blob:",
        ];

        // Compile CSP header string
        $cspHeader = collect($cspDirectives)->map(function ($directives, $directiveName) {
            return "$directiveName $directives";
        })->implode('; ');

        // Add the CSP header to the response
        $response = $next($request);
        $response->headers->set('Content-Security-Policy', $cspHeader);

        if (str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
            $content = $response->getContent();
            if ($content) {
                $contentWithNonce = $this->addNonceToInlineScripts($content, $nonce);
                $response->setContent($contentWithNonce);
            }
        }
        return $response;
    }

    protected function addNonceToInlineScripts($content, $nonce)
    {
        return preg_replace_callback('/<script(.*?)>(.*?)<\/script>/is', function ($match) use ($nonce) {
            $attributes = $match[1];
            $scriptContent = $match[2];

            if (!str_contains($attributes, 'nonce')) {
                $attributes .= " nonce=\"$nonce\"";
            }

            return "<script$attributes>$scriptContent</script>";
        }, $content);
    }
}