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

// DEBUG ชั่วคราว: เช็คค่า config จริงที่ runtime เห็น (ลบทิ้งหลังแก้ปัญหาเสร็จ)
Route::get('/debug-config', function (\Illuminate\Http\Request $request) {
    if ($request->header('X-Admin-Key') !== config('tracking.admin_api_key')) {
        abort(401);
    }

    return response()->json([
        'app_env' => config('app.env'),
        'app_debug' => config('app.debug'),
        'app_url' => config('app.url'),
        'public_base_url' => config('tracking.public_base_url'),
        'env_file_exists' => file_exists(base_path('.env')),
        'env_file_mtime' => file_exists(base_path('.env')) ? date('Y-m-d H:i:s', filemtime(base_path('.env'))) : null,
        'config_cached' => app()->configurationIsCached(),
    ]);
});

Route::fallback(function () {
    return response()->view('message', [
        'title' => 'ไม่พบหน้านี้',
        'message' => 'กรุณาสแกน QR Code จากเอกสารอีกครั้ง',
        'company' => config('tracking.company'),
    ], 404);
});
