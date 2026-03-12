<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaintenanceController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:500',
        ]);

        $currentStatus = $this->getStatus();
        $newEnabled = !$currentStatus['enabled'];

        $data = [
            'enabled' => $newEnabled,
            'message' => $newEnabled
                ? ($request->input('message', 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.'))
                : ($currentStatus['message'] ?? ''),
            'updated_at' => now()->toDateTimeString(),
            'updated_by' => auth()->user()->name ?? 'admin',
        ];

        Storage::disk('local')->put('maintenance.json', json_encode($data, JSON_PRETTY_PRINT));

        return redirect()->back()->with(
            'success',
            $newEnabled
                ? 'Mode maintenance AKTIF. Frontend tidak bisa diakses oleh user.'
                : 'Mode maintenance NONAKTIF. Frontend kembali normal.'
        );
    }

    public function status()
    {
        return response()->json($this->getStatus());
    }

    protected function getStatus(): array
    {
        if (!Storage::disk('local')->exists('maintenance.json')) {
            return ['enabled' => false, 'message' => '', 'updated_at' => null, 'updated_by' => null];
        }

        $data = json_decode(Storage::disk('local')->get('maintenance.json'), true);

        return [
            'enabled' => $data['enabled'] ?? false,
            'message' => $data['message'] ?? '',
            'updated_at' => $data['updated_at'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
        ];
    }
}
