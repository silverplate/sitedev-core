<?php

require_once realpath(dirname(__FILE__) . '/../../../core/src') . '/libs.php';

initSettings();
\App\Cms\Back\Office::bootstrap();
