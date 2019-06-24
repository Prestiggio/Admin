<?php
Route::get('ckeditor/plugins/{path}/plugin.js', 'CkeditorController@plugin')->where('path', '.+');
Route::get('ckeditor/plugins/{path}', 'CkeditorController@index')->where('path', '.+');
