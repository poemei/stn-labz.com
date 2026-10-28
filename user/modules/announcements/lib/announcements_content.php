<?php
declare(strict_types=1);

final class announcements_content
{
    private render_md $renderer;

    public function __construct(render_md $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render(string $body, string $contentType): string
{
    $contentType = strtolower(trim($contentType));

    switch ($contentType) {
        case 'markdown':
            return (string) $this->renderer->markdown($body);

        case 'html':
            return $body;

        case 'text':
        default:
            return nl2br(htmlspecialchars(
                $body,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            ));
    }
}
}
