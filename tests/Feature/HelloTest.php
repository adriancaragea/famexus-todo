<?php

use Firebase\JWT\JWT;

beforeEach(function () {
    $key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
    openssl_pkey_export($key, $this->privateKey);

    $publicKeyPath = tempnam(sys_get_temp_dir(), 'jwt');
    file_put_contents($publicKeyPath, openssl_pkey_get_details($key)['key']);
    config(['famexus.jwt.public_key' => $publicKeyPath]);
});

function gatewayToken(string $privateKey, array $overrides = []): string
{
    return JWT::encode(array_merge([
        'iss' => 'famexus-gateway',
        'aud' => 'todo',
        'iat' => time(),
        'exp' => time() + 60,
    ], $overrides), $privateKey, 'RS256');
}

it('says hello to the gateway', function () {
    $this->withToken(gatewayToken($this->privateKey))
        ->getJson('/api/v1/hello')
        ->assertOk()
        ->assertExactJson(['service' => 'todo', 'message' => 'Hello from todo']);
});

it('rejects requests without a gateway token', function () {
    $this->getJson('/api/v1/hello')->assertUnauthorized();
});

it('rejects tokens addressed to another service', function () {
    $this->withToken(gatewayToken($this->privateKey, ['aud' => 'calendar']))
        ->getJson('/api/v1/hello')
        ->assertUnauthorized();
});

it('rejects expired tokens', function () {
    $this->withToken(gatewayToken($this->privateKey, ['iat' => time() - 120, 'exp' => time() - 60]))
        ->getJson('/api/v1/hello')
        ->assertUnauthorized();
});

it('rejects tokens signed with another key', function () {
    $other = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
    openssl_pkey_export($other, $otherPrivateKey);

    $this->withToken(gatewayToken($otherPrivateKey))
        ->getJson('/api/v1/hello')
        ->assertUnauthorized();
});
