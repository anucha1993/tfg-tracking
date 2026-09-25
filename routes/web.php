<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/healthz', fn () => response()->json(['ok' => true]));

// ---------- หน้าลูกค้า ----------
Route::get('/t/{token}', [TrackingController::class, 'show'])
    ->where('token', '.*')
    ->middleware('throttle:60,1');

// QR ของลิงก์ (ต้องมี token ที่ถูกต้องเท่านั้น) — ใช้แนบเอกสาร/อีเมล
Route::get('/qr/{token}.png', [TrackingController::class, 'qr'])
    ->where('token', '.*')
    ->middleware('throttle:60,1');

// ---------- API ภายใน (เรียกจากปุ่มใน CRM) ----------
Route::get('/api/admin/link', [AdminController::class, 'link'])
    ->middleware('throttle:120,1');

Route::fallback(function () {
    return response()->view('message', [
        'title' => __('ไม่พบหน้านี้'),
        'message' => __('กรุณาสแกน QR Code จากเอกสารอีกครั้ง'),
        'company' => config('tracking.company'),
    ], 404);
});
