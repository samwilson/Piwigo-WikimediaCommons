<?php
/*
Plugin Name: Wikimedia Commons
Version: 0.1.0
Description: A Piwigo plugin for exporting photos to Wikimedia Commons.
Plugin URI: auto
Author: Sam Wilson
Author URI: https://samwilson.id.au
Has Settings: webmaster
*/

// Make sure we're already in Piwigo.
defined('PHPWG_ROOT_PATH') or exit(1);

// Define plugin's paths etc.
define('WIKIMEDIACOMMONS_ID', 'WikimediaCommons');
define('WIKIMEDIACOMMONS_PATH', PHPWG_PLUGINS_PATH.WIKIMEDIACOMMONS_ID.'/');
define('WIKIMEDIACOMMONS_PAGE', 'plugin-'.WIKIMEDIACOMMONS_ID);
define(
  'WIKIMEDIACOMMONS_ADMIN',
  get_absolute_root_url().'admin.php?page=plugin-'.WIKIMEDIACOMMONS_ID
);
define(
  'WIKIMEDIACOMMONS_DIR',
  realpath(PHPWG_PLUGINS_PATH.WIKIMEDIACOMMONS_ID).'/'
);

// Complain if our plugin directory is not named correctly.
if (basename(__DIR__) !== WIKIMEDIACOMMONS_ID) {
  add_event_handler('init', function () {
    global $page;
    $page['errors'][] = l10n(
      'wikimediacommons-wrong-plugin-dir',
      basename(__DIR__),
      WIKIMEDIACOMMONS_ID
    );
  });
  return;
}

//Initialise the plugin.
add_event_handler('init', function() {
  global $conf;
  load_language('plugin.lang', WIKIMEDIACOMMONS_PATH);
  if (isset($conf[WIKIMEDIACOMMONS_ID])) {
    $conf[WIKIMEDIACOMMONS_ID] = safe_unserialize($conf[WIKIMEDIACOMMONS_ID]);
  }
});

// Add a tab to the picture page.
add_event_handler(
  'tabsheet_before_select',
  function(array $sheets, ?string $id) {
    if ($id == 'photo')
    {
      $logo_url = WIKIMEDIACOMMONS_PATH.'/admin/commons.svg';
      $logo = '<img src="'.$logo_url.'" width="15" height="15" />';
      $sheets[WIKIMEDIACOMMONS_ID] = array(
        'caption' => $logo.' '.l10n('Wikimedia Commons'),
        'url' => WIKIMEDIACOMMONS_ADMIN.'-'.$_GET['image_id'],
      );
    }
    return $sheets;
  }
);
