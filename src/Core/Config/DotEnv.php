<?php
namespace App\Core;

include_once '../src/Core/ConfigInterface.php';

class DotEnv implements ConfigInterface
{
    private static array $data = [];

    public static function init(string $filePath): void
    {
        $data = [];

        if (!file_exists($filePath)) {
            throw new \RuntimeException("Файл .env не найден по пути " . $filePath);
        }
    
        $lines = file($filePath, (FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        
        foreach ($lines as $line) {
            if ((strpos(trim($line), '#') === 0) || (strpos(trim($line), ';') === 0)) {
                continue;
            }
    
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                
                $name = trim($name);
                $value = trim($value);
                
                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                } elseif (preg_match('/^\'(.*)\'$/', $value, $matches)) {
                    $value = $matches[1];
                }
                
                $value = str_replace('\\n', "\n", $value);
                $value = str_replace('\\r', "\r", $value);
                $value = str_replace('\\t', "\t", $value);
                
                $data[$name] = $value;
            }
        }

        self::$data = $data;
    }

    public static function getData(): array
    {
        return self::$data;
    }

    public static function update(string $filePath, array $settings): void
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException('.env file not found');
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $newContent = [];

        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0 || strpos(trim($line), ';') === 0) {
                $newContent[] = $line;
                continue;
            }

            $pos = strpos($line, '=');
            if ($pos === false) {
                $newContent[] = $line;
                continue;
            }

            $key = trim(substr($line, 0, $pos));
            if (array_key_exists($key, $settings)) {
                $newContent[] = $key . '=' . $settings[$key];
                unset($settings[$key]);
            } else {
                $newContent[] = $line;
            }
        }

        foreach ($settings as $key => $value) {
            $newContent[] = $key . '=' . $value;
        }

        file_put_contents($filePath, implode(PHP_EOL, $newContent));
        self::$data = $settings;
    }
}