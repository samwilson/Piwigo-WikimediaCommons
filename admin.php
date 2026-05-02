<?php

require_once __DIR__.'/vendor/autoload.php';
require_once PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php';

defined('PHPWG_ROOT_PATH') or exit(1);

// Main navigation.
$tab = $_GET['tab'] ?? 'settings';
if (in_array($tab, array('settings', 'oauth'))) {
  $admin_page = __DIR__.'/admin/'.$tab.'.php';
} else {
  $admin_page = __DIR__.'/admin/photo.php';
}
require_once $admin_page;
