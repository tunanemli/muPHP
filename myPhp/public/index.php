<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Framework'ü başlat
$app = Core\Framework::getInstance();

// Rotaları yükle
require_once __DIR__ . '/../routes/web.php';

// Uygulamayı çalıştır
$app->run();