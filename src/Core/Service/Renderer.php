<?php
namespace App\Core\Service;

include_once '../src/Core/KernelServiceInterface.php';
include_once '../src/Core/Service/Renderer/TemplatesCachingService.php';
include_once '../src/Core/Service/Renderer/HtmlMinificator.php';

use App\Core\KernelServiceInterface;
use Throwable;
use RuntimeException;

/* Рендерер HTML-страниц */
class Renderer implements KernelServiceInterface
{
    private string $templateDir = '../templates/';

    private array $sectionsStack = [];
    private array $currentSections = [];
    private ?string $currentSection = null;
    private ?string $layout = null;

    private bool $cachingEnabled = false;

    private bool $minifyingEnabled = true;
    private bool $isMinified = false;
    
    /* Инициализация при помощи Dependency Injection */
    public function __construct(
        private TemplatesCachingService $caching,
        private HtmlMinificator $minificator
    )
    {}

    /* Активация кэширования */
    public function enableCaching(bool $flag): void
    {
        $this->cachingEnabled = $flag;
    }

    /* Активация минификации итоговой страницы */
    public function enableMinifying(bool $flag): void
    {
        $this->minifyingEnabled = $flag;
    }
    
    /* Рендеринг страницы для отправки в тело HTTP-ответа */
    public function render(string $templatePath, array $data = []): string
    {
        // Проверка хранилища кэша шаблонов по требованию
        if ($this->cachingEnabled === true) {
            $cacheFilePath = $this->caching->getCacheFilePath($templatePath, $data);

            if ($content = $this->caching->extractFromCache($templatePath, $cacheFilePath)) {
                return $content;
            }
        }

        // Компиляция шаблона с подстановкой макросов
        // в готовую страницу
        $content = $this->compileTemplate($templatePath, $data);

        // Минификация откомпилированной страницы по требованию
        if ($this->minifyingEnabled) {
            $content = $this->minifyContent($content);
        }

        // Кэширование минифицированной страницы по требованию
        if ($this->cachingEnabled === true) {
            if ($this->isMinified === false) {
                $content = $this->minificator->minify($content);
            }

            $this->saveToCache($cacheFilePath, $content);
        }

        return $content;
    }

    /* Компиляция шаблона */
    private function compileTemplate(string $templatePath, array $data): string
    {
        $this->sectionsStack[] = $this->currentSections;
        $this->currentSections = [];
        $this->currentSection = null;
        $this->layout = null;
        
        $fullPath = $this->templateDir . $templatePath;
        
        if (!file_exists($fullPath)) {
            throw new RuntimeException("Не найден файл шаблона: " . $fullPath);
        }

        $content = $this->renderTemplate($fullPath, $data);
        
        if ($this->layout) {
            $layoutPath = $this->templateDir . $this->layout;
            $content = $this->compileTemplate($layoutPath, array_merge($data, ['content' => $content]));
        }

        $this->currentSections = array_pop($this->sectionsStack);
        
        return $content;
    }

    private function minifyContent(string $content): string
    {
        $contentMinified = $this->minificator->minify($content);
        $this->isMinified = true;

        return $contentMinified;
    }

    private function saveToCache(string $cacheFilePath, string &$content): void
    {
        $cachingContent = '';
        if ($this->isMinified === false) {
            $cachingContent = $this->minificator->minify($content);
        } else {
            $cachingContent = &$content;
        }

        $this->caching->cache($cacheFilePath, $cachingContent);
    }
    
    protected function renderTemplate(string $templatePath, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        
        try {
            include $templatePath;
        } catch (Throwable $e) {
            ob_end_clean();
            throw new RuntimeException("Ошибка рендеринга шаблона: " . $e->getMessage());
        }
        
        return ob_get_clean();
    }
    
    /* Методы подстановки макросов */
    // Экранирование симворов
    public function escape(string|array $value)
    {
        if (is_array($value)) {
            return array_map([$this, 'escapeString'], $value);
        }
        return $this->escapeString($value);
    }
    
    // Расширение шаблона до родительского
    public function extend(string $layout): void
    {
        $this->layout = $layout;
    }
    
    // Установка родительского шаблона (вёрстки)
    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }
    
    // Начало секции кода
    public function startSection(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }
    
    // Конец секции кода
    public function endSection(): void
    {
        if ($this->currentSection) {
            $this->currentSections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }
    
    // Вывод секции из дочернего шаблона
    public function section(string $name): string
    {
        if (isset($this->currentSections[$name])) {
            return $this->currentSections[$name];
        }
        
        foreach (array_reverse($this->sectionsStack) as $sections) {
            if (isset($sections[$name])) {
                return $sections[$name];
            }
        }
        
        return '';
    }

    // Встраивание шаблонного компонента
    public function includeComponent(string $componentPath, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        include $this->templateDir . $componentPath;
    }

    // Подготовка цвета для элемента о статусе
    public function getStatusColor(string $status, array $statusConfig): string
    {
        foreach ($statusConfig as $typeConfig) {
            if (isset($typeConfig[$status])) {
                return $typeConfig[$status]['color'];
            }
        }
        
        return '#858796';
    }

    // Подготовка значения для элемента о статусе
    public function getStatusText(string $status, array $statusConfig): string
    {
        foreach ($statusConfig as $typeConfig) {
            if (isset($typeConfig[$status])) {
                return $typeConfig[$status]['text'];
            }
        }
        
        return $status;
    }

    /* Методы подстановки макросов */
    private function escapeString(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}