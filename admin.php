<?php

require_once __DIR__.'/vendor/autoload.php';
include_once PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php';

defined('PHPWG_ROOT_PATH') or exit(1);

// Main navigation.
$tab = $_GET['tab'] ?? 'settings';
if (in_array($tab, array('settings', 'oauth'))) {
  $adminPage = __DIR__.'/admin/'.$tab.'.php';
} else {
  $adminPage = __DIR__.'/admin/photo.php';
}
require_once $adminPage;
