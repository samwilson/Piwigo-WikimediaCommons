<?php

defined('PHPWG_ROOT_PATH') or exit(1);

check_status(ACCESS_WEBMASTER);

$page['active_menu'] = get_active_menu('plugins');

// Set up the plugin's settings.
if (isset($conf[WIKIMEDIACOMMONS_ID])) {
  $conf[WIKIMEDIACOMMONS_ID] = safe_unserialize($conf[WIKIMEDIACOMMONS_ID]);
} else {
  $endpoint = 'https://commons.wikimedia.org/w/index.php?title=Special:OAuth';
  $conf[WIKIMEDIACOMMONS_ID] = array(
    'endpoint' => $endpoint,
    'key' => '',
    'secret' => '',
  );
}

// Save the new settings and redirect back to the settings page.
if (isset($_POST[WIKIMEDIACOMMONS_ID]['endpoint'])) {
  $conf[WIKIMEDIACOMMONS_ID] = array(
    'endpoint' => trim($_POST[WIKIMEDIACOMMONS_ID]['endpoint']),
    'key' => trim($_POST[WIKIMEDIACOMMONS_ID]['key']),
    'secret' => trim($_POST[WIKIMEDIACOMMONS_ID]['secret']),
  );
  conf_update_param(WIKIMEDIACOMMONS_ID, $conf[WIKIMEDIACOMMONS_ID]);
  $_SESSION['page_infos'][] = l10n('Settings saved.');
  redirect(WIKIMEDIACOMMONS_ADMIN.'-settings');
}

// Prepare the template.
$callback_url = add_url_params(
  get_absolute_root_url().'admin.php',
  array('page' => 'plugin-wikimediacommons-callback')
);
$template->assign(array(
  'admin_url' => WIKIMEDIACOMMONS_ADMIN,
  'wikimediacommons_page' => WIKIMEDIACOMMONS_PAGE,
  'wikimediacommons_conf' => $conf[WIKIMEDIACOMMONS_ID],
  'callback_url' => $callback_url,
));

$template_handle = WIKIMEDIACOMMONS_ID.'-settings';
$template->set_filename($template_handle, __DIR__.'/settings.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT', $template_handle);
