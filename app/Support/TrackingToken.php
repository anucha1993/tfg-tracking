<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * ลิงก์ลูกค้า = <dealId>.<signature>
 * signature = HMAC-SHA256(TRACKING_SECRET, "deal:<dealId>") ตัดเหลือ 16 bytes (base64url)
 *
 * - เดา/แก้เลข Deal เพื่อดูของลูกค้ารายอื่นไม่ได้ เพราะลายเซ็นจะไม่ตรง
 * - เปลี่ยน TRACKING_SECRET = ยกเลิกลิงก์เก่าทั้งหมด
 */
class TrackingToken
{
    private static function sign(string $dealId, string $secret): string
    {
        $hash = substr(hash_hmac('sha256', "deal:{$dealId}", $secret, true), 0, 16);

        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }

    public static function createToken(string|int $dealId, string $secret): string
    {
        $id = trim((string) $dealId);
        if (!preg_match('/^\d{5,25}$/', $id)) {
            throw new InvalidArgumentException('Invalid deal id');
        }

        return $id.'.'.self::sign($id, $secret);
    }

    /** คืนค่า dealId ถ้า token ถูกต้อง ไม่งั้นคืน null */
    public static function verifyToken(?string $token, string $secret): ?string
    {
        if (!is_string($token) || strlen($token) > 80) {
            return null;
        }

        if (!preg_match('/^(\d{5,25})\.([A-Za-z0-9_-]{22})$/', $token, $m)) {
            return null;
        }

        [, $id, $sig] = $m;
        $expected = self::sign($id, $secret);

        if (strlen($expected) !== strlen($sig)) {
            return null;
        }

        return hash_equals($expected, $sig) ? $id : null;
    }
}
