<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

if (!function_exists('q8_get_custom_css_scopes')) {
    function q8_get_custom_css_scopes()
    {
        $scopes = array(
            'site' => array(
                'label' => '前台站点',
                'icon' => 'fa-home',
                'description' => '作用于前台模板主页面，适合首页、商品页和公共展示区域。',
                'hook' => 'suisui_custom_css_content_site'
            ),
            'user' => array(
                'label' => '用户中心',
                'icon' => 'fa-user-circle',
                'description' => '作用于用户中心、登录注册和个人页，不影响后台管理端。',
                'hook' => 'suisui_custom_css_content_user'
            ),
            'admin' => array(
                'label' => '后台管理',
                'icon' => 'fa-dashboard',
                'description' => '作用于后台管理界面，适合微调布局、卡片和菜单样式。',
                'hook' => 'suisui_custom_css_content_admin'
            )
        );

        return apply_filters('suisui_custom_css_scopes', $scopes);
    }
}

if (!function_exists('q8_normalize_custom_css_scope')) {
    function q8_normalize_custom_css_scope($scope)
    {
        $scope = strtolower(trim((string)$scope));
        $scopes = q8_get_custom_css_scopes();
        return isset($scopes[$scope]) ? $scope : '';
    }
}

if (!function_exists('q8_get_custom_css_keys')) {
    function q8_get_custom_css_keys($scope)
    {
        $scope = q8_normalize_custom_css_scope($scope);
        if ($scope === '') {
            return array('enabled' => '', 'content' => '');
        }

        return array(
            'enabled' => 'custom_css_' . $scope . '_enabled',
            'content' => 'custom_css_' . $scope . '_content'
        );
    }
}

if (!function_exists('q8_get_custom_css_state')) {
    function q8_get_custom_css_state($scope)
    {
        global $conf;

        $keys = q8_get_custom_css_keys($scope);
        if ($keys['enabled'] === '') {
            return array('enabled' => false, 'content' => '');
        }

        $enabledRaw = isset($conf[$keys['enabled']]) ? $conf[$keys['enabled']] : '0';
        $content = isset($conf[$keys['content']]) ? str_replace(array("\r\n", "\r"), "\n", (string)$conf[$keys['content']]) : '';

        return array(
            'enabled' => in_array((string)$enabledRaw, array('1', 'true', 'on', 'yes'), true),
            'content' => $content
        );
    }
}

if (!function_exists('q8_is_custom_css_enabled')) {
    function q8_is_custom_css_enabled($scope)
    {
        $state = q8_get_custom_css_state($scope);
        return !empty($state['enabled']);
    }
}

if (!function_exists('q8_get_custom_css')) {
    function q8_get_custom_css($scope)
    {
        $scope = q8_normalize_custom_css_scope($scope);
        if ($scope === '') {
            return '';
        }

        $state = q8_get_custom_css_state($scope);
        $css = $state['enabled'] ? $state['content'] : '';
        $context = array(
            'scope' => $scope,
            'enabled' => $state['enabled']
        );

        $css = apply_filters('suisui_custom_css_content', $css, $scope, $context);
        $css = apply_filters('suisui_custom_css_content_' . $scope, $css, $context);

        return trim((string)$css);
    }
}

if (!function_exists('q8_escape_style_content')) {
    function q8_escape_style_content($css)
    {
        return str_replace('</style', '<\/style', (string)$css);
    }
}

if (!function_exists('q8_render_custom_css')) {
    function q8_render_custom_css($scope)
    {
        $scope = q8_normalize_custom_css_scope($scope);
        if ($scope === '') {
            return '';
        }

        $css = q8_get_custom_css($scope);
        if ($css === '') {
            return '';
        }

        $context = array(
            'scope' => $scope,
            'enabled' => true
        );
        $safeScope = htmlspecialchars($scope, ENT_QUOTES, 'UTF-8');
        $markup = '<style id="suisui-custom-css-' . $safeScope . '" data-scope="' . $safeScope . '">' . "\n" . q8_escape_style_content($css) . "\n" . '</style>';
        $markup = apply_filters('suisui_custom_css_markup', $markup, $scope, $css, $context);
        $markup = apply_filters('suisui_custom_css_markup_' . $scope, $markup, $css, $context);

        return (string)$markup;
    }
}

if (!function_exists('q8_inject_custom_css_into_html')) {
    function q8_inject_custom_css_into_html($html, $scope)
    {
        $markup = q8_render_custom_css($scope);
        if ($markup === '' || !is_string($html) || $html === '') {
            return $html;
        }

        if (stripos($html, '</head>') !== false) {
            $html = preg_replace('/<\/head>/i', $markup . "\n</head>", $html, 1);
        } else {
            $html = $markup . "\n" . $html;
        }

        $html = apply_filters('suisui_custom_css_injected_html', $html, $scope, $markup);
        $html = apply_filters('suisui_custom_css_injected_html_' . $scope, $html, $markup);

        return $html;
    }
}

if (!function_exists('q8_save_custom_css_value')) {
    function q8_save_custom_css_value($key, $value)
    {
        global $DB;

        if (function_exists('saveSetting')) {
            return saveSetting($key, $value);
        }

        $exists = intval($DB->getColumn("SELECT COUNT(*) FROM pre_config WHERE k=:key LIMIT 1", array(':key' => $key)));
        if ($exists > 0) {
            return $DB->exec("UPDATE pre_config SET v=:value WHERE k=:key", array(':key' => $key, ':value' => $value));
        }

        return $DB->exec("INSERT INTO pre_config (k, v) VALUES (:key, :value)", array(':key' => $key, ':value' => $value));
    }
}

if (!function_exists('q8_save_custom_css_settings')) {
    function q8_save_custom_css_settings($input)
    {
        global $CACHE, $conf;

        $scopes = q8_get_custom_css_scopes();
        $payload = array();

        foreach ($scopes as $scope => $meta) {
            $keys = q8_get_custom_css_keys($scope);
            $payload[$keys['enabled']] = !empty($input[$keys['enabled']]) ? '1' : '0';
            $payload[$keys['content']] = isset($input[$keys['content']]) ? str_replace(array("\r\n", "\r"), "\n", (string)$input[$keys['content']]) : '';
        }

        $payload = apply_filters('suisui_custom_css_save_payload', $payload, $input);

        foreach ($payload as $key => $value) {
            q8_save_custom_css_value($key, $value);
        }

        if (isset($CACHE) && is_object($CACHE)) {
            $CACHE->clear();
            $conf = $CACHE->pre_fetch();
            $GLOBALS['conf'] = $conf;
        }

        do_action('suisui_custom_css_saved', $payload);

        return $payload;
    }
}
