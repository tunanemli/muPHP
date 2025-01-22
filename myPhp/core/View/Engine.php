<?php

namespace Core\View;

class Engine
{
    protected array $compiledPaths = [];
    protected string $cachePath;
    protected array $extensions = ['.blade.php', '.php'];
    protected array $directives = [];
    
    public function __construct(string $cachePath)
    {
        $this->cachePath = rtrim($cachePath, '/');
        $this->registerBaseDirectives();
    }
    
    protected function registerBaseDirectives(): void
    {
        // Temel direktifleri kaydet
        $this->directive('if', fn($expression) => "<?php if($expression): ?>");
        $this->directive('else', fn() => "<?php else: ?>");
        $this->directive('elseif', fn($expression) => "<?php elseif($expression): ?>");
        $this->directive('endif', fn() => "<?php endif; ?>");
        
        $this->directive('foreach', fn($expression) => "<?php foreach($expression): ?>");
        $this->directive('endforeach', fn() => "<?php endforeach; ?>");
        
        $this->directive('for', fn($expression) => "<?php for($expression): ?>");
        $this->directive('endfor', fn() => "<?php endfor; ?>");
        
        $this->directive('while', fn($expression) => "<?php while($expression): ?>");
        $this->directive('endwhile', fn() => "<?php endwhile; ?>");
        
        $this->directive('isset', fn($expression) => "<?php if(isset($expression)): ?>");
        $this->directive('endisset', fn() => "<?php endif; ?>");
        
        $this->directive('empty', fn($expression) => "<?php if(empty($expression)): ?>");
        $this->directive('endempty', fn() => "<?php endif; ?>");
        
        $this->directive('include', fn($expression) => "<?php echo \$this->render($expression); ?>");
        
        $this->directive('csrf', fn() => "<?php echo csrf_field(); ?>");
        $this->directive('method', fn($expression) => "<?php echo method_field($expression); ?>");
    }
    
    public function directive(string $name, callable $handler): void
    {
        $this->directives[$name] = $handler;
    }
    
    public function render(string $view, array $data = []): string
    {
        $compiledPath = $this->getCompiledPath($view);
        
        if (!$this->isExpired($view, $compiledPath)) {
            return $this->evaluatePath($compiledPath, $data);
        }
        
        $content = $this->getViewContent($view);
        $compiled = $this->compile($content);
        
        file_put_contents($compiledPath, $compiled);
        
        return $this->evaluatePath($compiledPath, $data);
    }
    
    protected function compile(string $content): string
    {
        $result = $content;
        
        // Echo statements
        $result = preg_replace('/\{\{\s*(.+?)\s*\}\}/', '<?php echo e($1); ?>', $result);
        $result = preg_replace('/\{!!\s*(.+?)\s*!!\}/', '<?php echo $1; ?>', $result);
        
        // Direktifleri işle
        foreach ($this->directives as $name => $handler) {
            $pattern = "/\@{$name}(\s*\(.*?\))?/";
            $result = preg_replace_callback($pattern, function($matches) use ($handler) {
                $expression = isset($matches[1]) ? trim($matches[1], '() ') : '';
                return $handler($expression);
            }, $result);
        }
        
        return $result;
    }
    
    protected function evaluatePath(string $path, array $data): string
    {
        extract($data);
        
        ob_start();
        include $path;
        return ob_get_clean();
    }
    
    protected function getCompiledPath(string $view): string
    {
        $hash = md5($view);
        return "{$this->cachePath}/{$hash}.php";
    }
    
    protected function isExpired(string $view, string $compiled): bool
    {
        if (!file_exists($compiled)) {
            return true;
        }
        
        return filemtime($this->findView($view)) > filemtime($compiled);
    }
    
    protected function findView(string $view): string
    {
        $view = str_replace('.', '/', $view);
        
        foreach ($this->extensions as $extension) {
            $path = resource_path("views/{$view}{$extension}");
            if (file_exists($path)) {
                return $path;
            }
        }
        
        throw new \RuntimeException("View [{$view}] not found.");
    }
    
    protected function getViewContent(string $view): string
    {
        return file_get_contents($this->findView($view));
    }
} 