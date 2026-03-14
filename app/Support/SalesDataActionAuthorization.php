<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;

class SalesDataActionAuthorization
{
    public static function canManageData(?Authenticatable $user): bool
    {
        if (! $user) {
            return false;
        }

        $email = strtolower((string) data_get($user, 'email'));
        $allowedEmails = array_values(array_filter(array_map(
            static fn(string $value): string => strtolower(trim($value)),
            explode(',', (string) config('app.sales_data_admin_emails', ''))
        )));

        if (count($allowedEmails) > 0) {
            return in_array($email, $allowedEmails, true);
        }

        // Safe default for fresh installs without explicit allowlist configuration.
        return (int) $user->getAuthIdentifier() === 1;
    }
}
