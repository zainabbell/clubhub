<?php
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax']);
session_start();
require_once __DIR__.'/functions.php';
require_once __DIR__.'/users.php';
require_once __DIR__.'/auth.php';
