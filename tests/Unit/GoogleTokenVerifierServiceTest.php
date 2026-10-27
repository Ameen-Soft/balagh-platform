<?php

namespace Tests\Unit;

use App\Services\GoogleTokenVerifierService;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GoogleTokenVerifierServiceTest extends TestCase
{
    protected GoogleTokenVerifierService $verifier;

    protected string $privateKeyPem;

    protected string $publicKeyPem;

    protected string $kid = 'test-google-key-id-1';

    protected array $jwks;

    protected function setUp(): void
    {
        parent::setUp();

        $this->verifier = new GoogleTokenVerifierService;
        Cache::forget(GoogleTokenVerifierService::CACHE_KEY);

        $pair = $this->generateRsaKeyPair();
        $this->privateKeyPem = $pair['private'];
        $this->publicKeyPem = $pair['public'];
        $details = $pair['details'];

        // Convert RSA public key details into standard JWKS format
        $n = rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '=');
        $e = rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '=');

        $this->jwks = [
            'keys' => [
                [
                    'kid' => $this->kid,
                    'kty' => 'RSA',
                    'alg' => 'RS256',
                    'use' => 'sig',
                    'n' => $n,
                    'e' => $e,
                ],
            ],
        ];

        // Mock Google's public JWKS endpoint
        Http::fake([
            GoogleTokenVerifierService::JWKS_URL => Http::response($this->jwks, 200),
        ]);
    }

    /**
     * Generate an RSA key pair respecting Windows OpenSSL configuration.
     *
     * @return array{private: string, public: string, details: array}
     */
    protected function generateRsaKeyPair(): array
    {
        $options = [
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $possible = [
                'C:/Program Files/Git/usr/ssl/openssl.cnf',
                'C:/Program Files (x86)/Git/usr/ssl/openssl.cnf',
                dirname(PHP_BINARY).'/extras/ssl/openssl.cnf',
            ];
            foreach ($possible as $path) {
                if (file_exists($path)) {
                    $options['config'] = $path;
                    break;
                }
            }
        }

        $res = openssl_pkey_new($options);
        openssl_pkey_export($res, $privateKey, null, $options);
        $details = openssl_pkey_get_details($res);

        return [
            'private' => $privateKey,
            'public' => $details['key'],
            'details' => $details,
        ];
    }

    /**
     * Helper to create and sign a mock Google ID Token using RS256.
     */
    protected function generateToken(array $customClaims = [], ?string $privateKey = null, ?string $keyId = null): string
    {
        $payload = array_merge([
            'iss' => 'https://accounts.google.com',
            'aud' => 'web-client-id-123.apps.googleusercontent.com',
            'sub' => '109876543210987654321',
            'email' => 'citizen@gmail.com',
            'email_verified' => true,
            'name' => 'المواطن اليمني',
            'picture' => 'https://lh3.googleusercontent.com/photo.jpg',
            'iat' => time(),
            'exp' => time() + 3600,
        ], $customClaims);

        return JWT::encode(
            $payload,
            $privateKey ?? $this->privateKeyPem,
            'RS256',
            $keyId ?? $this->kid
        );
    }

    /**
     * 1. Valid token with matching web audience verifies successfully.
     */
    public function test_valid_google_token_verifies_cryptographically(): void
    {
        $idToken = $this->generateToken();

        $result = $this->verifier->verify($idToken, 'web-client-id-123.apps.googleusercontent.com');

        $this->assertEquals('109876543210987654321', $result['sub']);
        $this->assertEquals('citizen@gmail.com', $result['email']);
        $this->assertTrue($result['email_verified']);
        $this->assertEquals('المواطن اليمني', $result['name']);
        $this->assertEquals('https://lh3.googleusercontent.com/photo.jpg', $result['picture']);
    }

    /**
     * 2. Audience mismatch is strictly rejected.
     */
    public function test_token_with_wrong_audience_is_rejected(): void
    {
        // Token was created for Android app
        $idToken = $this->generateToken([
            'aud' => 'android-client-id-999.apps.googleusercontent.com',
        ]);

        $this->expectException(ValidationException::class);

        // Attempting to use Android token on Web endpoint
        $this->verifier->verify($idToken, 'web-client-id-123.apps.googleusercontent.com');
    }

    /**
     * 3. Expired token is rejected.
     */
    public function test_expired_token_is_rejected(): void
    {
        $idToken = $this->generateToken([
            'iat' => time() - 7200,
            'exp' => time() - 3600,
        ]);

        $this->expectException(ValidationException::class);

        $this->verifier->verify($idToken, 'web-client-id-123.apps.googleusercontent.com');
    }

    /**
     * 4. Token with invalid/tampered cryptographic signature is rejected.
     */
    public function test_token_with_invalid_signature_is_rejected(): void
    {
        // Generate an entirely different key pair
        $otherPair = $this->generateRsaKeyPair();

        // Sign token with a key NOT published in the JWKS
        $idToken = $this->generateToken([], $otherPair['private']);

        $this->expectException(ValidationException::class);

        $this->verifier->verify($idToken, 'web-client-id-123.apps.googleusercontent.com');
    }

    /**
     * 5. Untrusted issuer is rejected.
     */
    public function test_token_with_untrusted_issuer_is_rejected(): void
    {
        $idToken = $this->generateToken([
            'iss' => 'https://evil-hacker.com',
        ]);

        $this->expectException(ValidationException::class);

        $this->verifier->verify($idToken, 'web-client-id-123.apps.googleusercontent.com');
    }

    /**
     * 6. Both iss 'accounts.google.com' and 'https://accounts.google.com' are accepted as per Google spec.
     */
    public function test_alternative_standard_google_issuer_is_accepted(): void
    {
        $idToken = $this->generateToken([
            'iss' => 'accounts.google.com',
        ]);

        $result = $this->verifier->verify($idToken, 'web-client-id-123.apps.googleusercontent.com');

        $this->assertEquals('citizen@gmail.com', $result['email']);
    }
}
