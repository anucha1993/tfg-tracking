<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * ลิงก์ลูกค้า = <dealId>.<signature>
 * signature = HMAC-SHA256(TRACKING_SECRET, "deal:<dealId>") แบบ hex ตัวพิมพ์เล็ก 32 ตัวแรก
 *
 * ใช้รูปแบบ hex เพื่อให้ Zoho Deluge สร้างลิงก์เองได้ด้วย:
 *   zoho.encryption.hmacsha256(secret, "deal:" + dealId, "hex").subString(0,32)
 *
 * - เดา/แก้เลข Deal เพื่อดูของลูกค้ารายอื่นไม่ได้ เพราะลายเซ็นจะไม่ตรง
 * - เปลี่ยน TRACKING_SECRET = ยกเลิกลิงก์เก่าทั้งหมด
 * - ยังรับลิงก์รุ่นแรก (base64url 22 ตัว) ได้ เผื่อมีที่สร้างไปแล้ว
 */
class TrackingToken
{
    private static function hmac(string $dealId, string $secret): string
    {
        return hash_hmac('sha256', "deal:{$dealId}", $secret, true);
    }

    private static function sigHex(string $dealId, string $secret): string
    {
        return substr(bin2hex(self::hmac($dealId, $secret)), 0, 32);
    }

    private static function sigB64(string $dealId, string $secret): string
    {
        $hash = substr(self::hmac($dealId, $secret), 0, 16);

        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }

    public static function createToken(string|int $dealId, string $secret): string
    {
        $id = trim((string) $dealId);
        if (!preg_match('/^\d{5,25}$/', $id)) {
            throw new InvalidArgumentException('Invalid deal id');
        }

        return $id.'.'.self::sigHex($id, $secret);
    }

    /** คืนค่า dealId ถ้า token ถูกต้อง ไม่งั้นคืน null */
    public static function verifyToken(?string $token, string $secret): ?string
    {
        if (!is_string($token) || strlen($token) > 80) {
            return null;
        }

        if (preg_match('/^(\d{5,25})\.([0-9a-fA-F]{32})$/', $token, $m)) {
            [, $id, $sig] = $m;

            return hash_equals(self::sigHex($id, $secret), strtolower($sig)) ? $id : null;
        }

        if (preg_match('/^(\d{5,25})\.([A-Za-z0-9_-]{22})$/', $token, $m)) {
            [, $id, $sig] = $m;
            $expected = self::sigB64($id, $secret);

            return strlen($expected) === strlen($sig) && hash_equals($expected, $sig) ? $id : null;
        }

        return null;
    }
}
