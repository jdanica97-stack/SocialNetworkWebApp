<?php
// bin/lint.php
// Project-wide automated PHP syntax validator for SMCC Connect
// Scans application, configuration, and test files while excluding vendor/ and generated folders.

$rootDir = realpath(__DIR__ . '/..');
$phpBinary = PHP_BINARY;

// Fallback if PHP_BINARY is not pointing to xampp php
if (!file_exists($phpBinary) || strpos($phpBinary, 'php') === false) {
    $phpBinary = 'c:/xampp/php/php.exe';
}

echo "=== SMCC Connect PHP Syntax Checker ===" . PHP_EOL;
echo "Root directory: " . $rootDir . PHP_EOL;
echo "PHP Binary:     " . $phpBinary . PHP_EOL . PHP_EOL;

$scanDirs = ['app', 'config', 'public', 'tests'];
$files = [];

foreach ($scanDirs as $dir) {
    $path = $rootDir . DIRECTORY_SEPARATOR . $dir;
    if (!is_dir($path)) continue;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
            $files[] = $file->getRealPath();
        }
    }
}

// Include root files if any
$rootFiles = glob($rootDir . '/*.php');
foreach ($rootFiles as $rf) {
    $files[] = realpath($rf);
}

$files = array_unique($files);
sort($files);

$total = count($files);
$passed = 0;
$failed = 0;
$errors = [];

echo "Found {$total} PHP files to lint." . PHP_EOL . PHP_EOL;

foreach ($files as $filePath) {
    $relPath = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $filePath);
    $cmd = escapeshellarg($phpBinary) . ' -l ' . escapeshellarg($filePath) . ' 2>&1';
    $output = [];
    $returnVar = 0;
    exec($cmd, $output, $returnVar);

    if ($returnVar === 0) {
        $passed++;
        echo " [OK]   {$relPath}" . PHP_EOL;
    } else {
        $failed++;
        $errors[$relPath] = implode(PHP_EOL, $output);
        echo " [FAIL] {$relPath}" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Lint Summary ===" . PHP_EOL;
echo "Total files checked: {$total}" . PHP_EOL;
echo "Passed:              {$passed}" . PHP_EOL;
echo "Failed:              {$failed}" . PHP_EOL;

if ($failed > 0) {
    echo PHP_EOL . "Syntax Error Details:" . PHP_EOL;
    foreach ($errors as $path => $err) {
        echo "--- {$path} ---" . PHP_EOL;
        echo $err . PHP_EOL;
    }
    exit(1);
} else {
    echo PHP_EOL . "All files passed syntax validation with zero errors!" . PHP_EOL;
    exit(0);
}
