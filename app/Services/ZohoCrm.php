<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * ตัวเรียก Zoho CRM API แบบบางๆ + แคชผลลัพธ์ระยะสั้น
 */
class ZohoCrm
{
    public function __construct(
        private readonly ZohoAuth $auth,
        private readonly string $apiDomain,
        private readonly string $apiVersion,
        private readonly int $cacheTtlSeconds,
    ) {}

    public function getDeal(string $dealId): ?array
    {
        if (!preg_match('/^\d{5,25}$/', $dealId)) {
            return null;
        }

        return Cache::remember("zoho:deal:{$dealId}", $this->cacheTtlSeconds, function () use ($dealId) {
            try {
                $json = $this->request("/Deals/{$dealId}");

                return $json['data'][0] ?? null;
            } catch (ZohoCrmException $e) {
                if ($e->status === 404 || $e->code === 'INVALID_DATA') {
                    return null;
                }
                throw $e;
            }
        });
    }

    private function request(string $path, bool $retry = true): array
    {
        $token = $this->auth->getAccessToken();

        $res = Http::withToken($token, 'Zoho-oauthtoken')
            ->get("{$this->apiDomain}/crm/{$this->apiVersion}{$path}");

        if ($res->status() === 204) {
            return [];
        }

        $json = $res->json() ?? [];

        // token ถูกเพิกถอน/หมดอายุก่อนเวลา -> refresh แล้วลองใหม่ 1 ครั้ง
        if ($res->status() === 401 && $retry) {
            $this->auth->invalidate();

            return $this->request($path, false);
        }

        if (!$res->successful()) {
            throw new ZohoCrmException(
                "Zoho CRM {$res->status()}: ".trim(($json['code'] ?? '').' '.($json['message'] ?? '')),
                $res->status(),
                $json['code'] ?? null,
            );
        }

        return $json;
    }
}
