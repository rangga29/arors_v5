<?php

namespace App\Services;

class FisioMaxAppointment {
    public function getMaxPatients($dayOfWeek, $patientType)
    {
        switch ($dayOfWeek) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                if ($patientType === 'UMUM PAGI') {
                    return 7;
                } elseif ($patientType === 'UMUM SORE') {
                    return 3;
                } elseif ($patientType === 'BPJS PAGI') {
                    return 55;
                } elseif ($patientType === 'BPJS SORE') {
                    return 10;
                }
                break;
            case 6:
                if ($patientType === 'UMUM PAGI') {
                    return 7;
                } elseif ($patientType === 'UMUM SORE') {
                    return 3;
                } elseif ($patientType === 'BPJS PAGI') {
                    return 24;
                } elseif ($patientType === 'BPJS SORE') {
                    return 4;
                }
                break;
            default:
                return 0;
        }
    }
}
