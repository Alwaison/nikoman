<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\MemberController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn (): JsonResponse => response()->json(['status' => 'ok']))->name('health');

    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    Route::get('/members/{memberId}', [MemberController::class, 'show'])->whereUuid('memberId')->name('members.show');
    Route::put('/members/{memberId}', [MemberController::class, 'update'])->whereUuid('memberId')->name('members.update');
});
