<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Product\Entities\UrlRedirect;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class RedirectController
{
    use HasCrudActions;

    protected $model = UrlRedirect::class;
    protected $label = 'product::redirects.redirect';
    protected $viewPath = 'product::admin.redirects';
    protected $routePrefix = 'admin.redirects';

    public function status($id): Response|JsonResponse|RedirectResponse
    {
        $redirect = UrlRedirect::findOrFail($id);

        $redirect->update([
            'is_active' => request('is_active') ? 1 : 0,
        ]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true], 200);
        }

        return redirect()->route('admin.redirects.index')
            ->withSuccess(trans('admin::messages.resource_updated', ['resource' => 'redirect']));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'source_path' => 'required|string',
                'status_code' => 'nullable|in:301,302',
            ]);

            $data = $this->prepareData($request);

            if ($this->isLoop($data['source_path'], $data['target_url'])) {
                throw new \RuntimeException('Geçersiz yönlendirme: döngü tespit edildi (A↔B).');
            }

            $source = $data['source_path'];
            UrlRedirect::updateOrCreate(
                ['source_path' => $source],
                $data
            );

            if ($request->wantsJson()) {
                return response()->json(['success' => true], 200);
            }

            return redirect()->route('admin.redirects.index')
                ->withSuccess(trans('admin::messages.resource_created', ['resource' => 'redirect']));
        } catch (\Throwable $e) {
            report($e);

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function update($id, Request $request)
    {
        try {
            $request->validate([
                'source_path' => 'required|string',
                'status_code' => 'nullable|in:301,302',
            ]);

            $redirect = UrlRedirect::findOrFail($id);

            $data = $this->prepareData($request);

            if ($this->isLoop($data['source_path'], $data['target_url'], $redirect->id)) {
                throw new \RuntimeException('Geçersiz yönlendirme: döngü tespit edildi (A↔B).');
            }

            $redirect->update($data);

            if ($request->wantsJson()) {
                return response()->json(['success' => true], 200);
            }

            return redirect()->route('admin.redirects.index')
                ->withSuccess(trans('admin::messages.resource_updated', ['resource' => 'redirect']));
        } catch (\Throwable $e) {
            report($e);

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    private function prepareData(Request $request): array
    {
        $source = (string) $request->input('source_path', '');

        $parsed = is_string($source) ? parse_url($source, PHP_URL_PATH) : null;
        $source = $parsed ?: '/';
        $source = '/' . ltrim($source, '/');

        $segments = explode('/', ltrim($source, '/'));
        $supportedLocales = array_keys(LaravelLocalization::getSupportedLocales());
        if (!empty($segments) && in_array($segments[0], $supportedLocales)) {
            array_shift($segments);
            $source = '/' . implode('/', $segments);
        }

        $statusCode = (int) ($request->input('status_code') ?? 301);
        $statusCode = in_array($statusCode, [301, 302]) ? $statusCode : 301;

        $target = (string) ($request->input('target_url') ?? '');
        $targetParsed = $target ? parse_url($target, PHP_URL_PATH) : null;
        if ($targetParsed !== null) {
            $target = '/' . ltrim($targetParsed, '/');
        }

        $isActive = $request->has('is_active') ? (bool) $request->input('is_active') : false;

        $inferredType = ($target === '/' || $target === '') ? 'home' : 'custom';

        return [
            'source_path' => $source,
            'target_type' => $request->input('target_type', $inferredType),
            'target_id' => $request->input('target_id'),
            'target_url' => $target ?: '/',
            'status_code' => $statusCode,
            'is_active' => $isActive,
        ];
    }

    private function isLoop(string $source, ?string $target, ?int $excludeId = null): bool
    {
        if (!$target) return false;
        if ($source === $target) return true;

        $query = UrlRedirect::query()
            ->where('source_path', $target)
            ->where('is_active', true);

        if ($excludeId) $query->where('id', '!=', $excludeId);

        $other = $query->first();
        return $other && ($other->target_url === $source) && (bool)$other->is_active;
    }
}
