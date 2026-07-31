<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class IncomingMessageDeduplicator
{
    public function acquire(string $messageId, string $scope = 'global'): bool
    {
        return Cache::add($this->key($messageId, $scope), 'processing', now()->addDays(30));
    }

    public function complete(string $messageId, string $scope = 'global'): void
    {
        Cache::put($this->key($messageId, $scope), 'processed', now()->addDays(30));
    }

    public function release(string $messageId, string $scope = 'global'): void
    {
        Cache::forget($this->key($messageId, $scope));
    }

    private function key(string $messageId, string $scope): string
    {
        return 'whatsapp-message:'.hash('sha256', $scope."\0".$messageId);
    }
}
