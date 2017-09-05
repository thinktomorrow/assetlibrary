<?php



Route::get('media', 'Media\MediaLibraryController@index')->name('media.library');
Route::post('media/upload', 'Media\MediaController@store')->name('media.upload');
Route::post('media/remove', 'Media\MediaController@destroy')->name('media.remove');