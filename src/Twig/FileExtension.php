<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigTest;

class FileExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('file_size', [$this, 'getFileSize']),
            new TwigFilter('format_bytes', [$this, 'formatBytes']),
        ];
    }
    
    public function getTests(): array
    {
        return [
            new TwigTest('file_exists', [$this, 'fileExists']),
        ];
    }
    
    public function getFileSize(string $filePath): int
    {
        if (file_exists($filePath)) {
            return filesize($filePath);
        }
        return 0;
    }
    
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    public function fileExists(string $filePath): bool
    {
        return file_exists($filePath);
    }
}
