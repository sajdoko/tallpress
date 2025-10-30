<?php

namespace Sajdoko\TallPress\Livewire\Concerns;

trait WithToast
{
    /**
     * Dispatch a toast notification.
     */
    protected function toast(string $message, string $type = 'success'): void
    {
        $this->dispatch('toast', [
            'message' => $message,
            'type' => $type,
        ]);
    }

    /**
     * Dispatch a success toast notification.
     */
    protected function toastSuccess(string $message): void
    {
        $this->toast($message, 'success');
    }

    /**
     * Dispatch an error toast notification.
     */
    protected function toastError(string $message): void
    {
        $this->toast($message, 'error');
    }

    /**
     * Dispatch an info toast notification.
     */
    protected function toastInfo(string $message): void
    {
        $this->toast($message, 'info');
    }

    /**
     * Dispatch a warning toast notification.
     */
    protected function toastWarning(string $message): void
    {
        $this->toast($message, 'warning');
    }
}
