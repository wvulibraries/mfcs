<?php
    ob_clean();
    $filepath = "/home/mfcs.lib.wvu.edu/data/exports/c/1/f/3/8/3/8/e/7/a/0/5/4/0/2/c/b/9/5/d/f/7/9/8/b/9/4/0/6/f/0/4/c1f3838e-7a05-402c-b95d-f798b9406f04/video/firstRecording.mp4";
    $fp       = fopen($filepath, 'rb');
    $size     = filesize($filepath);
    $length   = $size;
    $fi       = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $fi->file($filepath);
    $start    = 0;
    $end      = $size - 1;

    // Send the content type header
    header('Content-type: video/mp4');
    header("Accept-Ranges: bytes 0-$length");
    if (isset($_SERVER['HTTP_RANGE'])) {
        $c_start = $start;
        $c_end   = $end;
        list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
        if (strpos($range, ',') !== false) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$size");
            exit;
        }
        if ($range == '-') {
            $c_start = $size - substr($range, 1);
        }else{
            $range  = explode('-', $range);
            $c_start = $range[0];
            $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
        }
        $c_end = ($c_end > $end) ? $end : $c_end;
        if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$size");
            exit;
        }
        $start  = $c_start;
        $end    = $c_end;
        $length = $end - $start + 1;
        fseek($fp, $start);
        header('HTTP/1.1 206 Partial Content');
    }
    header("Content-Range: bytes $start-$end/$size");
    header("Content-Length: ".$length);
    $buffer = 1024 * 8;
    while(!feof($fp) && ($p = ftell($fp)) <= $end) {
        if ($p + $buffer > $end) {
            $buffer = $end - $p + 1;
        }
        set_time_limit(0);
        echo fread($fp, $buffer);
        flush();
    }
    fclose($fp);
    exit();
?>