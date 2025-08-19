<?php

namespace App\Http\Controllers;

use App\Http\Requests\QrCarolusStoreRequest;
use App\Http\Requests\QrCarolusUpdateRequest;
use App\Models\Log;
use App\Models\QrCarolus;
use App\Services\APIHeaderGenerator;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCarolusController extends Controller
{
    protected APIHeaderGenerator $apiHeaderGenerator;

    public function __construct(APIHeaderGenerator $apiHeaderGenerator)
    {
        $this->apiHeaderGenerator = $apiHeaderGenerator;
    }

    public function index()
    {
        return view('backend.carolus.view', [
            'qrcaroluses' => QrCarolus::orderBy('qrc_room')->get()
        ]);
    }

    public function store(QrCarolusStoreRequest $request)
    {
        $validateData = $request->validated();

        do {
            $validateData['qrc_ucode'] = Str::random(20);
            $ucodeCheck = QrCarolus::where('qrc_ucode', $validateData['qrc_ucode'])->exists();
        } while ($ucodeCheck);
        QrCarolus::create($validateData);

        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'QR CAROLUS',
            'lo_message' => 'CREATE : ' . $validateData['qrc_room']
        ]);

        return redirect()->route('qrcarolus')->with('success', 'Data QR Carolus Berhasil Ditambahkan');
    }

    public function show(QrCarolus $qrcarolus)
    {
        $data = QrCarolus::where('qrc_ucode', $qrcarolus->qrc_ucode)->first();

        return response()->json($data);
    }

    public function update(QrCarolusUpdateRequest $request, QrCarolus $qrcarolus)
    {
        $validateData = $request->validated();

        $qrcarolus->update($validateData);

        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'QR CAROLUS',
            'lo_message' => 'UPDATE : ' . $validateData['qrc_room']
        ]);

        return redirect()->route('qrcarolus')->with('success', 'Data QR Carolus Berhasil Diubah');
    }

    public function destroy(QrCarolus $qrcarolus)
    {
        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'QR CAROLUS',
            'lo_message' => 'UPDATE : ' . $qrcarolus->qrc_room
        ]);

        $qrcarolus->delete();

        return redirect()->route('qrcarolus')->with('success', 'Data QR Carolus Berhasil Dihapus');
    }

    public function getQrCode(QrCarolus $qrcarolus)
    {
        $data = QrCarolus::where('qrc_ucode', $qrcarolus->qrc_ucode)->first();
        $url = 'https://registrasi.rscahyakawaluyan.com/carolus/' . $data->qrc_room . '/' . $data->qrc_ucode . '/menu';

        $qrcode = Builder::create()->writer(new PngWriter())->data($url)->size(500)->margin(10)->build();
        $filename = '[' . Carbon::now()->format("Y-m-d") .'] QRCarolus Bed ' . $data->qrc_room . '.png';

        return response($qrcode->getString())->header('Content-Type', 'image/png')->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    protected function getQrData($qrc_room, $qrc_ucode)
    {
        return QrCarolus::where('qrc_room', $qrc_room)
            ->where('qrc_ucode', $qrc_ucode)
            ->where('qrc_active', true)
            ->first();
    }

    public function menu($qrc_room, $qrc_ucode)
    {
        $dataQr = $this->getQrData($qrc_room, $qrc_ucode);

        if($dataQr) {
            return view('backend.carolus.view-menu', [
                'qrc_room' => $qrc_room,
                'qrc_ucode' => $qrc_ucode,
            ]);
        } else {
            Log::create([
                'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
                'lo_user' => 'user',
                'lo_ip' => \Request::ip(),
                'lo_module' => 'QR CAROLUS',
                'lo_message' => 'ACCESS FAILED : Room ' . $qrc_room . ' - Access Wrong QR Code'
            ]);

            return view('backend.carolus.view-error', [
                'qrc_room' => $qrc_room,
                'qrc_ucode' => $qrc_ucode,
                'message' => 'Mohon Maaf Data Pasien Tidak Ditemukan. Silahkan Mencoba Scanning QR Code Lagi.'
            ]);
        }
    }

    public function hospitalInformation($qrc_room, $qrc_ucode)
    {
        $dataQr = $this->getQrData($qrc_room, $qrc_ucode);

        if($dataQr) {
            return redirect('https://linktr.ee/RSCahyaKawaluyan');
        } else {
            Log::create([
                'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
                'lo_user' => 'user',
                'lo_ip' => \Request::ip(),
                'lo_module' => 'QR CAROLUS',
                'lo_message' => 'ACCESS FAILED : Room ' . $qrc_room . ' - Access Wrong QR Code'
            ]);

            return view('backend.carolus.view-error', [
                'qrc_room' => $qrc_room,
                'qrc_ucode' => $qrc_ucode,
                'message' => 'Mohon Maaf Data Pasien Tidak Ditemukan. Silahkan Mencoba Scanning QR Code Lagi.'
            ]);
        }
    }

    public function billingForm($qrc_room, $qrc_ucode)
    {
        $dataQr = $this->getQrData($qrc_room, $qrc_ucode);

        if($dataQr) {
            return view('backend.carolus.view-login', [
                'qrc_room' => $qrc_room,
                'qrc_ucode' => $qrc_ucode,
            ]);
        } else {
            Log::create([
                'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
                'lo_user' => 'user',
                'lo_ip' => \Request::ip(),
                'lo_module' => 'QR CAROLUS',
                'lo_message' => 'ACCESS FAILED : Room ' . $qrc_room . ' - Access Wrong QR Code'
            ]);

            return view('backend.carolus.view-error', [
                'qrc_room' => $qrc_room,
                'qrc_ucode' => $qrc_ucode,
                'message' => 'Mohon Maaf Data Pasien Tidak Ditemukan. Silahkan Mencoba Scanning QR Code Lagi.'
            ]);
        }
    }

    public function billingCheck(Request $request, $qrc_room, $qrc_ucode)
    {
        $dataQr = $this->getQrData($qrc_room, $qrc_ucode);

        if($dataQr) {
            $request->validate([
                'password' => 'required',
            ], [
                'password.required' => 'Password tidak boleh kosong.',
            ]);

            if ($request->input('password') === $dataQr->qrc_password) {
                session()->put("billing_login_{$qrc_room}_{$qrc_ucode}", true);

                return redirect()->route('carolus.billing-information', [
                    'qrc_room' => $qrc_room,
                    'qrc_ucode' => $qrc_ucode
                ]);
            } else {
                return back()->withErrors(['password' => 'Password salah.']);
            }
        } else {
            Log::create([
                'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
                'lo_user' => 'user',
                'lo_ip' => \Request::ip(),
                'lo_module' => 'QR CAROLUS',
                'lo_message' => 'ACCESS FAILED : Room ' . $qrc_room . ' - Access Wrong QR Code'
            ]);

            return view('backend.carolus.view-error', [
                'qrc_room' => $qrc_room,
                'qrc_ucode' => $qrc_ucode,
                'message' => 'Mohon Maaf Data Pasien Tidak Ditemukan. Silahkan Mencoba Scanning QR Code Lagi.'
            ]);
        }
    }

    public function billingInformation($qrc_room, $qrc_ucode)
    {
        $isLoggedIn = session()->get("billing_login_{$qrc_room}_{$qrc_ucode}");
        if (!$isLoggedIn) {
            return redirect()->route('carolus.menu', [
                'qrc_room'  => $qrc_room,
                'qrc_ucode' => $qrc_ucode
            ])->withErrors(['access' => 'Silakan login terlebih dahulu.']);
        }

        $dataQr = $this->getQrData($qrc_room, $qrc_ucode);
        if (! $dataQr) {
            Log::create([
                'lo_time'   => Carbon::now()->format('Y-m-d H:i:s'),
                'lo_user'   => 'user',
                'lo_ip'     => request()->ip(),
                'lo_module' => 'QR CAROLUS',
                'lo_message'=> 'ACCESS FAILED : Room ' . $qrc_room . ' - Access Wrong QR Code'
            ]);
            return view('backend.carolus.view-error', [
                'qrc_room'  => $qrc_room,
                'qrc_ucode' => $qrc_ucode,
                'message'   => 'Mohon Maaf Data Pasien Tidak Ditemukan. Silahkan Mencoba Scanning QR Code Lagi.'
            ]);
        }

        $link    = env('API_KEY', 'rsck');
        $headers = $this->apiHeaderGenerator->generateApiHeader();

        $handlerStack = HandlerStack::create();
        $handlerStack->push(Middleware::retry(
            function ($retry, $request, $response, $exception) {
                return $retry < 3
                    && $exception instanceof RequestException
                    && $exception->getCode() === 28;
            },
            function ($retry) {
                return 1000 * pow(2, $retry);
            }
        ));

        try {
            $client = new Client([
                'handler' => $handlerStack,
                'verify'  => false
            ]);

            $queryParams = [
                'DepartmentID'             => 'Inpatient',
                'ServiceUnitCode'          => 'CAR001',
                'RoomCode'                 => $qrc_room,
                'isShowPaymentInformation' => 1,
            ];
            $response = $client->get(
                "https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/registration/base/information/detail2",
                [
                    'headers' => $headers,
                    'query'   => $queryParams,
                ]
            );

            if ($response->getStatusCode() != 200) {
                Log::create([
                    'lo_time'   => Carbon::now()->format('Y-m-d H:i:s'),
                    'lo_user'   => 'user',
                    'lo_ip'     => request()->ip(),
                    'lo_module' => 'QR CAROLUS',
                    'lo_message'=> 'ACCESS FAILED : Room ' . $qrc_room . ' - ' . $response->getStatusCode()
                ]);
                return view('backend.carolus.view-error', [
                    'qrc_room'  => $qrc_room,
                    'qrc_ucode' => $qrc_ucode,
                    'message'   => "Mohon Maaf Data Pasien Tidak Ditemukan. Silahkan Mencoba Scanning QR Code Lagi. [{$response->getStatusCode()}]"
                ]);
            }

            $apiBody = json_decode($response->getBody(), true);
            if (empty($apiBody['Data'])) {
                Log::create([
                    'lo_time'   => Carbon::now()->format('Y-m-d H:i:s'),
                    'lo_user'   => 'user',
                    'lo_ip'     => request()->ip(),
                    'lo_module' => 'QR CAROLUS',
                    'lo_message'=> 'ACCESS FAILED : Room ' . $qrc_room . ' - No Patient Found'
                ]);
                return view('backend.carolus.view-error', [
                    'qrc_room'  => $qrc_room,
                    'qrc_ucode' => $qrc_ucode,
                    'message'   => 'Mohon Maaf Data Pasien Tidak Ditemukan. Silahkan Mencoba Scanning QR Code Lagi.'
                ]);
            }

            // Data berhasil didapatkan:
            $dataField = json_decode($apiBody['Data'], true); // <-- array asosiatif

            Log::create([
                'lo_time'   => Carbon::now()->format('Y-m-d H:i:s'),
                'lo_user'   => 'user',
                'lo_ip'     => request()->ip(),
                'lo_module' => 'QR CAROLUS',
                'lo_message'=> 'ACCESS SUCCESS : ' . $qrc_room
            ]);
            $dataQr->increment('qrc_counter');

            // ───────────────────────────────────────────────────
            // 1) Siapkan satu record tagihan (billingRecord) agar bisa di-foreach di view:
            $billingRecord = $dataField;

            // Pastikan field penting ada (beri default jika kosong):
            $billingRecord['CustomerType']                      = $billingRecord['CustomerType']                      ?? '';
            $billingRecord['BusinessPartnerName']               = $billingRecord['BusinessPartnerName']               ?? '';
            $billingRecord['PaymentInformation']['TotalAmount'] = $billingRecord['PaymentInformation']['TotalAmount'] ?? 0;
            $billingRecord['PaymentInformation']['PaymentAmount']   = $billingRecord['PaymentInformation']['PaymentAmount']   ?? 0;
            $billingRecord['PaymentInformation']['RemainingAmount'] = $billingRecord['PaymentInformation']['RemainingAmount'] ?? 0;

            // ───────────────────────────────────────────────────
            // 2) Buat array billingData yang berisi satu elemen:
            $billingData = [ $billingRecord ];

            dd($billingData);

            // ───────────────────────────────────────────────────
            // 3) Return view dengan key 'billingData', supaya di Blade bisa di-@foreach:
            return view('backend.carolus.view-billing', [
                'qrc_room'    => $qrc_room,
                'qrc_ucode'   => $qrc_ucode,
                'billingData' => $billingData,
            ]);

        } catch (RequestException $e) {
            Log::create([
                'lo_time'   => Carbon::now()->format('Y-m-d H:i:s'),
                'lo_user'   => 'user',
                'lo_ip'     => request()->ip(),
                'lo_module' => 'QR CAROLUS',
                'lo_message'=> 'ACCESS FAILED : Room ' . $qrc_room . ' - [500]'
            ]);
            return view('backend.carolus.view-error', [
                'qrc_room'  => $qrc_room,
                'qrc_ucode' => $qrc_ucode,
                'message'   => 'Mohon Maaf Data Pasien Tidak Ditemukan. Silahkan Mencoba Scanning QR Code Lagi. [500]'
            ]);
        }
    }

    public function billingInformation2($qrc_room, $qrc_ucode)
    {
        $isLoggedIn = session()->get("billing_login_{$qrc_room}_{$qrc_ucode}");

        if (!$isLoggedIn) {
            return redirect()->route('carolus.menu', [
                'qrc_room' => $qrc_room,
                'qrc_ucode' => $qrc_ucode
            ])->withErrors(['access' => 'Silakan login terlebih dahulu.']);
        }

        $dataQr = $this->getQrData($qrc_room, $qrc_ucode);

        if($dataQr) {
            $responses = [];
            $link = env('API_KEY', 'rsck');
            $headers = $this->apiHeaderGenerator->generateApiHeader();

            $handlerStack = HandlerStack::create();
            $handlerStack->push(Middleware::retry(function ($retry, $request, $response, $exception) {
                return $retry < 3 && $exception instanceof RequestException && $exception->getCode() === 28;
            }, function ($retry) {
                return 1000 * pow(2, $retry);
            }));

            try {
                $client = new Client(['handler' => $handlerStack, 'verify' => false]);
                $queryParams = [
                    'DepartmentID' => 'Inpatient',
                    'ServiceUnitCode' => 'CAR001',
                    'RoomCode' => $qrc_room,
                    'isShowPaymentInformation' => 1,
                ];
                //https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/registration/base/information/detail2
                $response = $client->get("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/registration/base/information/detail2", [
                    'headers' => $headers,
                    'query' => $queryParams,
                ]);
                if ($response->getStatusCode() == 200) {
                    $data = json_decode($response->getBody(), true);
                    if (!empty($data['Data'])) {
                        $dataField = json_decode($data['Data'], true);

                        Log::create([
                            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
                            'lo_user' => 'user',
                            'lo_ip' => \Request::ip(),
                            'lo_module' => 'QR CAROLUS',
                            'lo_message' => 'ACCESS SUCCESS : ' . $qrc_room
                        ]);
                        $dataQr->increment('qrc_counter');

                        return view('backend.carolus.view-billing', [
                            'qrc_room' => $qrc_room,
                            'qrc_ucode' => $qrc_ucode,
                            'billingData' => $dataField,
                        ]);
                    } else {
                        Log::create([
                            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
                            'lo_user' => 'user',
                            'lo_ip' => \Request::ip(),
                            'lo_module' => 'QR CAROLUS',
                            'lo_message' => 'ACCESS FAILED : Room ' . $qrc_room . ' - No Patient Found'
                        ]);

                        return view('backend.carolus.view-error', [
                            'qrc_room' => $qrc_room,
                            'qrc_ucode' => $qrc_ucode,
                            'message' => 'Mohon Maaf Data Pasien Tidak Ditemukan. Silahkan Mencoba Scanning QR Code Lagi.'
                        ]);
                    }
                } else {
                    Log::create([
                        'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
                        'lo_user' => 'user',
                        'lo_ip' => \Request::ip(),
                        'lo_module' => 'QR CAROLUS',
                        'lo_message' => 'ACCESS FAILED : Room ' . $qrc_room . ' - ' . $response->getStatusCode()
                    ]);

                    return view('backend.carolus.view-error', [
                        'qrc_room' => $qrc_room,
                        'qrc_ucode' => $qrc_ucode,
                        'message' => 'Mohon Maaf Data Pasien Tidak Ditemukan. Silahkan Mencoba Scanning QR Code Lagi. [' . $response->getStatusCode() . ']'
                    ]);
                }
            } catch (RequestException $e) {
                Log::create([
                    'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
                    'lo_user' => 'user',
                    'lo_ip' => \Request::ip(),
                    'lo_module' => 'QR CAROLUS',
                    'lo_message' => 'ACCESS FAILED : Room ' . $qrc_room . ' - [500]'
                ]);

                return view('backend.carolus.view-error', [
                    'qrc_room' => $qrc_room,
                    'qrc_ucode' => $qrc_ucode,
                    'message' => 'Mohon Maaf Data Pasien Tidak Ditemukan. Silahkan Mencoba Scanning QR Code Lagi. [500]'
                ]);
            }
        } else {
            Log::create([
                'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
                'lo_user' => 'user',
                'lo_ip' => \Request::ip(),
                'lo_module' => 'QR CAROLUS',
                'lo_message' => 'ACCESS FAILED : Room ' . $qrc_room . ' - Access Wrong QR Code'
            ]);

            return view('backend.carolus.view-error', [
                'qrc_room' => $qrc_room,
                'qrc_ucode' => $qrc_ucode,
                'message' => 'Mohon Maaf Data Pasien Tidak Ditemukan. Silahkan Mencoba Scanning QR Code Lagi.'
            ]);
        }
    }

    public function billingLogout($qrc_room, $qrc_ucode)
    {
        session()->forget("billing_login_{$qrc_room}_{$qrc_ucode}");

        return redirect()->route('carolus.menu', [
            'qrc_room' => $qrc_room,
            'qrc_ucode' => $qrc_ucode
        ])->with('success', 'Berhasil logout.');
    }
}
