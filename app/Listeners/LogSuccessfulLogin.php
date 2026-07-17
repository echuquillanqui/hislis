<?php

namespace App\Listeners;

use App\Services\AuditLogger;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function handle(Login $event): void
    {
        $this->auditLogger->log(
            action: 'auth.login',
            auditable: $event->user,
            newValues: [
                'guard' => $event->guard,
                'remember' => $event->remember,
            ],
            userId: $event->user->getAuthIdentifier()
        );
    }
}
