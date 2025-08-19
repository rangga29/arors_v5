<?php

namespace App\Http\Controllers;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use function now;

class SitemapController extends Controller
{
    public function index()
    {
        // Create a new sitemap instance
        $sitemap = Sitemap::create();

        // Add static URLs
        $sitemap->add(Url::create('/')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_ALWAYS)
            ->setPriority(1.0));

        $sitemap->add(Url::create('/registrasi/pasien-umum')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_ALWAYS)
            ->setPriority(1.0));

        $sitemap->add(Url::create('/registrasi/pasien-bpjs')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_ALWAYS)
            ->setPriority(1.0));

        $sitemap->add(Url::create('/registrasi/pasien-fisioterapi')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_ALWAYS)
            ->setPriority(1.0));

        $sitemap->add(Url::create('/registrasi/pasien-baru')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_ALWAYS)
            ->setPriority(1.0));
        $sitemap->add(Url::create('/registrasi/sunday-clinic')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_ALWAYS)
            ->setPriority(1.0));

        $sitemap->add(Url::create('/cek-antrian-pasien/norm')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_ALWAYS)
            ->setPriority(1.0));

        $sitemap->add(Url::create('/cek-antrian-pasien/nik')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_ALWAYS)
            ->setPriority(1.0));

        $sitemap->add(Url::create('/v2/registrasi/pasien-umum')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_ALWAYS)
            ->setPriority(1.0));

        $sitemap->add(Url::create('/v2/registrasi/pasien-bpjs')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_ALWAYS)
            ->setPriority(1.0));

        $sitemap->add(Url::create('/v2/registrasi/pasien-fisioterapi')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_ALWAYS)
            ->setPriority(1.0));

        $sitemap->add(Url::create('/v2/registrasi/pasien-baru')
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_ALWAYS)
            ->setPriority(1.0));

        // Save sitemap to file
        $sitemap->writeToFile(public_path('sitemap.xml'));

        // Optionally return the sitemap response for debugging
        return $sitemap->toResponse(request());
    }
}
