<?php

namespace Azuriom\Plugin\Marketplace\Support;

class ResourceFilePolicy
{
    public const DEFAULT_ALLOWED = ['zip', 'rar', '7z', 'jar'];

    public const FORBIDDEN = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'pht', 'phar', 'phps', 'inc',
        'js', 'mjs', 'cjs', 'jsx', 'ts', 'tsx',
        'html', 'htm', 'xhtml', 'shtml', 'shtm', 'svg',
        'blade', 'twig', 'tpl',
        'env', 'htaccess', 'htpasswd', 'ini', 'conf', 'config',
        'sh', 'bash', 'zsh', 'fish', 'cmd', 'bat', 'ps1', 'vbs', 'wsf',
        'cgi', 'pl', 'py', 'pyc', 'rb', 'lua',
        'exe', 'com', 'msi', 'dll', 'so', 'dylib', 'app', 'scr',
    ];

    public function parse(string $extensions): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn (string $extension) => strtolower(ltrim(trim($extension), '.')),
            preg_split('/[\s,;]+/', $extensions) ?: []
        ))));
    }

    public function invalid(array $extensions): array
    {
        return array_values(array_filter(
            $extensions,
            fn (string $extension) => preg_match('/^[a-z0-9]+$/', $extension) !== 1
        ));
    }

    public function forbidden(array $extensions): array
    {
        return array_values(array_intersect($extensions, self::FORBIDDEN));
    }

    public function allowedExtensions(): array
    {
        $extensions = $this->parse((string) setting(
            'marketplace.allowed_extensions',
            implode(',', self::DEFAULT_ALLOWED)
        ));
        $extensions = array_values(array_diff($extensions, $this->invalid($extensions), self::FORBIDDEN));

        return $extensions === [] ? self::DEFAULT_ALLOWED : $extensions;
    }

    public function acceptAttribute(): string
    {
        return implode(',', array_map(
            fn (string $extension) => '.'.$extension,
            $this->allowedExtensions()
        ));
    }
}
