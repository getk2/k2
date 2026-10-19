<?php
/**
 * Build an installable K2 package from this repository.
 *
 * Usage, from the repository root:
 *   php build/build.php
 *
 * Reads the version from k2.xml and writes build/com_k2_v{version}.zip.
 * The archive root is the repository itself, so Joomla finds k2.xml,
 * the component, modules, plugins, and media in the paths the manifest expects.
 */

$root = dirname(__DIR__);
$manifestFile = $root . DIRECTORY_SEPARATOR . 'k2.xml';
$manifest = file_get_contents($manifestFile);

if ($manifest === false || !preg_match('/<version>([^<]+)<\/version>/', $manifest, $match)) {
    fwrite(STDERR, "Could not read the version from k2.xml\n");
    exit(1);
}

$version = trim($match[1]);
$zipPath = __DIR__ . DIRECTORY_SEPARATOR . 'com_k2_v' . $version . '.zip';

$skipTop = array('build', '.git', '.github', '.idea');
$skipNames = array('.gitignore', '.ftpconfig', '.project', '.DS_Store', 'Thumbs.db');

if (file_exists($zipPath) && !unlink($zipPath)) {
    fwrite(STDERR, "Could not replace {$zipPath}\n");
    exit(1);
}

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
    fwrite(STDERR, "Could not create {$zipPath}\n");
    exit(1);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$count = 0;
$rootPrefix = rtrim($root, '/\\') . DIRECTORY_SEPARATOR;

foreach ($iterator as $file) {
    $path = $file->getPathname();
    $relative = substr($path, strlen($rootPrefix));
    $relative = str_replace('\\', '/', $relative);
    $parts = explode('/', $relative);

    if (in_array($parts[0], $skipTop, true)) {
        continue;
    }

    $skip = false;
    foreach ($parts as $part) {
        if ($part === '.git' || $part === '.idea' || in_array($part, $skipNames, true)) {
            $skip = true;
            break;
        }
    }
    if ($skip) {
        continue;
    }

    $zip->addFile($path, $relative);
    $count++;
}

if (!$zip->close()) {
    fwrite(STDERR, "Could not finish {$zipPath}\n");
    exit(1);
}

$size = filesize($zipPath);
echo "Wrote {$zipPath}\n";
echo "Version {$version}, {$count} files, " . number_format($size) . " bytes\n";
