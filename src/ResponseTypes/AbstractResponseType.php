<?php

/**
 * OAuth 2.0 Abstract Response Type.
 *
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

declare(strict_types=1);

namespace League\OAuth2\Server\ResponseTypes;

use League\OAuth2\Server\CryptKeyInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

abstract class AbstractResponseType implements ResponseTypeInterface
{
    protected AccessTokenEntityInterface $accessToken;

    protected RefreshTokenEntityInterface $refreshToken;

    public function setAccessToken(AccessTokenEntityInterface $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function setRefreshToken(RefreshTokenEntityInterface $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }
}
