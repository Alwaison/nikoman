
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn (): JsonResponse => response()->json(['status' => 'ok']))->name('health');
});
