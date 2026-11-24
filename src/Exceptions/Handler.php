<?php

declare(strict_types=1);

namespace Air\Exceptions;

use Air\Application;
use Air\Http\Request;
use Air\Http\Response;
use Air\Log\Logger;
use Air\Session\SessionManager;
use Air\Validation\ValidationException;
use Throwable;

class Handler
{
    public function __construct(private Application $app)
    {
    }

    public function render(Request $request, Throwable $e): Response
    {
        if ($e instanceof ValidationException && !($request->expectsJson() || $request->isJson())) {
            if ($this->app->has(SessionManager::class)) {
                /** @var SessionManager $session */
                $session = $this->app->make(SessionManager::class);
                $session->flash('_errors', $e->errors());
                $session->flashInput($request->post());
            }
            $target = $request->header('referer', '/');
            return (new Response('', 302, ['Location' => $target]))->withStatus(302);
        }

        $status = $e instanceof HttpException ? $e->getStatusCode() : 500;
        $headers = $e instanceof HttpException ? $e->getHeaders() : [];
        $message = $e->getMessage() !== '' ? $e->getMessage() : 'Server Error';
        $debug = $this->app->isDebug();

        if ($status >= 500 && $this->app->has(Logger::class)) {
            /** @var Logger $logger */
            $logger = $this->app->make(Logger::class);
            $logger->error($message, ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        if ($request->expectsJson() || $request->isJson()) {
            $payload = [
                'error' => [
                    'status' => $status,
                    'message' => $debug || $status < 500 ? $message : 'Server Error',
                ],
            ];
            if ($debug) {
                $payload['error']['exception'] = get_class($e);
                $payload['error']['file'] = $e->getFile();
                $payload['error']['line'] = $e->getLine();
                $payload['error']['trace'] = explode("\n", $e->getTraceAsString());
            }

            return Response::json($payload, $status, $headers);
        }

        $title = $debug || $status < 500 ? $message : 'Server Error';
        $body = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . e((string) $status) . ' ' . e($title) . '</title>';
        $body .= '<style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;margin:2rem;color:#111}pre{background:#f5f5f5;padding:1rem;overflow:auto}</style></head><body>';
        $body .= '<h1>' . e((string) $status) . ' ' . e($title) . '</h1>';
        if ($debug) {
            $body .= '<p><strong>' . e(get_class($e)) . '</strong> in ' . e($e->getFile()) . ':' . e((string) $e->getLine()) . '</p>';
            $body .= '<pre>' . e($e->getTraceAsString()) . '</pre>';
        }
        $body .= '</body></html>';

        return Response::html($body, $status, $headers);
    }
}
