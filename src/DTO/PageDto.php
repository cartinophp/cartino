<?php

namespace Cartino\DTO;

use DateTime;
use Illuminate\Support\Str;

class PageDto extends BaseDto
{
    public function __construct(
        public ?int $id = null,
        public ?int $site_id = null,
        public string $title = '',
        public string $handle = '',
        public string $content = '',
        public string $status = 'draft',
        public ?int $template_id = null,
        public ?int $author_id = null,
        public bool $show_title = true,
        public ?string $seo_title = null,
        public ?string $seo_description = null,
        public ?DateTime $published_at = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    /**
     * Create from array
     */
    public static function from(array $data): static
    {
        return new static(
            id: $data['id'] ?? null,
            site_id: isset($data['site_id']) ? ((int) $data['site_id']) : null,
            title: $data['title'] ?? '',
            handle: $data['handle'] ?? '',
            content: $data['content'] ?? '',
            status: $data['status'] ?? 'draft',
            template_id: isset($data['template_id']) ? ((int) $data['template_id']) : null,
            author_id: isset($data['author_id']) ? ((int) $data['author_id']) : null,
            show_title: filter_var($data['show_title'] ?? true, FILTER_VALIDATE_BOOLEAN),
            seo_title: $data['seo_title'] ?? null,
            seo_description: $data['seo_description'] ?? null,
            published_at: isset($data['published_at']) ? new DateTime($data['published_at']) : null,
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null,
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter(
            [
                'id' => $this->id,
                'site_id' => $this->site_id,
                'title' => $this->title,
                'handle' => $this->handle ?: Str::slug($this->title),
                'content' => $this->content,
                'status' => $this->status,
                'template_id' => $this->template_id,
                'author_id' => $this->author_id,
                'show_title' => $this->show_title,
                'seo_title' => $this->seo_title,
                'seo_description' => $this->seo_description,
                'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            fn ($value) => $value !== null,
        );
    }

    /**
     * Validate page data
     */
    public function validate(): array
    {
        $errors = [];

        if (empty(trim($this->title))) {
            $errors['title'] = 'Page title is required';
        }

        if (strlen($this->title) > 255) {
            $errors['title'] = 'Title cannot exceed 255 characters';
        }

        if (empty(trim($this->content))) {
            $errors['content'] = 'Page content is required';
        }

        if (! in_array($this->status, ['published', 'draft', 'private'])) {
            $errors['status'] = 'Status must be published, draft, or private';
        }

        if (! empty($this->seo_title) && strlen($this->seo_title) > 60) {
            $errors['seo_title'] = 'SEO title cannot exceed 60 characters';
        }

        if (! empty($this->seo_description) && strlen($this->seo_description) > 160) {
            $errors['seo_description'] = 'SEO description cannot exceed 160 characters';
        }

        if (! empty($this->handle) && ! preg_match('/^[a-z0-9\-]+$/', $this->handle)) {
            $errors['handle'] = 'Handle can only contain lowercase letters, numbers, and hyphens';
        }

        if ($this->published_at && $this->status === 'published' && $this->published_at > new DateTime) {
            $errors['published_at'] = 'Published date cannot be in the future for published pages';
        }

        return $errors;
    }

    /**
     * Check if page is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && ($this->published_at === null || $this->published_at <= new DateTime);
    }

    /**
     * Check if page is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if page is private
     */
    public function isPrivate(): bool
    {
        return $this->status === 'private';
    }

    /**
     * Check if page is scheduled
     */
    public function isScheduled(): bool
    {
        return $this->status === 'published' && $this->published_at && $this->published_at > new DateTime;
    }

    /**
     * Get SEO title or fallback to title
     */
    public function getSeoTitle(): string
    {
        return $this->seo_title ?: $this->title;
    }

    /**
     * Get SEO description or generate from content
     */
    public function getSeoDescription(): string
    {
        if ($this->seo_description) {
            return $this->seo_description;
        }

        // Generate description from content (strip HTML and limit to 160 chars)
        $content = strip_tags($this->content);
        $content = preg_replace('/\s+/', ' ', $content);

        if (strlen($content) <= 160) {
            return $content;
        }

        return substr($content, 0, 157).'...';
    }

    /**
     * Get URL handle
     */
    public function getHandle(): string
    {
        return $this->handle ?: Str::slug($this->title);
    }

    /**
     * Get page URL
     */
    public function getUrl(string $baseUrl = ''): string
    {
        return rtrim($baseUrl, '/').'/pages/'.$this->getHandle();
    }

    /**
     * Get page preview URL
     */
    public function getPreviewUrl(string $baseUrl = ''): string
    {
        return $this->getUrl($baseUrl).'?preview=true';
    }

    /**
     * Get word count of content
     */
    public function getWordCount(): int
    {
        $text = strip_tags($this->content);

        return str_word_count($text);
    }

    /**
     * Get estimated reading time (based on 200 words per minute)
     */
    public function getReadingTime(): int
    {
        return max(1, (int) ceil($this->getWordCount() / 200));
    }

    /**
     * Check if page has template
     */
    public function hasTemplate(): bool
    {
        return $this->template_id !== null;
    }

    /**
     * Check if show title is enabled
     */
    public function shouldShowTitle(): bool
    {
        return $this->show_title;
    }

    /**
     * Get content excerpt
     */
    public function getExcerpt(int $length = 200): string
    {
        $text = strip_tags($this->content);
        $text = preg_replace('/\s+/', ' ', trim($text));

        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - 3).'...';
    }

    /**
     * Check if page has published date
     */
    public function hasPublishedDate(): bool
    {
        return $this->published_at !== null;
    }

    /**
     * Get formatted published date
     */
    public function getFormattedPublishedDate(string $format = 'M j, Y'): ?string
    {
        return $this->published_at?->format($format);
    }
}
