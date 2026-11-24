<?php

declare(strict_types=1);

namespace Air\Http;

abstract class Controller
{
    /** @param array<string,mixed> $data */
    protected function view(string $view, array $data = [], int $status = 200): Response
    {
        $response = view($view, $data);
        if ($status !== 200) {
            $response = new Response($response->content(), $status, $response->headers());
        }

        return $response;
    }

    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function redirect(string $to, int $status = 302): Response
    {
        return Response::redirect($to, $status);
    }
}
