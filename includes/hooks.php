<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

if (!function_exists('q8_get_hook_store')) {
    function &q8_get_hook_store()
    {
        if (!isset($GLOBALS['_q8_hook_store']) || !is_array($GLOBALS['_q8_hook_store'])) {
            $GLOBALS['_q8_hook_store'] = array(
                'action' => array(),
                'filter' => array()
            );
        }

        return $GLOBALS['_q8_hook_store'];
    }
}

if (!function_exists('q8_normalize_hook_name')) {
    function q8_normalize_hook_name($hook)
    {
        return strtolower(trim((string)$hook));
    }
}

if (!function_exists('q8_register_hook')) {
    function q8_register_hook($type, $hook, $callback, $priority)
    {
        if (!is_callable($callback)) {
            return false;
        }

        $hook = q8_normalize_hook_name($hook);
        if ($hook === '') {
            return false;
        }

        $priority = is_numeric($priority) ? intval($priority) : 10;
        $store =& q8_get_hook_store();

        if (!isset($store[$type][$hook])) {
            $store[$type][$hook] = array();
        }

        if (!isset($store[$type][$hook][$priority])) {
            $store[$type][$hook][$priority] = array();
        }

        $store[$type][$hook][$priority][] = $callback;
        ksort($store[$type][$hook]);

        return true;
    }
}

if (!function_exists('q8_remove_hook')) {
    function q8_remove_hook($type, $hook, $callback, $priority)
    {
        $hook = q8_normalize_hook_name($hook);
        $priority = is_numeric($priority) ? intval($priority) : 10;
        $store =& q8_get_hook_store();

        if (empty($store[$type][$hook][$priority])) {
            return false;
        }

        foreach ($store[$type][$hook][$priority] as $index => $registered) {
            if ($registered === $callback) {
                unset($store[$type][$hook][$priority][$index]);
            }
        }

        if (empty($store[$type][$hook][$priority])) {
            unset($store[$type][$hook][$priority]);
        }

        if (empty($store[$type][$hook])) {
            unset($store[$type][$hook]);
        }

        return true;
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10)
    {
        return q8_register_hook('action', $hook, $callback, $priority);
    }
}

if (!function_exists('remove_action')) {
    function remove_action($hook, $callback, $priority = 10)
    {
        return q8_remove_hook('action', $hook, $callback, $priority);
    }
}

if (!function_exists('do_action')) {
    function do_action($hook)
    {
        $args = func_get_args();
        array_shift($args);
        $hook = q8_normalize_hook_name($hook);

        if ($hook === '') {
            return;
        }

        $store =& q8_get_hook_store();
        if (empty($store['action'][$hook])) {
            return;
        }

        foreach ($store['action'][$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                call_user_func_array($callback, $args);
            }
        }
    }
}

if (!function_exists('q8_render_action')) {
    function q8_render_action($hook)
    {
        $args = func_get_args();
        ob_start();
        call_user_func_array('do_action', $args);
        return ob_get_clean();
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10)
    {
        return q8_register_hook('filter', $hook, $callback, $priority);
    }
}

if (!function_exists('remove_filter')) {
    function remove_filter($hook, $callback, $priority = 10)
    {
        return q8_remove_hook('filter', $hook, $callback, $priority);
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($hook, $value)
    {
        $args = func_get_args();
        $hook = q8_normalize_hook_name(array_shift($args));
        $value = array_shift($args);

        if ($hook === '') {
            return $value;
        }

        $store =& q8_get_hook_store();
        if (empty($store['filter'][$hook])) {
            return $value;
        }

        foreach ($store['filter'][$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                $callbackArgs = array_merge(array($value), $args);
                $value = call_user_func_array($callback, $callbackArgs);
            }
        }

        return $value;
    }
}

if (!function_exists('q8_get_registered_hooks')) {
    function q8_get_registered_hooks($type = null)
    {
        $store =& q8_get_hook_store();

        if ($type === null) {
            return $store;
        }

        return isset($store[$type]) ? $store[$type] : array();
    }
}
