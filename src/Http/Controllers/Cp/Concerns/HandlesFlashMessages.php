<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Cp\Concerns;

use Inertia\Inertia;

trait HandlesFlashMessages
{
    /**
     * Flash a success toast to the frontend.
     */
    protected function flashSuccess(string $message, array $toastData = [], array $extra = []): void
    {
        $this->flashToast('success', $message, $toastData, $extra);
    }

    /**
     * Flash an error toast to the frontend.
     */
    protected function flashError(string $message, array $toastData = [], array $extra = []): void
    {
        $this->flashToast('error', $message, $toastData, $extra);
    }

    /**
     * Flash a warning toast to the frontend.
     */
    protected function flashWarning(string $message, array $toastData = [], array $extra = []): void
    {
        $this->flashToast('warning', $message, $toastData, $extra);
    }

    /**
     * Flash an informational toast to the frontend.
     */
    protected function flashInfo(string $message, array $toastData = [], array $extra = []): void
    {
        $this->flashToast('info', $message, $toastData, $extra);
    }

    /**
     * Flash a toast payload using Inertia's flash data.
     */
    protected function flashToast(string $type, string $message, array $toastData = [], array $extra = []): void
    {
        Inertia::flash(array_merge($extra, [
            'toast' => array_merge([
                'type' => $type,
                'message' => $message,
            ], $toastData),
        ]));
    }
}
