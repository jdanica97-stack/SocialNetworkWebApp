<?php
// Root entry point forwarding to public front controller
// Ensures compatibility whether accessed via web server root or subdirectory without mod_rewrite
require_once __DIR__ . '/public/index.php';
