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

$oauth_client_conf = new ClientConfig($conf[WIKIMEDIACOMMONS_ID]['endpoint']);
$oauth_client_conf->setConsumer(new Consumer(
  $conf[WIKIMEDIACOMMONS_ID]['key'],
  $conf[WIKIMEDIACOMMONS_ID]['secret']
));
$oauth_client_conf->setUserAgent('piwigo/wikimedia-commons-plugin '.get_absolute_root_url());
$client = new Client($oauth_client_conf);
if (isset($_GET['returnto'])) {
  $client->setCallback(WIKIMEDIACOMMONS_ADMIN.'-oauth&returnto='.urlencode($_GET['returnto']));
}

if (isset($_GET['logout'])) {
  userprefs_update_param(WIKIMEDIACOMMONS_ID, null);
  $logged_out_msg = 'You have been logged out of Wikimedia Commons.';
  $_SESSION['page_infos'][] = l10n($logged_out_msg);
  redirect($_GET['returnto'] ?? get_root_url().'admin.php');
}

if (!isset($_GET['oauth_verifier'])) {
  try {
    // Send them off to Commons to authorise Piwigo.
    list($auth_url, $request_token) = $client->initiate();
    $_SESSION['request_secret'] = $request_token->secret;
    redirect($auth_url);
  } catch (Exception $e) {
    $page['errors'][] = $e->getMessage();
  }

} else {
  // When they come back, reconstruct the token from the session,
  // and fetch an access token to store.
  $request_token = new Token($_GET['oauth_token'], $_SESSION['request_secret']);
  $access_token = $client->complete( $request_token, $_GET['oauth_verifier'] );
  $ident = $client->identify( $access_token );
  userprefs_update_param(WIKIMEDIACOMMONS_ID, array(
    'username' => $ident->username,
    'access_key' => $access_token->key,
    'access_secret' => $access_token->secret,
  ));
  $_SESSION['page_infos'][] = l10n('You are now logged in to Wikimedia Commons.');
  redirect($_GET['returnto'] ?? get_root_url().'admin.php');
}
