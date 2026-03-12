<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClinicStoreRequest;
use App\Http\Requests\ClinicUpdateRequest;
use App\Models\Clinic;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ClinicController extends Controller
{
    public function index()
    {
        $this->authorize('view', Clinic::class);

        return view('backend.clinics.view', [
            'clinics' => Clinic::orderBy('cl_order')->get()
        ]);
    }

    public function store(ClinicStoreRequest $request)
    {
        $this->authorize('create', Clinic::class);

        $validateData = $request->validated();
        $validateData['cl_umum'] = $request->has('cl_umum') ? $validateData['cl_umum'] : 0;
        $validateData['cl_bpjs'] = $request->has('cl_bpjs') ? $validateData['cl_bpjs'] : 0;

        do {
            $validateData['cl_ucode'] = Str::random(20);
            $ucodeCheck = Clinic::where('cl_ucode', $validateData['cl_ucode'])->exists();
        } while ($ucodeCheck);
        Clinic::create($validateData);

        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'CLINIC',
            'lo_message' => 'CREATE : ' . $validateData['cl_code'] . ' - ' . $validateData['cl_name']
        ]);

        return redirect()->route('clinics')->with('success', 'Data Klinik Berhasil Ditambahkan');
    }

    public function show(Clinic $clinic)
    {
        $this->authorize('edit', Clinic::class);

        $data = Clinic::where('cl_ucode', $clinic->cl_ucode)->first();

        return response()->json($data);
    }

    public function update(ClinicUpdateRequest $request, Clinic $clinic)
    {
        $this->authorize('edit', Clinic::class);

        $validateData = $request->validated();
        $validateData['cl_umum'] = $request->has('cl_umum') ? $validateData['cl_umum'] : 0;
        $validateData['cl_bpjs'] = $request->has('cl_bpjs') ? $validateData['cl_bpjs'] : 0;

        $duplicateCheck = Clinic::where('cl_order', $validateData['cl_order'])->first();
        if($duplicateCheck) {
            if($duplicateCheck['cl_ucode'] != $clinic['cl_ucode']) {
                $duplicateCheck->update([
                    'cl_order' => $clinic['cl_order']
                ]);
            }
        }
        $clinic->update($validateData);

        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'CLINIC',
            'lo_message' => 'UPDATE : ' . $validateData['cl_code'] . ' - ' . $validateData['cl_name']
        ]);

        return redirect()->route('clinics')->with('success', 'Data Klinik Berhasil Diubah');
    }

    public function destroy(Clinic $clinic)
    {
        $this->authorize('delete', Clinic::class);

        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'CLINIC',
            'lo_message' => 'DELETE : ' . $clinic->cl_code . ' - ' . $clinic->cl_name
        ]);

        $clinic->delete();

        return redirect()->route('clinics')->with('success', 'Data Klinik Berhasil Dihapus');
    }

    public function getLastOrder()
    {
        $this->authorize('create', Clinic::class);

        $data = Clinic::orderBy('cl_order', 'DESC')->first();

        return response()->json($data);
    }
}
