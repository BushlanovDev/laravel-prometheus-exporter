<?php

declare(strict_types=1);

namespace BushlanovDev\LaravelPrometheusExporter\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Prometheus\RenderTextFormat;

trait MetricsTrait
{
    /**
     * @param Request $request
     *
     * @return Response
     * @throws \Throwable
     */
    public function metrics(Request $request): Response
    {
        if (!$this->isAuthenticated($request)) {
            return $this->responseFactory->make(
                'Unauthorized.',
                Response::HTTP_UNAUTHORIZED,
                ['Content-Type' => RenderTextFormat::MIME_TYPE]
            );
        }

        return $this->responseFactory->make(
            (new RenderTextFormat())->render($this->prometheusExporter->export()),
            Response::HTTP_OK,
            ['Content-Type' => RenderTextFormat::MIME_TYPE],
        );
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isAuthenticated(Request $request): bool
    {
        if (env('APP_ENV') === 'testing' || !config('prometheus.metrics_route_auth.enabled')) {
            return true;
        }

        $username = config('prometheus.metrics_route_auth.basic_auth.username');
        $password = config('prometheus.metrics_route_auth.basic_auth.password');

        return (!empty($request->header('php-auth-user')) && $request->header('php-auth-user') === $username)
            && (!empty($request->header('php-auth-pw')) && $request->header('php-auth-pw') === $password);
    }
}
