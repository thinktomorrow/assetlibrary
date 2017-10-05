<?php

Route::get('media', 'MediaLibraryController@index')->name('media.library');
Route::post('media/upload', 'MediaController@store')->name('media.upload');
Route::post('media/remove', 'MediaController@destroy')->name('media.remove');
