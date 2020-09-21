<?php

use Agenciafmd\Media\Http\Controllers\UploadController;

Route::post('upload', [UploadController::class, 'index'])
    ->name('admix.upload.index');
Route::post('destroy', [UploadController::class, 'destroy'])
    ->name('admix.upload.destroy');
Route::get('meta/{uuid?}', [UploadController::class, 'metaForm'])
    ->name('admix.upload.meta');
Route::post('meta/{uuid?}', [UploadController::class, 'metaPost'])
    ->name('admix.upload.meta.post');
Route::post('sort', [UploadController::class, 'sort'])
    ->name('admix.upload.sort');
Route::post('medium', [UploadController::class, 'medium'])
    ->name('admix.upload.medium');

//// resize route
//Route::get('/media/{path}', 'MediaController@show')
//    ->name('media.show')
//    ->where('path', '.*');