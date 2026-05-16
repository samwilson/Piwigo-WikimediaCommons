<?php
// phpcs:disable

namespace Piwigo\Plugin\WikimediaCommons;

use Addwiki\Mediawiki\Api\Client\Auth\OAuthOwnerConsumer as AddWikiOAuthOwnerConsumer;

/**
 * This class is a temporary workaround in order to set the user agent,
 * until https://github.com/addwiki/addwiki/issues/229 is resolved.
 */
class OAuthOwnerConsumer extends AddWikiOAuthOwnerConsumer
{

  private ?string $identifierForUserAgent = null;

  public function setIdentifierForUserAgent($identifier): void {
    $this->identifierForUserAgent = $identifier;
  }

  public function identifierForUserAgent(): ?string {
    return $this->identifierForUserAgent ?? parent::identifierForUserAgent();
  }
}
