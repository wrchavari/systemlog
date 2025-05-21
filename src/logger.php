<?php

    function log_msg($msg, $level = 'INFO') {
        $dir = __DIR__ . '/../logs/system/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $file = $dir . date('Y-m-d') . '_system.log';
        $date = date('Y-m-d H:i:s');
        file_put_contents($file, "[$date] [$level] $msg\n", FILE_APPEND);
    }