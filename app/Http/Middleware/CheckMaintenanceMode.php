<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isMaintenanceMode()) {
            $data = $this->getMaintenanceData();
            return response()->view('errors.503', [
                'message' => $data['message'] ?? 'Sistem sedang dalam pemeliharaan.',
            ], 503);
        }

        return $next($request);
    }

    protected function isMaintenanceMode(): bool
    {
        if (!Storage::disk('local')->exists('maintenance.json')) {
            return false;
        }

        $data = json_decode(Storage::disk('local')->get('maintenance.json'), true);

        return isset($data['enabled']) && $data['enabled'] === true;
    }

    protected function getMaintenanceData(): array
    {
        if (!Storage::disk('local')->exists('maintenance.json')) {
            return [];
        }

        return json_decode(Storage::disk('local')->get('maintenance.json'), true) ?? [];
    }
}
