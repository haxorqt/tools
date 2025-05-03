<?php
set_time_limit(0);
ini_set('memory_limit', '512M');

function scanShellBackdoor($dir) {
    $backdoorPatterns = [
        'system(', 'exec(', 'shell_exec(', 'passthru(', 'popen(', 'proc_open(', 'eval(', 'base64_decode(',
        'gzinflate(', 'str_rot13(', 'file_get_contents("php://input")'
    ];

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $suspiciousFiles = [];

    foreach ($files as $file) {
        if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($file->getRealPath());
            
            foreach ($backdoorPatterns as $pattern) {
                if (strpos($content, $pattern) !== false) {
                    $suspiciousFiles[] = $file->getRealPath();
                    break;
                }
            }
        }
    }

    return $suspiciousFiles;
}

$websiteRoot = __DIR__; // Ubah sesuai direktori root website jika perlu
$suspiciousFiles = scanShellBackdoor($websiteRoot);

if (!empty($suspiciousFiles)) {
    echo "<h2>🚨 Ditemukan kemungkinan shell backdoor di:</h2><ul>";
    foreach ($suspiciousFiles as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
} else {
    echo "<h2>✅ Tidak ditemukan shell backdoor.</h2>";
}
?>
