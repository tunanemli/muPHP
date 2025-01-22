<?php

namespace Core\Providers;

use Core\View\View;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Custom direktifleri kaydet
        View::directive('auth', function() {
            return "<?php if(auth()->check()): ?>";
        });
        
        View::directive('endauth', function() {
            return "<?php endif; ?>";
        });
        
        View::directive('guest', function() {
            return "<?php if(!auth()->check()): ?>";
        });
        
        View::directive('endguest', function() {
            return "<?php endif; ?>";
        });
    }
    
    public function boot(): void
    {
        // Cache dizinini oluştur
        if (!is_dir(storage_path('framework/views'))) {
            mkdir(storage_path('framework/views'), 0755, true);
        }
    }
} 