<?php
namespace App\Core\Service;

include_once '../src/Core/KernelServiceInterface.php';

use App\Core\KernelServiceInterface;

class HtmlMinificator implements KernelServiceInterface
{
    public function __construct()
    {}

    public function minify(string $html)
    {
        // Удаление комментариев HTML
        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);

        // Удаление комментариев CSS/JS
        $html = preg_replace('/\/\*[\s\S]*?\*\/|([^\\:]|^)\/\/.*$/m', '', $html);
        
        // Удаление лишних пробелов и переносов строк
        $html = preg_replace('/\s{2,}/', ' ', $html);
        $html = preg_replace('/\s*([<>])\s*/', '$1', $html);
        
        // Удаление пробелов перед закрывающими тегами
        $html = preg_replace('/\s+>/', '>', $html);
        
        // Удаление пробелов после открывающих тегов
        $html = preg_replace('/>\s+/', '>', $html);
        
        return trim($html);
    }
}