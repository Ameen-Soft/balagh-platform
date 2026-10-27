<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GoogleTokenVerifierService
{
    /**
     * Google Public JWKS URL.
     */
    public const JWKS_URL = 'https://www.googleapis.com/oauth2/v3/certs';

    /**
     * Cache key for Google public keys.
     */
    public const CACHE_KEY = 'google_oauth2_jwks_keys';

    /**
     * Cache duration in seconds (6 hours).
     */
    public const CACHE_TTL = 21600;

    /**
     * Verify a Google ID token cryptographically using public certificates.
     *
     * @param  string  $idToken
     * @param  string|array  $expectedAudience  Expected client ID (e.g. Web Client ID)
     * @return array{sub: string, email: string, email_verified: bool, name: string, picture: ?string, hd: ?string}
     *
     * @throws ValidationException
     */
    public function verify(string $idToken, string|array $expectedAudience): array
    {
        $keys = $this->getPublicKeys();

        try {
            $decoded = JWT::decode($idToken, $keys);
        } catch (\UnexpectedValueException $e) {
            // Possible key rotation by Google — clear cache and retry once
            Cache::forget(self::CACHE_KEY);
            try {
                $keys = $this->getPublicKeys();
                $decoded = JWT::decode($idToken, $keys);
            } catch (\Throwable $retryError) {
                Log::warning('Google JWT decoding failed after key refresh: '.$retryError->getMessage());
                throw ValidationException::withMessages([
                    'id_token' => ['رمز تسجيل الدخول من Google غير صالح أو تم التلاعب به.'],
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Google JWT verification failed: '.$e->getMessage());
            throw ValidationException::withMessages([
                'id_token' => ['رمز تسجيل الدخول من Google غير صالح أو منتهي الصلاحية.'],
            ]);
        }

        $payload = (array) $decoded;

        // 1. Validate Issuer (iss)
        $issuer = $payload['iss'] ?? '';
        if ($issuer !== 'https://accounts.google.com' && $issuer !== 'accounts.google.com') {
            Log::warning("Google token issuer mismatch: expected accounts.google.com, got {$issuer}");
            throw ValidationException::withMessages([
                'id_token' => ['جهة إصدار رمز الدخول غير موثوقة.'],
            ]);
        }

        // 2. Validate Audience (aud) strictly against expected audience for the channel
        $expected = (array) $expectedAudience;
        $tokenAud = $payload['aud'] ?? null;
        $tokenAzp = $payload['azp'] ?? null;

        $audienceValid = in_array($tokenAud, $expected, true) ||
            ($tokenAzp && in_array($tokenAzp, $expected, true));

        if (! $audienceValid) {
            Log::warning("Google token audience mismatch: token aud '{$tokenAud}' / azp '{$tokenAzp}' not in expected list.");
            throw ValidationException::withMessages([
                'id_token' => ['رمز المصادقة غير مخصص لهذا التطبيق أو المسار.'],
            ]);
        }

        // 3. Validate Subject (sub)
        $sub = $payload['sub'] ?? null;
        if (! $sub || ! is_string($sub)) {
            throw ValidationException::withMessages([
                'id_token' => ['لم يتم العثور على معرّف مستخدم صالح في رمز Google.'],
            ]);
        }

        // 4. Validate Email
        $email = $payload['email'] ?? null;
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'id_token' => ['لم يتم العثور على عنوان بريد إلكتروني صالح في رمز Google.'],
            ]);
        }

        return [
            'sub' => (string) $sub,
            'email' => strtolower((string) $email),
            'email_verified' => filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'name' => (string) ($payload['name'] ?? explode('@', (string) $email)[0]),
            'picture' => isset($payload['picture']) ? (string) $payload['picture'] : null,
            'hd' => isset($payload['hd']) ? (string) $payload['hd'] : null,
        ];
    }

    /**
     * Retrieve Google public certificates parsed into JWK Key objects.
     *
     * @return array<string, \Firebase\JWT\Key>
     *
     * @throws ValidationException
     */
    public function getPublicKeys(): array
    {
        $jwks = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            try {
                $response = Http::timeout(10)->get(self::JWKS_URL);
            } catch (\Throwable $e) {
                Log::error('Failed to fetch Google JWKS public certificates: '.$e->getMessage());
                throw ValidationException::withMessages([
                    'id_token' => ['تعذر الاتصال بخوادم Google للتحقق من شهادات المصادقة.'],
                ]);
            }

            if (! $response->successful()) {
                Log::error('Google JWKS response unsuccessful: '.$response->status());
                throw ValidationException::withMessages([
                    'id_token' => ['فشل جلب شهادات التحقق من خوادم Google.'],
                ]);
            }

            $data = $response->json();

            if (! isset($data['keys']) || ! is_array($data['keys'])) {
                Log::error('Invalid JWKS structure returned from Google.');
                throw ValidationException::withMessages([
                    'id_token' => ['بنية شهادات Google غير صالحة.'],
                ]);
            }

            return $data;
        });

        return JWK::parseKeySet($jwks);
    }
}
