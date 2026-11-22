<?php

namespace App\Helpers;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Verrouillage anti-bruteforce des connexions, par identifiant (indépendant
 * de l'IP pour protéger le compte visé même en cas de tentatives distribuées).
 * Même mécanique que le RateLimiter déjà utilisé pour l'OTP de reset de mot
 * de passe.
 */
class LoginThrottle
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 900; // 15 minutes

    private static function key(string $guard, string $identifiant): string
    {
        return 'login-attempts:' . $guard . ':' . strtolower(trim($identifiant));
    }

    public static function tooManyAttempts(string $guard, string $identifiant): bool
    {
        return RateLimiter::tooManyAttempts(self::key($guard, $identifiant), self::MAX_ATTEMPTS);
    }

    public static function hit(string $guard, string $identifiant): void
    {
        RateLimiter::hit(self::key($guard, $identifiant), self::DECAY_SECONDS);
    }

    public static function clear(string $guard, string $identifiant): void
    {
        RateLimiter::clear(self::key($guard, $identifiant));
    }

    public static function availableInMinutes(string $guard, string $identifiant): int
    {
        return (int) ceil(RateLimiter::availableIn(self::key($guard, $identifiant)) / 60);
    }

    public static function lockoutMessage(string $guard, string $identifiant): string
    {
        $minutes = self::availableInMinutes($guard, $identifiant);

        return "Trop de tentatives échouées. Compte temporairement verrouillé, réessayez dans {$minutes} minute(s).";
    }
}
