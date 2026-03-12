<?php

namespace App\Http\Controllers;

use App\Services\APIBpjsHeaderGenerator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use LZCompressor\LZString;
use function view;

class CekBpjsController extends Controller
{
    protected APIBpjsHeaderGenerator $apiBpjsHeaderGenerator;

    public function __construct(APIBpjsHeaderGenerator $apiBpjsHeaderGenerator)
    {
        $this->apiBpjsHeaderGenerator = $apiBpjsHeaderGenerator;
    }

    public function viewCekSep()
    {
        return view('backend.bpjs-check.view-sep');
    }

    public function cekSep(Request $request)
    {
        $headerBpjs = $this->apiBpjsHeaderGenerator->generateApiBpjsHeader();

        $handlerStackBpjs = HandlerStack::create();
        $handlerStackBpjs->push(Middleware::retry(function ($retry, $request, $response, $exception) {
            return $retry < 10 && $exception instanceof RequestException && $exception->getCode() === 28;
        }, function ($retry) {
            return 1000 * pow(2, $retry);
        }));

        try {
            $clientBpjs = new Client(['handler' => $handlerStackBpjs, 'verify' => false]);
            $responseBpjs = $clientBpjs->get("https://apijkn.bpjs-kesehatan.go.id/vclaim-rest/peserta/nik/{$request->nik}/tglSEP/2024-12-12", [
                'headers' => $headerBpjs,
            ]);

            $dataBpjs = json_decode($responseBpjs->getBody(), true);
            if($dataBpjs['metaData']['code'] == 200)
            {
                date_default_timezone_set('UTC');
                $bpjs_time_stamp = $headerBpjs['X-timestamp'];

                $bpjs_consumer_id = "25796";
                $bpjs_consumer_secret = "4qP1E30D6D";

                $bpjs_key_dec = $bpjs_consumer_id . $bpjs_consumer_secret . $bpjs_time_stamp;
                $bpjs_key_hash = hex2bin(hash('SHA256', $bpjs_key_dec));
                $bpjs_key_iv = substr($bpjs_key_hash, 0, 16);
                $bpjs_decryptResult = openssl_decrypt(base64_decode($dataBpjs['response']), 'AES-256-CBC', $bpjs_key_hash, OPENSSL_RAW_DATA, $bpjs_key_iv);

                $bpjs_unCompressedResult = LZString::decompressFromEncodedURIComponent($bpjs_decryptResult);
                $bpjs_result = json_decode($bpjs_unCompressedResult, TRUE);

                return view('backend.bpjs-check.result', [
                    'status' => $dataBpjs['metaData'],
                    'data' => $bpjs_result
                ]);
            } else {
                return view('backend.bpjs-check.result', [
                    'status' => $dataBpjs['metaData'],
                    'data' => ''
                ]);
            }
        } catch (RequestException $e) {
            $type = 'danger';
            $message = response()->json(['error' => $e->getMessage()], 500);
            return redirect()->route('cek-bpjs.view-cek-sep')->with($type, $message);
        }
    }

    public function viewCekRujukan()
    {
        return view('backend.bpjs-check.view-rujukan');
    }

    public function cekRujukan(Request $request)
    {
        $headerBpjs = $this->apiBpjsHeaderGenerator->generateApiBpjsHeader();

        $handlerStackBpjs = HandlerStack::create();
        $handlerStackBpjs->push(Middleware::retry(function ($retry, $request, $response, $exception) {
            return $retry < 10 && $exception instanceof RequestException && $exception->getCode() === 28;
        }, function ($retry) {
            return 1000 * pow(2, $retry);
        }));

        try {
            $clientBpjs = new Client(['handler' => $handlerStackBpjs, 'verify' => false]);
            $responseBpjs = $clientBpjs->get("https://apijkn.bpjs-kesehatan.go.id/vclaim-rest/rujukan/{$request->rujukan}", [
                'headers' => $headerBpjs,
            ]);

            $dataBpjs = json_decode($responseBpjs->getBody(), true);
            if($dataBpjs['metaData']['code'] == 200)
            {
                date_default_timezone_set('UTC');
                $bpjs_time_stamp = $headerBpjs['X-timestamp'];

                $bpjs_consumer_id = "25796";
                $bpjs_consumer_secret = "4qP1E30D6D";

                $bpjs_key_dec = $bpjs_consumer_id . $bpjs_consumer_secret . $bpjs_time_stamp;
                $bpjs_key_hash = hex2bin(hash('SHA256', $bpjs_key_dec));
                $bpjs_key_iv = substr($bpjs_key_hash, 0, 16);
                $bpjs_decryptResult = openssl_decrypt(base64_decode($dataBpjs['response']), 'AES-256-CBC', $bpjs_key_hash, OPENSSL_RAW_DATA, $bpjs_key_iv);

                $bpjs_unCompressedResult = LZString::decompressFromEncodedURIComponent($bpjs_decryptResult);
                $bpjs_result = json_decode($bpjs_unCompressedResult, TRUE);

                return view('backend.bpjs-check.result', [
                    'status' => $dataBpjs['metaData'],
                    'data' => $bpjs_result
                ]);
            } else {
                return view('backend.bpjs-check.result', [
                    'status' => $dataBpjs['metaData'],
                    'data' => ''
                ]);
            }
        } catch (RequestException $e) {
            $type = 'danger';
            $message = response()->json(['error' => $e->getMessage()], 500);
            return redirect()->route('cek-bpjs.view-cek-rujukan')->with($type, $message);
        }
    }
}
