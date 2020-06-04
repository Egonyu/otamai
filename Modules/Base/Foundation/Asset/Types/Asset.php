<?php

namespace Modules\Base\Foundation\Asset\Types;

use Illuminate\Support\Facades\File;

abstract class Asset
{
    protected function asset($url)
    {
        list($dirname, $name, $extension) = $this->parse($url);

        $url = "{$dirname}/{$name}";

        return "{$url}.{$extension}";
    }

    private function parse($url)
    {
        return [
            File::dirname($url),
            File::name($url),
            File::extension($url),
        ];
    }
}
