<?php

namespace App\Http\Controllers;

use App\Services\ZohoCrm;
use App\Support\TrackingToken;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(private readonly ZohoCrm $crm) {}

    private function linkFor(string $dealId): string
    {
        $secret = config('tracking.tracking_secret');
        $token = TrackingToken::createToken($dealId, $secret);

        return config('tracking.public_base_url')."/t/{$token}";
    }

    public function link(Request $request): JsonResponse
    {
        $adminKey = config('tracking.admin_api_key');
        $given = (string) $request->header('X-Admin-Key', '');

        if (!hash_equals($adminKey, $given)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $dealId = trim((string) $request->query('deal_id', ''));
        if (!preg_match('/^\d{5,25}$/', $dealId)) {
            return response()->json(['error' => 'invalid deal_id'], 400);
        }

        try {
            if ($request->query('check', 'true') !== 'false') {
                $deal = $this->crm->getDeal($dealId);
                if (!$deal) {
                    return response()->json(['error' => 'deal not found'], 404);
                }
            }

            $token = TrackingToken::createToken($dealId, config('tracking.tracking_secret'));
            $url = $this->linkFor($dealId);

            $qr = (new Builder(writer: new PngWriter()))->build(data: $url, size: 480, margin: 2);

            return response()->json([
                'deal_id' => $dealId,
                'url' => $url,
                'qr_png_url' => config('tracking.public_base_url')."/qr/{$token}.png",
                'qr_data_url' => $qr->getDataUri(),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['error' => 'crm error'], 502);
        }
    }
}
