<?php

namespace App\Pipelines\ContentFilters;


interface ContentFilterInterface
{
    public function handle(string $content, \Closure $next);
}
