<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/** เรียก Google Cloud Translation API (v2, REST + API key) พร้อมแคชผลถาวร */
class GoogleTranslateService
{
    private readonly ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google_translate.key') ?: null;
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== null;
    }

    /** แปลข้อความไทย -> ภาษาปลายทาง คืนค่า null หากแปลไม่ได้ (ไม่มีคีย์/เรียก API ล้มเหลว) */
    public function translate(string $text, string $targetLang): ?string
    {
        if (!$this->apiKey || trim($text) === '') {
            return null;
        }

        $cacheKey = 'gtranslate:'.$targetLang.':'.md5($text);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $res = Http::asForm()->timeout(5)->post('https://translation.googleapis.com/language/translate/v2', [
                'key' => $this->apiKey,
                'q' => $text,
                'source' => 'th',
                'target' => $targetLang,
                'format' => 'text',
            ]);

            if (!$res->successful()) {
                report(new \RuntimeException('Google Translate API failed: '.$res->status().' '.$res->body()));

                return null;
            }

            $translated = $res->json('data.translations.0.translatedText');
            if (!$translated) {
                return null;
            }

            Cache::put($cacheKey, $translated, now()->addDays(30));

            return $translated;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}
