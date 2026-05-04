<?php

use App\Models\TicketAdjunto;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('landing');
});

Route::get('/docs', function () {
    return view('docs.index');
});

Route::get('/docs/{role}', function (string $role) {
    $allowed = ['super-admin', 'admin-empresa', 'usuario', 'agente-helpdesk', 'solo-lectura'];
    abort_unless(in_array($role, $allowed), 404);

    return view("docs.roles.{$role}");
});

Route::get('/select-empresa', function () {
    if (! auth()->check()) {
        return redirect('/admin/login');
    }

    return view('filament.pages.auth.select-empresa-standalone');
})->middleware(['web'])->name('filament.admin.select-empresa');

Route::get('/tickets/adjuntos/{adjunto}', function (TicketAdjunto $adjunto) {
    $disk = Storage::disk('tickets');

    abort_unless($disk->exists($adjunto->nombre_almacenado), 404);

    return $disk->download($adjunto->nombre_almacenado, $adjunto->nombre_original);
})->middleware('auth')->name('tickets.adjunto.download');

Route::get('/logos/{path}', function (string $path) {
    $disk = Storage::disk('logos');

    abort_unless($disk->exists($path), 404);

    return response($disk->get($path), 200, [
        'Content-Type' => $disk->mimeType($path),
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->middleware('auth')->where('path', '.*')->name('logos.show');

Route::get('/politicas/{politica}/pdf', [\App\Http\Controllers\PoliticaPdfController::class, 'download'])
    ->middleware('auth')
    ->name('politicas.pdf');

Route::get('/politicas/{politica}/nda/{aceptacion}/pdf', [\App\Http\Controllers\PoliticaPdfController::class, 'downloadNdaFirmante'])
    ->middleware('auth')
    ->name('politicas.nda-firmante-pdf');

Route::get('/politicas/{politica}/firmantes/pdf', [\App\Http\Controllers\PoliticaPdfController::class, 'downloadResumenFirmantes'])
    ->middleware('auth')
    ->name('politicas.resumen-firmantes-pdf');

Route::get('/politicas/{politica}/firmados/zip', [\App\Http\Controllers\PoliticaPdfController::class, 'downloadAllFirmadosZip'])
    ->middleware('auth')
    ->name('politicas.firmados-zip');
