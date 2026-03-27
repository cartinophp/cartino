<?php

declare(strict_types=1);

namespace Cartino\Workflows\Actions;

use Cartino\Models\Notification;

class CreateNotificationAction
{
    public function execute(array $data, array $config): void
    {
        Notification::create([
            'type' => $config['type'] ?? 'info',
            'title' => $this->parseTemplate($config['title'], $data),
            'message' => $this->parseTemplate($config['message'], $data),
            'user_id' => $config['user_id'] ?? null,
            'data' => $data,
        ]);
    }

    protected function parseTemplate(string $template, array $data): string
    {
        return preg_replace_callback(
            '/\{\{([\w.]+)\}\}/',
            function ($matches) use ($data) {
                return data_get($data, $matches[1], '');
            },
            $template,
        );
    }
}
