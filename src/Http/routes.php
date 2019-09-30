<?php
Route::get('ckeditor/plugins/{path}/plugin.js', 'CkeditorController@plugin')->where('path', '.+');
Route::get('ckeditor/plugins/{path}', 'CkeditorController@index')->where('path', '.+');
Route::get('languages/{lang}.js', 'PublicController@translation')->where('lang', '.+');
