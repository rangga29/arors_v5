<?php

namespace App\Http\Controllers\Version2;

use App\Http\Controllers\Controller;
use App\Models\FisioterapiAppointment;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use App\Services\APIHeaderGenerator;
use App\Services\AppointmentDate;
use App\Services\AppointmentOpen;
use App\Services\FisioMaxAppointment;
use App\Services\NormConverter;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use function back;
use function env;
use function json_decode;
use function pow;
use function redirect;
use function strtoupper;

class OldFisioterapiController extends Controller
{
    protected APIHeaderGenerator $apiHeaderGenerator;
    protected NormConverter $normConverter;
    protected FisioMaxAppointment $fisioMaxAppointment;
    protected AppointmentOpen $appointmentOpen;
    protected AppointmentDate $appointmentDate;

    public function __construct(APIHeaderGenerator $apiHeaderGenerator, NormConverter $normConverter, FisioMaxAppointment $fisioMaxAppointment, AppointmentOpen $appointmentOpen, AppointmentDate $appointmentDate)
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->apiHeaderGenerator = $apiHeaderGenerator;
        $this->normConverter = $normConverter;
        $this->fisioMaxAppointment = $fisioMaxAppointment;
        $this->appointmentOpen = $appointmentOpen;
        $this->appointmentDate = $appointmentDate;
    }

    public function index()
    {
        return view('version2.fisio-1', [
            'background' => 'fisio',
            'todayDate' => Carbon::today()->format('Y-m-d'),
            'appointmentDate' => $this->appointmentDate->selectAppointmentDate(),
            'isOpen' => $this->appointmentOpen->selectAppointmentOpen(),
            'currentHour' => now()->hour,
            'dates' => ScheduleDate::where('sd_date', $this->appointmentDate->selectAppointmentDate())->get()
        ]);
    }

    public function appointmentStore(Request $request)
    {
        if(!$this->appointmentOpen->selectAppointmentOpen()) {
            return redirect()->route('old-fisioterapi');
        }

        $link = env('API_KEY', 'rsck');
        $medicalNo = $this->normConverter->normConverter($request->norm);
        $headers = $this->apiHeaderGenerator->generateApiHeader();

        try {
            $birthdate = Carbon::createFromFormat('d/m/Y', $request->birthday)->format('Ymd');
        } catch (InvalidFormatException) {
            return back()->with('error', 'Format Tanggal Lahir Salah');
        }

        $scheduleDateOldFormat = ScheduleDate::where('sd_date', $this->appointmentDate->selectAppointmentDate())->first();
        $scheduleDate = Carbon::createFromFormat('Y-m-d', $scheduleDateOldFormat['sd_date'])->format('Ymd');
        $scheduleDateNumber = Carbon::createFromFormat('Y-m-d', $scheduleDateOldFormat['sd_date'])->format('N');

        $handlerStack = HandlerStack::create();
        $handlerStack->push(Middleware::retry(function ($retry, $request, $response, $exception) {
            return $retry < 10 && $exception instanceof RequestException && $exception->getCode() === 28;
        }, function ($retry) {
            return 1000 * pow(2, $retry);
        }));

        try {
            $client = new Client(['handler' => $handlerStack, 'verify' => false]);
            $response = $client->get("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/patient/{$medicalNo}", [
                'headers' => $headers,
            ]);

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
                if (isset($data['Data'])) {
                    $dataField = json_decode($data['Data'], true);
                    $existingAppointment = FisioterapiAppointment::where('sd_id', $scheduleDateOldFormat['id'])->where('fap_norm', $dataField['MedicalNo'])->first();

                    if($existingAppointment){
                        return back()->with('error', 'Pasien Sudah Terdaftar Pada Tanggal Tersebut.');
                    }

                    if ($birthdate == $dataField['DateOfBirth']) {
                        $checkNorm = FisioterapiAppointment::where('sd_id', $scheduleDateOldFormat['id'])->where('fap_type', $request->service)->where('fap_norm', $dataField['MedicalNo'])->count();
                        if($checkNorm == 0) {
                            do {
                                $ucode = Str::random(20);
                                $ucodeCheck = FisioterapiAppointment::where('fap_ucode', $ucode)->exists();
                            } while ($ucodeCheck);
                            do {
                                $token = Str::random(6);
                                $tokenCheck = FisioterapiAppointment::where('fap_token', $token)->exists();
                            } while ($tokenCheck);

                            $maxPatients = $this->fisioMaxAppointment->getMaxPatients($scheduleDateNumber, $request->service);
                            $currentPatients = FisioterapiAppointment::where('sd_id', $scheduleDateOldFormat['id'])->where('fap_type', $request->service)->count();
                            if($currentPatients < $maxPatients) {
                                if (($request->service === 'UMUM PAGI' || $request->service === 'BPJS PAGI')) {
                                    $reg_time = Carbon::createFromFormat('H:i', '07:00')->addMinutes((7 * ($currentPatients)))->subMinutes(30)->format('H:i');
                                    $app_time = Carbon::createFromFormat('H:i', '07:00')->addMinutes((7 * ($currentPatients)))->format('H:i');
                                } else {
                                    if ($scheduleDateNumber == 6) {
                                        $reg_time = Carbon::createFromFormat('H:i', '12:00')->addMinutes((7 * ($currentPatients)))->subMinutes(30)->format('H:i');
                                        $app_time = Carbon::createFromFormat('H:i', '12:00')->addMinutes((7 * ($currentPatients)))->format('H:i');
                                    } else {
                                        $reg_time = Carbon::createFromFormat('H:i', '14:00')->addMinutes((7 * ($currentPatients)))->subMinutes(30)->format('H:i');
                                        $app_time = Carbon::createFromFormat('H:i', '14:00')->addMinutes((7 * ($currentPatients)))->format('H:i');
                                    }
                                }
                            } else {
                                return back()->with('error', 'Kuota Untuk Tanggal ' . Carbon::createFromFormat('Y-m-d', $scheduleDateOldFormat['sd_date'])->isoFormat('dddd, DD MMMM YYYY') . ' Sudah Terpenuhi.');
                            }

                            FisioterapiAppointment::create([
                                'sd_id' => $scheduleDateOldFormat['id'],
                                'fap_ucode' => $ucode,
                                'fap_token' => strtoupper($token),
                                'fap_type' => $request->service,
                                'fap_queue' => $currentPatients + 1,
                                'fap_registration_time' => $reg_time,
                                'fap_appointment_time' => $app_time,
                                'fap_norm' => $dataField['MedicalNo'],
                                'fap_name' => $dataField['FullName'],
                                'fap_birthday' => Carbon::createFromFormat('Ymd', $dataField['DateOfBirth'])->format('Y-m-d'),
                                'fap_gender' => $dataField['Gender'],
                                'fap_phone' => $request->phone_number
                            ]);
                            return redirect()->route('fisioterapi.final', $ucode)->with('success', 'Registrasi Berhasil Dilakukan');
                        } else {
                            return back()->with('error', 'Pasien Sudah Terdaftar Pada Tanggal Tersebut.');
                        }
                    } else {
                        return back()->with('error', 'Data Pasien Tidak Cocok');
                    }
                } else {
                    return back()->with('error', 'Data Pasien Tidak Ditemukan');
                }
            } else {
                return back()->with('error', 'Request failed. Status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
