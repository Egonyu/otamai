<?php

if (! function_exists('setting')) {
    
    function setting($name = null, $default = null,$withClean=1)
    {
        if (is_null($name)) {
            return app('setting');
        }

        if (is_array($name)) {
            return app('setting')->set($name);
        }

        try {
            if(!$withClean){
                return (app('setting')->get($name, $default));
            }
            return app('setting')->get($name, $default);
            //return clean(app('setting')->get($name, $default));
        } catch (PDOException $e) {
            return $default;
        }
    }
}
