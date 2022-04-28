<?php
Route::get('{page}.html', 'PublicController@static_page')->where('page', '.+');
Route::get('ckeditor/plugins/{path}/plugin.js', 'CkeditorController@plugin')->where('path', '.+');
Route::get('ckeditor/plugins/{path}', 'CkeditorController@index')->where('path', '.+');
Route::get('languages/{lang}.js', 'PublicController@translation')->where('lang', '.+');
