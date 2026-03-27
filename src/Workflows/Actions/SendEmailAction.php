<?php

declare(strict_types=1);

namespace Cartino\Workflows\Actions;

use Illuminate\Support\Facades\Mail;

class SendEmailAction
{
    public function execute(array $data, array $config): void
    {
        $to = $this->parseTemplate($config['to'], $data);
        $subject = $this->parseTemplate($config['subject'], $data);
        $body = $this->parseTemplate($config['body'], $data);

        Mail::raw($body, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
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
