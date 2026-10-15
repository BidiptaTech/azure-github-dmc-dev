<?php

namespace App\Services;

use RuntimeException;

class EnvWriter
{
    /**
     * @param  array<string, string|null>  $values
     */
    public function setMany(array $values): void
    {
        $path = base_path('.env');

        if (! is_file($path)) {
            throw new RuntimeException('.env file not found.');
        }

        if (! is_writable($path)) {
            throw new RuntimeException('.env file is not writable.');
        }

        $content = file_get_contents($path);

        foreach ($values as $key => $value) {
            $line = $key.'='.$this->escapeValue($value);
            $pattern = '/^'.preg_quote($key, '/').'=.*/m';

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $line, $content);
            } else {
                $content = rtrim($content).PHP_EOL.$line.PHP_EOL;
            }
        }

        file_put_contents($path, $content);
    }

    private function escapeValue(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (preg_match('/[\s#="\'\\\\]/', $value)) {
            return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
        }

        return $value;
    }
}
