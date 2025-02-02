<?php

/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

declare(strict_types=1);

namespace League\OAuth2\Server\Entities\Traits;

use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use League\OAuth2\Server\CryptKeyInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use RuntimeException;

trait AccessTokenTrait
{
    private ?CryptKeyInterface $privateKey = null;

    private Configuration $jwtConfiguration;

    /**
     * Set the private key used to encrypt this access token.
     */
    public function setPrivateKey(CryptKeyInterface $privateKey): void
    {
        $this->privateKey = $privateKey;
    }

    /**
     * Initialise the JWT Configuration.
     */
    public function initJwtConfiguration(): void
    {
        $privateKeyContents = $this->privateKey->getKeyContents();

        if ($privateKeyContents === '') {
            throw new RuntimeException('Private key is empty');
        }

        $this->jwtConfiguration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($privateKeyContents, $this->privateKey->getPassPhrase() ?? ''),
            InMemory::plainText('empty', 'empty')
        );
    }

    /**
     * Generate a JWT from the access token
     */
    private function convertToJWT(): Token
    {
        $this->initJwtConfiguration();

        return $this->jwtConfiguration->builder()
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo($this->getSubjectIdentifier())
            ->withClaim('scopes', $this->getScopes())
            ->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());
    }

    /**
     * Generate a string representation from the access token
     */
    public function toString(): string
    {
        if ($this->privateKey === null) {
            return $this->getIdentifier();
        }
        
        return $this->convertToJWT()->toString();
    }

    abstract public function getClient(): ClientEntityInterface;

    abstract public function getExpiryDateTime(): DateTimeImmutable;

    /**
     * Get the token user.
     *
     * @return ?UserEntityInterface
     */
    abstract public function getUser(): ?UserEntityInterface;

    /**
     * @return ScopeEntityInterface[]
     */
    abstract public function getScopes(): array;

    /**
     * @return non-empty-string
     */
    abstract public function getIdentifier(): string;

    /**
     * @return non-empty-string
     */
    private function getSubjectIdentifier(): string
    {
        return $this->getUser()?->getIdentifier() ?? $this->getClient()->getIdentifier();
    }
}
