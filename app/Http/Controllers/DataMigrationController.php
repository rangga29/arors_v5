<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AsuransiAppointment;
use App\Models\BpjsKesehatanAppointment;
use App\Models\NewAppointment;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use App\Models\UmumAppointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use League\Csv\Reader;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Str;
use App\Services\APIHeaderGenerator;
use App\Services\NormConverter;

class DataMigrationController extends Controller
{
    protected $apiHeaderGenerator;
    protected $normConverter;

    public function __construct(APIHeaderGenerator $apiHeaderGenerator, NormConverter $normConverter)
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->apiHeaderGenerator = $apiHeaderGenerator;
        $this->normConverter = $normConverter;
    }

    public function index()
    {
        return view('backend.data-migration.view');
    }

    public function dataMigration(Request $request)
    {
        $file = $request->file('csv_file');
        $type = $request->selectedType;

        try {
            $csv = \League\Csv\Reader::createFromPath($file->getRealPath(), 'r');
            $csv->setHeaderOffset(0);
            $records = $csv->getRecords();
            $recordsArray = iterator_to_array($records);
        } catch (\Exception $e) {
            return redirect()->route('data-migration')->withErrors(['CSV Error: ' . $e->getMessage()]);
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($recordsArray as $index => $record) {
            $rowNum = $index + 2;

            try {
                $sdDate = trim($record['sd_date'] ?? '');
                if (empty($sdDate)) {
                    $errorCount++;
                    $errors[] = "Baris {$rowNum}: Kolom sd_date kosong";
                    continue;
                }

                if ($type === 'fisioterapi') {
                    $result = $this->migrateFisioWithApi($record, $sdDate);
                    if ($result === true) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = "Baris {$rowNum}: " . $result;
                    }
                    continue; // Skip the rest, as migrateFisioWithApi handles its own scheduling lookups
                }

                $scheduleDate = ScheduleDate::where('sd_date', $sdDate)->first();
                if (!$scheduleDate) {
                    $errorCount++;
                    $errors[] = "Baris {$rowNum}: ScheduleDate tidak ditemukan untuk tanggal {$sdDate}";
                    continue;
                }

                // 2. Cari ScheduleDetail langsung melalui relasi Schedule
                //    Ini mengatasi masalah ketika sesi 1 dan sesi 2 berada di record Schedule yang berbeda
                $session = trim($record['scd_session'] ?? '1');
                $doctorCode = trim($record['sc_doctor_code']);
                $clinicCode = trim($record['sc_clinic_code']);

                $scheduleDetail = ScheduleDetail::where('scd_session', $session)
                    ->whereHas('schedule', function ($q) use ($scheduleDate, $doctorCode, $clinicCode) {
                        $q->where('sd_id', $scheduleDate->id)
                            ->where('sc_doctor_code', $doctorCode)
                            ->where('sc_clinic_code', $clinicCode);
                    })->first();

                if (!$scheduleDetail) {
                    $errorCount++;
                    $errors[] = "Baris {$rowNum}: ScheduleDetail tidak ditemukan (tanggal: {$sdDate}, doctor: {$doctorCode}, clinic: {$clinicCode}, sesi: {$session})";
                    continue;
                }

                // 4. Insert data
                $result = $this->migrateDirectly($record, $type, $scheduleDetail);

                if ($result === true) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = "Baris {$rowNum}: " . $result;
                }
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Baris {$rowNum}: " . $e->getMessage();
            }
        }

        $message = "Migrasi Selesai. Berhasil: {$successCount}, Gagal: {$errorCount}";
        if ($errorCount > 0) {
            return redirect()->route('data-migration')
                ->with('success', $message)
                ->withErrors($errors);
        }

        return redirect()->route('data-migration')->with('success', $message);
    }

    private function migrateDirectly(array $record, string $type, $scheduleDetail)
    {
        // Create base appointment
        $appointmentData = Appointment::create([
            'scd_id' => $scheduleDetail->id,
            'ap_ucode' => $record['ap_ucode'],
            'ap_no' => $record['ap_no'],
            'ap_token' => $record['ap_token'],
            'ap_queue' => $record['ap_queue'],
            'ap_type' => $record['ap_type'],
            'ap_registration_time' => $record['ap_registration_time'],
            'ap_appointment_time' => $record['ap_appointment_time'],
        ]);

        // Create type-specific record
        if ($type == 'umum') {
            UmumAppointment::create([
                'ap_id' => $appointmentData->id,
                'uap_norm' => $record['uap_norm'],
                'uap_name' => $record['uap_name'],
                'uap_birthday' => $record['uap_birthday'],
                'uap_gender' => $record['uap_gender'],
                'uap_phone' => $record['uap_phone'],
            ]);
            $this->updateCountersUmum($scheduleDetail);
        } elseif ($type == 'asuransi') {
            AsuransiAppointment::create([
                'ap_id' => $appointmentData->id,
                'aap_norm' => $record['aap_norm'],
                'aap_name' => $record['aap_name'],
                'aap_birthday' => $record['aap_birthday'],
                'aap_gender' => $record['aap_gender'],
                'aap_phone' => $record['aap_phone'],
                'aap_business_partner_code' => $record['aap_business_partner_code'] ?? 'AAA',
                'aap_business_partner_name' => $record['aap_business_partner_name'],
            ]);
            $this->updateCountersUmum($scheduleDetail);
        } elseif ($type == 'bpjs') {
            BpjsKesehatanAppointment::create([
                'ap_id' => $appointmentData->id,
                'bap_norm' => $record['bap_norm'],
                'bap_name' => $record['bap_name'],
                'bap_birthday' => $record['bap_birthday'],
                'bap_gender' => $record['bap_gender'],
                'bap_phone' => $record['bap_phone'],
                'bap_bpjs' => $record['bap_bpjs'],
                'bap_ppk1' => $record['bap_ppk1'],
            ]);
            ScheduleDetail::where('id', $scheduleDetail->id)->increment('scd_counter_max_bpjs');
            ScheduleDetail::where('id', $scheduleDetail->id)->increment('scd_counter_online_bpjs');
        } elseif ($type == 'baru') {
            NewAppointment::create([
                'ap_id' => $appointmentData->id,
                'nap_norm' => '00-00-00-00',
                'nap_name' => $record['nap_name'],
                'nap_birthday' => $record['nap_birthday'],
                'nap_phone' => $record['nap_phone'],
                'nap_ssn' => $record['nap_ssn'],
                'nap_gender' => $record['nap_gender'],
                'nap_address' => $record['nap_address'],
                'nap_email' => $record['nap_email'],
                'nap_business_partner_code' => $record['nap_business_partner_code'] ?? 'PERSONAL',
                'nap_business_partner_name' => $record['nap_business_partner_name'] ?? '',
            ]);
            $this->updateCountersUmum($scheduleDetail);
        }

        return true;
    }

    private function updateCountersUmum($scheduleDetail): void
    {
        if ($scheduleDetail->scd_counter_online_umum >= $scheduleDetail->scd_online_umum && $scheduleDetail->scd_counter_online_bpjs <= $scheduleDetail->scd_online_bpjs) {
            ScheduleDetail::where('id', $scheduleDetail->id)->decrement('scd_max_bpjs');
            ScheduleDetail::where('id', $scheduleDetail->id)->decrement('scd_online_bpjs');
            ScheduleDetail::where('id', $scheduleDetail->id)->increment('scd_max_umum');
            ScheduleDetail::where('id', $scheduleDetail->id)->increment('scd_online_umum');
        }
        ScheduleDetail::where('id', $scheduleDetail->id)->increment('scd_counter_max_umum');
        ScheduleDetail::where('id', $scheduleDetail->id)->increment('scd_counter_online_umum');
    }

    private function migrateFisioWithApi(array $record, string $sdDate)
    {
        $fapType = strtoupper(trim($record['fap_type']));
        $doctorCode = 'FIS001'; // Default UMUM/ASURANSI
        $session = '1'; // Default PAGI
        $isBpjs = 0;
        $isPersonal = 1;
        $businessPartnerCode = 'PERSONAL';
        $contractNo = '';

        if (str_contains($fapType, 'BPJS')) {
            $doctorCode = 'FIS002';
            $isBpjs = 1;
            $isPersonal = 0;
            $businessPartnerCode = 'BP00001';
            $contractNo = '114/HP-PKS-RSCK/XII/2021';
        } elseif (str_contains($fapType, 'ASURANSI')) {
            $isPersonal = 0;
            // If they are insurance, Medinfras requires BP code. We might need a fallback.
            $businessPartnerCode = 'ASURANSI_FALLBACK';
        }

        if (str_contains($fapType, 'SORE')) {
            $session = '2';
        }

        // 1. Find ScheduleDate
        $scheduleDate = ScheduleDate::where('sd_date', $sdDate)->first();
        if (!$scheduleDate) {
            return "ScheduleDate tidak ditemukan untuk tanggal {$sdDate}";
        }

        // 2. Find Schedule detail
        $clinicCode = 'KLI030'; // Assuming RehabMedik clinic code, might need adjustment based on your environment
        $scheduleDetail = ScheduleDetail::where('scd_session', $session)
            ->whereHas('schedule', function ($q) use ($scheduleDate, $doctorCode, $clinicCode) {
                $q->where('sd_id', $scheduleDate->id)
                    ->where('sc_doctor_code', $doctorCode)
                    // ->where('sc_clinic_code', $clinicCode)  // Optionally restrict by clinic if needed
                    ->whereIn('sc_clinic_department', ['DIAGNOSTIC', 'REHAB MEDIK']); // Usually Fisioterapi is Diagnostic/Rehab
            })->first();

        if (!$scheduleDetail) {
            return "ScheduleDetail tidak ditemukan untuk Fisioterapi (tanggal: {$sdDate}, doctor: {$doctorCode}, sesi: {$session})";
        }

        $scheduleData = Schedule::find($scheduleDetail->sc_id);

        $link = env('API_KEY', 'rsck');
        $normStr = trim($record['fap_norm']);
        // Strip dashes/spaces first so NormConverter padding works correctly
        $cleanNorm = preg_replace('/[^0-9]/', '', $normStr);
        $medicalNo = $this->normConverter->normConverter($cleanNorm);
        $headers = $this->apiHeaderGenerator->generateApiHeader();

        // 3. Hit Patient Check API first to validate and get accurate DateOfBirth and PatientName
        $handlerStackCheck = \GuzzleHttp\HandlerStack::create();
        $handlerStackCheck->push(\GuzzleHttp\Middleware::retry(function ($retryCheck, $requestCheck, $responseCheck, $exceptionCheck) {
            return $retryCheck < 10 && $exceptionCheck instanceof RequestException && $exceptionCheck->getCode() === 28;
        }, function ($retryCheck) {
            return 1000 * pow(2, $retryCheck);
        }));

        try {
            $clientCheck = new Client(['handler' => $handlerStackCheck, 'verify' => false]);
            $responseCheck = $clientCheck->get("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/patient/{$medicalNo}", [
                'headers' => $headers,
            ]);

            if ($responseCheck->getStatusCode() == 200) {
                $dataCheck = json_decode($responseCheck->getBody(), true);
                if (isset($dataCheck['Data'])) {
                    $dataFieldCheck = json_decode($dataCheck['Data'], true);

                    try {
                        $birthdate = Carbon::createFromFormat('Y-m-d', substr($record['fap_birthday'], 0, 10))->format('Ymd');
                    } catch (\Exception $e) {
                        return "Format Tanggal Lahir Salah di CSV: " . $record['fap_birthday'];
                    }

                    if ($birthdate == $dataFieldCheck['DateOfBirth']) {
                        $requestData = [
                            'HealthcareID' => '001',
                            'DepartmentID' => $scheduleData->sc_clinic_department,
                            'AppointmentMethod' => '003',
                            'MedicalNo' => $dataFieldCheck['MedicalNo'],
                            'ServiceUnitCode' => $scheduleData->sc_clinic_code,
                            'ParamedicCode' => $scheduleData->sc_doctor_code,
                            'VisitTypeCode' => 'VT09',
                            'OperationalTimeCode' => $scheduleData->sc_operational_time_code,
                            'StartDate' => Carbon::createFromFormat('Y-m-d', $sdDate)->format('Ymd'),
                            'Session' => $scheduleDetail->scd_session,
                            'Notes' => '', // Notes left blank to match FisioV2PatientCheck
                            'IsPersonalPayer' => $isPersonal,
                            'BusinessPartnerCode' => $businessPartnerCode,
                            'ContractNo' => $contractNo,
                            'IsBPJS' => $isBpjs,
                            'IsNewPatient' => 0,
                            'UserID' => '197317247'
                        ];

                        $handlerStackApp = \GuzzleHttp\HandlerStack::create();
                        $handlerStackApp->push(\GuzzleHttp\Middleware::retry(function ($retryApp, $requestApp, $responseApp, $exceptionApp) {
                            return $retryApp < 3 && $exceptionApp instanceof RequestException && $exceptionApp->getCode() === 28;
                        }, function ($retryApp) {
                            return 1000 * pow(2, $retryApp);
                        }));

                        try {
                            $clientApp = new Client(['handler' => $handlerStackApp, 'verify' => false]);
                            $responseApp = $clientApp->post("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/v2/centerback/ADT_A05_01", [
                                'headers' => $headers,
                                'form_params' => $requestData
                            ]);

                            if ($responseApp->getStatusCode() == 200) {
                                $dataApp = json_decode($responseApp->getBody(), true);
                                if ($dataApp['Status'] == 'SUCCESS (000)') {
                                    $dataFieldApp = json_decode($dataApp['Data'], true);

                                    do {
                                        $tokenApp = Str::random(6);
                                        $tokenCheckApp = Appointment::where('scd_id', $scheduleDetail->id)->where('ap_token', $tokenApp)->exists();
                                    } while ($tokenCheckApp);

                                    $typeLabel = $isBpjs ? 'BPJS' : (str_contains($fapType, 'ASURANSI') ? 'ASURANSI' : 'UMUM');

                                    $appointment = Appointment::create([
                                        'scd_id' => $scheduleDetail->id,
                                        'ap_ucode' => $dataFieldApp['AppointmentID'],
                                        'ap_no' => $dataFieldApp['AppointmentNo'],
                                        'ap_token' => strtoupper($tokenApp),
                                        'ap_queue' => $record['fap_queue'] ?? $dataFieldApp['QueueNo'] ?? 0,
                                        'ap_type' => $typeLabel,
                                        'ap_registration_time' => $record['fap_registration_time'] ?? Carbon::createFromFormat('H:i', $dataFieldApp['StartTime'])->subMinutes(30)->format('H:i:s'),
                                        'ap_appointment_time' => $record['fap_appointment_time'] ?? Carbon::createFromFormat('H:i', $dataFieldApp['StartTime'])->format('H:i:s'),
                                    ]);

                                    if ($isBpjs) {
                                        BpjsKesehatanAppointment::create([
                                            'ap_id' => $appointment->id,
                                            'bap_norm' => $normStr,
                                            'bap_name' => $record['fap_name'],
                                            'bap_birthday' => substr($record['fap_birthday'], 0, 10),
                                            'bap_gender' => explode('^', $record['fap_gender'])[0] ?? 'M', // Splitting M^Laki-laki
                                            'bap_phone' => $record['fap_phone'],
                                            'bap_bpjs' => $record['fap_bpjs'] ?? '-',
                                            'bap_ppk1' => '-'
                                        ]);
                                        ScheduleDetail::where('id', $scheduleDetail->id)->increment('scd_counter_max_bpjs');
                                        ScheduleDetail::where('id', $scheduleDetail->id)->increment('scd_counter_online_bpjs');
                                    } elseif ($typeLabel === 'ASURANSI') {
                                        AsuransiAppointment::create([
                                            'ap_id' => $appointment->id,
                                            'aap_norm' => $normStr,
                                            'aap_name' => $record['fap_name'],
                                            'aap_birthday' => substr($record['fap_birthday'], 0, 10),
                                            'aap_gender' => explode('^', $record['fap_gender'])[0] ?? 'M',
                                            'aap_phone' => $record['fap_phone'],
                                            'aap_business_partner_code' => $businessPartnerCode,
                                            'aap_business_partner_name' => 'Migrasi'
                                        ]);
                                        $this->updateCountersUmum($scheduleDetail);
                                    } else {
                                        UmumAppointment::create([
                                            'ap_id' => $appointment->id,
                                            'uap_norm' => $normStr,
                                            'uap_name' => $record['fap_name'],
                                            'uap_birthday' => substr($record['fap_birthday'], 0, 10),
                                            'uap_gender' => explode('^', $record['fap_gender'])[0] ?? 'M',
                                            'uap_phone' => $record['fap_phone'],
                                        ]);
                                        $this->updateCountersUmum($scheduleDetail);
                                    }

                                    return true;
                                } else {
                                    return "API Error: " . $dataApp['Status'] . " - " . ($dataApp['Remarks'] ?? '');
                                }
                            } else {
                                return "API HTTP Error " . $responseApp->getStatusCode();
                            }
                        } catch (\Exception $e) {
                            return "API Catch Error: " . $e->getMessage();
                        }
                    } else {
                        return "Data Pasien Tidak Sesuai (Tanggal Lahir di Medinfras berbeda dengan CSV).";
                    }
                } else {
                    return "Data Pasien Tidak Ditemukan di Medinfras.";
                }
            } else {
                return "Mohon Maaf Terjadi Kesalahan Pada Sistem Medinfras [" . $responseCheck->getStatusCode() . "]";
            }
        } catch (RequestException $e) {
            return "Koneksi ke Medinfras Error: " . $e->getMessage();
        }
    }
}
