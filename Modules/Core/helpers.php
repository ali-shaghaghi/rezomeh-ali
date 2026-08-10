<?php

if (!function_exists('module_path')) {
    /**
     * Get the path to a module.
     */
    function module_path(string $moduleName, string $path = ''): string
    {
        return base_path('Modules/' . $moduleName) . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('module_config_path')) {
    /**
     * Get the config path for a module.
     */
    function module_config_path(string $moduleName, string $fileName = ''): string
    {
        return module_path($moduleName, 'config' . DIRECTORY_SEPARATOR . $fileName);
    }
}

if (!function_exists('module_view_path')) {
    /**
     * Get the view path for a module.
     */
    function module_view_path(string $moduleName, string $path = ''): string
    {
        return module_path($moduleName, 'resources/views' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('module_lang_path')) {
    /**
     * Get the lang path for a module.
     */
    function module_lang_path(string $moduleName, string $path = ''): string
    {
        return module_path($moduleName, 'resources/lang' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('module_route_path')) {
    /**
     * Get the routes path for a module.
     */
    function module_route_path(string $moduleName, string $fileName = ''): string
    {
        return module_path($moduleName, 'routes' . DIRECTORY_SEPARATOR . $fileName);
    }
}

if (!function_exists('admin_logo_url')) {
    /**
     * Get the admin panel logo URL.
     * Checks for custom uploaded logo first, falls back to default.
     */
    function admin_logo_url(): string
    {
        $settingsPath = storage_path('app/settings.json');
        if (file_exists($settingsPath)) {
            $settings = json_decode(file_get_contents($settingsPath), true);
            if (!empty($settings['logo_path']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['logo_path'])) {
                $url = asset('storage/' . $settings['logo_path']);
                $fullPath = storage_path('app/public/' . $settings['logo_path']);
                if (file_exists($fullPath)) {
                    $url .= '?v=' . filemtime($fullPath);
                }
                return $url;
            }
        }
        return asset('img/logo.png');
    }
}

if (!function_exists('user_avatar_url')) {
    /**
     * Get the user's avatar URL.
     * Falls back to default avatar if none uploaded.
     */
    function user_avatar_url(?object $user = null): string
    {
        $user = $user ?? auth()->user();
        if ($user && !empty($user->avatar) && \Illuminate\Support\Facades\Storage::disk('public')->exists('avatars/' . $user->avatar)) {
            $path = 'avatars/' . $user->avatar;
            $url = asset('storage/' . $path);
            $fullPath = storage_path('app/public/' . $path);
            if (file_exists($fullPath)) {
                $url .= '?v=' . filemtime($fullPath);
            }
            return $url;
        }
        return asset('img/avatar.svg');
    }
}