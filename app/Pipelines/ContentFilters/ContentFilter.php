<?php

namespace App\Pipelines\ContentFilters;

class ContentFilter implements ContentFilterInterface
{
    protected array $badWords = ['animal', 'bad', 'شرير']; // Add words here

    public function handle(string $content, \Closure $next)
    {
        $filteredContent = str_ireplace($this->badWords, '***', $content);
        return $next($filteredContent);
    }
}
