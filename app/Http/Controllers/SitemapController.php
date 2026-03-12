<?php

namespace App\Http\Controllers;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use function now;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = Sitemap::create();

        // Halaman Utama
        $sitemap->add(Url::create('/')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(1.0));

        // Rawat Jalan
        $sitemap->add(Url::create('/registrasi/pasien-umum')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.9));

        $sitemap->add(Url::create('/registrasi/pasien-bpjs')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.9));

        $sitemap->add(Url::create('/registrasi/pasien-baru')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.9));

        // Rehabilitasi Medik & Fisioterapi
        $sitemap->add(Url::create('/registrasi/pasien-rehab-medik-fisioterapi')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.9));

        $sitemap->add(Url::create('/registrasi/pasien-rehab-medik')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.9));

        $sitemap->add(Url::create('/registrasi/pasien-fisioterapi')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.9));

        // Sunday Clinic
        $sitemap->add(Url::create('/registrasi/pasien-sunday-clinic')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(0.8));

        // Cek Antrian
        $sitemap->add(Url::create('/cek-antrian-pasien/norm')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.8));

        $sitemap->add(Url::create('/cek-antrian-pasien/nik')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.8));

        // Batal Antrian
        $sitemap->add(Url::create('/batal-antrian-pasien/norm')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.7));

        $sitemap->add(Url::create('/batal-antrian-pasien/nik')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.7));

        // Save sitemap to file
        $sitemap->writeToFile(public_path('sitemap.xml'));

        return $sitemap->toResponse(request());
    }
}
