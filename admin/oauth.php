<?php

defined('PHPWG_ROOT_PATH') or exit(1);

use MediaWiki\OAuthClient\Client;
use MediaWiki\OAuthClient\ClientConfig;
use MediaWiki\OAuthClient\Consumer;
use MediaWiki\OAuthClient\Token;

check_status(ACCESS_ADMINISTRATOR);
if (!isset($conf[WIKIMEDIACOMMONS_ID]))
{
  access_denied();
}

$conf[WIKIMEDIACOMMONS_ID] = safe_unserialize($conf[WIKIMEDIACOMMONS_ID]);

$oauthClientConf = new ClientConfig($conf[WIKIMEDIACOMMONS_ID]['endpoint'], true);
$oauthClientConf->setConsumer(new Consumer($conf[WIKIMEDIACOMMONS_ID]['key'], $conf[WIKIMEDIACOMMONS_ID]['secret']));
$oauthClientConf->setUserAgent(get_root_url());
$client = new Client($oauthClientConf);

if (isset($_GET['logout'])) {
  userprefs_update_param(WIKIMEDIACOMMONS_ID, null);
  $_SESSION['page_infos'][] = l10n('You have been logged out of Wikimedia Commons.');
  redirect(get_root_url().'admin.php');
}

if (!isset($_GET['oauth_verifier'])) {
  // Send them off to Commons to authorise Piwigo.
  list( $authUrl, $requestToken ) = $client->initiate();
  $_SESSION['request_secret'] = $requestToken->secret;
  redirect($authUrl);

} else {
  // When they come back, reconstruct the token from the session, and fetch an access token to store.
  $requestToken = new Token( $_GET['oauth_token'], $_SESSION['request_secret'] );
  $accessToken = $client->complete( $requestToken, $_GET['oauth_verifier'] );
  $ident = $client->identify( $accessToken );
  userprefs_update_param(WIKIMEDIACOMMONS_ID, [
    'username' => $ident->username,
    'access_key' => $accessToken->key,
    'access_secret' => $accessToken->secret,
  ]);
  $_SESSION['page_infos'][] = l10n('You are now logged in to Wikimedia Commons.');
  redirect(get_root_url().'admin.php');
}
