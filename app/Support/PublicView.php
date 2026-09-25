<?php

namespace App\Support;

use DateTimeImmutable;
use DateTimeZone;
use IntlDateFormatter;

/**
 * แปลงข้อมูล Deal จาก CRM -> ข้อมูลที่ "อนุญาต" ให้ลูกค้าเห็น (whitelist)
 * (ไม่มีมูลค่าดีล, ชื่อแรงงาน, โน้ตภายใน)
 */
class PublicView
{
    private const STATUS = [
        'ดำเนินการแล้ว' => 'done',
        'กำลังดำเนินการ' => 'active',
        'ดำเนินการล่าช้า' => 'delayed',
        'รอดำเนินการ' => 'pending',
    ];

    public const STATUS_LABEL = [
        'done' => 'เสร็จแล้ว',
        'active' => 'กำลังดำเนินการ',
        'delayed' => 'ล่าช้า',
        'pending' => 'รอดำเนินการ',
    ];

    public static function toPublicView(array $deal, bool $showDelayReason = true): array
    {
        $rows = is_array($deal['Tracking_System'] ?? null) ? $deal['Tracking_System'] : [];

        $steps = [];
        foreach ($rows as $i => $r) {
            $steps[] = [
                'order' => (int) ($r['LinkingModule5_Serial_Number'] ?? ($i + 1)) ?: $i + 1,
                'title' => (!empty($r['Job_Status']) && $r['Job_Status'] !== '-None-')
                    ? $r['Job_Status']
                    : 'ขั้นตอนที่ '.($i + 1),
                'status' => self::STATUS[$r['Tracking_Status'] ?? ''] ?? 'pending',
                // หมายเหตุ: Expected_Date ใน CRM คือ "วันเริ่ม" ที่วางแผนของขั้นตอนนี้
                'expectedDate' => $r['Expected_Date'] ?? null,
                'estimatedDays' => (int) ($r['Estimated_Days'] ?? 0) ?: null,
                'actualDate' => $r['Actual_Date'] ?? null,
                'delayReason' => $showDelayReason ? ($r['Delay_Reason'] ?? null) : null,
                'isCurrent' => false,
            ];
        }

        usort($steps, fn ($a, $b) => $a['order'] <=> $b['order']);

        $done = count(array_filter($steps, fn ($s) => $s['status'] === 'done'));

        $current = null;
        foreach ($steps as &$s) {
            if ($s['status'] === 'active' || $s['status'] === 'delayed') {
                $current = &$s;
                break;
            }
        }
        unset($s);
        if (!$current) {
            foreach ($steps as &$s) {
                if ($s['status'] === 'pending') {
                    $current = &$s;
                    break;
                }
            }
            unset($s);
        }
        if ($current) {
            $current['isCurrent'] = true;
        }

        // วันคาดว่าจะเสร็จ = วันเริ่มของขั้นสุดท้าย + จำนวนวันของขั้นนั้น
        $estimatedFinish = null;
        $withDates = array_values(array_filter($steps, fn ($s) => $s['expectedDate']));
        usort($withDates, fn ($a, $b) => strcmp($a['expectedDate'], $b['expectedDate']));
        $last = end($withDates);
        if ($last) {
            $estimatedFinish = self::addDays($last['expectedDate'], $last['estimatedDays'] ?? 0);
        } elseif (!empty($deal['Start_Date']) && !empty($deal['field47'])) {
            $estimatedFinish = self::addDays($deal['Start_Date'], (int) $deal['field47']);
        }

        $total = count($steps);

        return [
            'jobCode' => $deal['M5L_Job_Code'] ?? $deal['Deal_Name'] ?? '',
            'refNo' => (!empty($deal['M5L_Job_Code']) && !empty($deal['Deal_Name']) && $deal['Deal_Name'] !== $deal['M5L_Job_Code'])
                ? $deal['Deal_Name']
                : '',
            'customer' => $deal['Account_Name']['name'] ?? '',
            'services' => is_array($deal['MOU_Service'] ?? null) ? $deal['MOU_Service'] : [],
            'stage' => $deal['Stage'] ?? '',
            'startDate' => $deal['Start_Date'] ?? null,
            'estimatedFinish' => $estimatedFinish,
            'updatedAt' => $deal['Modified_Time'] ?? null,
            'total' => $total,
            'done' => $done,
            'percent' => $total ? (int) round(($done / $total) * 100) : 0,
            'allDone' => $total > 0 && $done === $total,
            'current' => $current,
            'steps' => $steps,
        ];
    }

    public static function addDays(?string $isoDate, int $days): ?string
    {
        if (!$isoDate) {
            return null;
        }
        try {
            $d = new DateTimeImmutable($isoDate.'T00:00:00', new DateTimeZone('UTC'));
        } catch (\Exception) {
            return null;
        }

        return $d->modify("+{$days} days")->format('Y-m-d');
    }

    public static function thDate(?string $iso): string
    {
        if (!$iso) {
            return '-';
        }
        $tz = new DateTimeZone('UTC');
        try {
            $d = preg_match('/^\d{4}-\d{2}-\d{2}$/', $iso)
                ? new DateTimeImmutable($iso.'T00:00:00', $tz)
                : new DateTimeImmutable($iso);
        } catch (\Exception) {
            return '-';
        }

        $fmt = new IntlDateFormatter('th_TH@calendar=buddhist', IntlDateFormatter::NONE, IntlDateFormatter::NONE, $tz, IntlDateFormatter::TRADITIONAL, 'd MMM y');

        return $fmt->format($d) ?: '-';
    }

    public static function thDateTime(?string $iso): string
    {
        if (!$iso) {
            return '-';
        }
        try {
            $d = new DateTimeImmutable($iso);
        } catch (\Exception) {
            return '-';
        }
        $tz = new DateTimeZone('Asia/Bangkok');
        $fmt = new IntlDateFormatter('th_TH@calendar=buddhist', IntlDateFormatter::NONE, IntlDateFormatter::NONE, $tz, IntlDateFormatter::TRADITIONAL, 'd MMM y HH:mm');

        return $fmt->format($d) ?: '-';
    }

    /** "เริ่มประมาณ 25 ก.ย. 2569 · ใช้เวลาประมาณการ 2 วัน" */
    public static function planText(?array $step): string
    {
        if (!$step || empty($step['expectedDate'])) {
            return '';
        }
        $text = 'เริ่มประมาณ '.self::thDate($step['expectedDate']);
        if (!empty($step['estimatedDays'])) {
            $text .= ' · ใช้เวลาประมาณการ '.$step['estimatedDays'].' วัน';
        }

        return $text;
    }
}
