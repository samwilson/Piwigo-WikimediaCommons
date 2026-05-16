<?php

require_once __DIR__.'/vendor/autoload.php';
require_once PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php';

defined('PHPWG_ROOT_PATH') or exit(1);

/**
 * Get a HTTP user-agent that conforms to the WMF User-Agent Policy.
 * @link https://foundation.wikimedia.org/wiki/Policy:Wikimedia_Foundation_User-Agent_Policy
 * @return string
 */
function wikimediacommons_useragent(): string
{
  $user_prefs = userprefs_get_param(WIKIMEDIACOMMONS_ID);
  $username = isset($user_prefs['username']) ? 'User:'.$user_prefs['username'] : '';
  return trim('piwigo/wikimedia-commons-plugin '.get_absolute_root_url().' '.$username);
}

// Main navigation.
$tab = $_GET['tab'] ?? 'settings';
if (in_array($tab, array('settings', 'oauth'))) {
  $admin_page = __DIR__.'/admin/'.$tab.'.php';
} else {
  $admin_page = __DIR__.'/admin/photo.php';
}
require_once $admin_page;
