<?php

namespace App\Http\Controllers;

use App\Services\ZohoCrm;
use App\Support\PublicView;
use App\Support\TrackingToken;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrackingController extends Controller
{
    public function __construct(private readonly ZohoCrm $crm) {}

    private function linkFor(string $dealId): string
    {
        $secret = config('tracking.tracking_secret');
        $token = TrackingToken::createToken($dealId, $secret);

        return config('tracking.public_base_url')."/t/{$token}";
    }

    /** โหลด Deal และตรวจว่าอนุญาตให้แสดงหรือไม่ */
    private function loadVisibleDeal(string $dealId): array
    {
        $deal = $this->crm->getDeal($dealId);
        if (!$deal) {
            return ['error' => 'notfound'];
        }

        $allowed = config('tracking.allowed_pipelines');
        if (count($allowed) && !empty($deal['Pipeline']) && !in_array($deal['Pipeline'], $allowed, true)) {
            return ['error' => 'notfound'];
        }

        if (in_array($deal['Stage'] ?? null, config('tracking.hidden_stages'), true)) {
            return ['error' => 'hidden'];
        }

        return ['deal' => $deal];
    }

    private function message(string $title, string $message, int $status): Response
    {
        return response()->view('message', [
            'title' => $title,
            'message' => $message,
            'company' => config('tracking.company'),
        ], $status);
    }

    public function show(Request $request, string $token): Response
    {
        $dealId = TrackingToken::verifyToken($token, config('tracking.tracking_secret'));

        if (!$dealId) {
            return $this->message('ไม่พบข้อมูล', 'ลิงก์ไม่ถูกต้องหรือถูกยกเลิกแล้ว กรุณาติดต่อเจ้าหน้าที่', 404)
                ->header('Cache-Control', 'private, no-store');
        }

        try {
            $result = $this->loadVisibleDeal($dealId);

            if (($result['error'] ?? null) === 'hidden') {
                return $this->message('ไม่สามารถแสดงข้อมูลได้', 'งานนี้ปิดการติดตามแล้ว กรุณาติดต่อเจ้าหน้าที่', 410)
                    ->header('Cache-Control', 'private, no-store');
            }

            if ($result['error'] ?? null) {
                return $this->message('ไม่พบข้อมูล', 'ไม่พบงานนี้ในระบบ กรุณาติดต่อเจ้าหน้าที่', 404)
                    ->header('Cache-Control', 'private, no-store');
            }

            $v = PublicView::toPublicView($result['deal'], (bool) config('tracking.show_delay_reason'));

            return response()->view('tracking', ['v' => $v, 'company' => config('tracking.company')])
                ->header('Cache-Control', 'private, no-store');
        } catch (\Throwable $e) {
            report($e);

            return $this->message('ระบบขัดข้องชั่วคราว', 'กรุณาลองใหม่อีกครั้งในอีกสักครู่', 503)
                ->header('Cache-Control', 'private, no-store');
        }
    }

    /** QR ของลิงก์ (ต้องมี token ที่ถูกต้องเท่านั้น) — ใช้แนบเอกสาร/อีเมล */
    public function qr(Request $request, string $token): Response
    {
        $dealId = TrackingToken::verifyToken($token, config('tracking.tracking_secret'));
        if (!$dealId) {
            abort(404);
        }

        $size = (int) $request->query('size', 480) ?: 480;

        $result = (new Builder(writer: new PngWriter()))->build(
            data: $this->linkFor($dealId),
            size: $size,
            margin: 2,
        );

        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
