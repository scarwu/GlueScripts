#!/usr/bin/env php
<?php

$root = $_SERVER['PWD'];
$srcPath = (true === isset($_SERVER['argv'][1]) && '' !== $_SERVER['argv'][1]) ? $_SERVER['argv'][1] : null;
$destPath = (true === isset($_SERVER['argv'][2]) && '' !== $_SERVER['argv'][2]) ? $_SERVER['argv'][2] : null;

if (false == isset($srcPath)) {
    return;
}

$srcPath = "{$root}/{$srcPath}";

if (false === file_exists($srcPath)) {
    return;
}

if (false == isset($destPath)) {
    $destPath = "{$srcPath}/output";
} else {
    $destPath = "{$root}/{$destPath}";
}

if (false === file_exists($destPath)) {
    @mkdir($destPath, 0775);
}

$srcPath = realpath($srcPath);
$destPath = realpath($destPath);

echo "In:{$srcPath}\n";
echo "Out:{$destPath}\n";

$dir = opendir($srcPath);

while ($file = readdir($dir)) {
    if ('.' === $file || '..' === $file) {
        continue;
    }

    if (false === is_file("{$srcPath}/{$file}")) {
        continue;
    }

    $srcImg = new Imagick("{$srcPath}/{$file}");
    $width = null;
    $height = null;

    if ($srcImg->getImageWidth() < $srcImg->getImageHeight()) {
        $height = 1500;
        $width = ($height * $srcImg->getImageWidth()) / $srcImg->getImageHeight();
    } else {
        $width = 1500;
        $height = ($width * $srcImg->getImageHeight()) / $srcImg->getImageWidth();
    }

    $srcImg->scaleImage($width, $height);

    $border = new ImagickDraw();
    $border->setFillColor('none');
    $border->setStrokeColor(new ImagickPixel('rgba(255, 255, 255, 1)'));
    $border->setStrokeWidth(1600);
    $border->setStrokeAntialias(false);
    $border->rectangle(1600, 1600, 0, 0);

    $destImg = new Imagick("{$srcPath}/{$file}");
    $destImg->scaleImage(1600, 1600);
    // $destImg->newImage(1600, 1600, new ImagickPixel('none'));
    $destImg->drawImage($border);
    $destImg->compositeImage($srcImg, Imagick::COMPOSITE_DEFAULT, (1600 - $width) / 2, (1600 - $height) / 2);
    $destImg->setCompressionQuality(100);
    $destImg->setImageFormat('jpg');

    file_put_contents("{$destPath}/{$file}", $destImg->getImageBlob());

    echo "{$srcPath}/{$file} > {$destPath}/{$file}\n";
}

closedir($dir);
