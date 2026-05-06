<?php

defined('PHPWG_ROOT_PATH') or exit(1);

use Addwiki\Mediawiki\Api\Client\Action\Request\ActionRequest;
use Addwiki\Mediawiki\Api\Client\Auth\OAuthOwnerConsumer;
use Addwiki\Mediawiki\Api\Client\MediaWiki;
use Piwigo\Plugin\WikimediaCommons\CommonsFileUploader;

check_status(ACCESS_ADMINISTRATOR);

$_GET['image_id'] = $_GET['tab'];
check_input_parameter(
  'image_id', $_GET, false, PATTERN_ID
);
$image_id = $_GET['tab'];
$admin_photo_base_url = get_root_url().'admin.php?page=photo-'.$image_id;

$page['active_menu'] = get_active_menu('photo');

$tabsheet = new tabsheet();
$tabsheet->set_id('photo');
$tabsheet->select(WIKIMEDIACOMMONS_ID);
$tabsheet->assign();

// OAuth consumer config.
if (!isset($conf[WIKIMEDIACOMMONS_ID])) {
  if (is_autorize_status(ACCESS_WEBMASTER)) {
    $page['warnings'] = l10n('Please set up the Wikimedia Commons OAuth consumer in the plugin settings.');
  } else {
    $page['errors'] = l10n('Please tell your site administrator to set up the Wikimedia Commons OAuth consumer in the plugin settings.');
  }
}

$user_prefs = userprefs_get_param(WIKIMEDIACOMMONS_ID);
$login_url = false;
if (!isset($user_prefs['access_key'])) {
  $login_url = WIKIMEDIACOMMONS_ADMIN.'-oauth&returnto='.urlencode($tabsheet->get_selected()['url']);
}

$query = 'SELECT * FROM '.IMAGES_TABLE.' WHERE id = '.$image_id.';';
$row = pwg_db_fetch_assoc(pwg_query($query));

if (isset($_POST['commons_filename'])) {
  try {
    $uploaded = uploadToCommons(
      $row,
      $_POST['commons_filename'],
      $_POST['wikitext'],
      $_POST['caption']
    );
  } catch (Exception $e) {
    $uploaded['warnings'] = [$e->getCode() => $e->getMessage()];
  }
  if (isset($uploaded['warnings'])) {
    foreach ($uploaded['warnings'] as $key => $val) {
      $formatted_val = is_string($val) ? $val : var_export($val, true);
      $page['warnings'][] = "Unable to upload to Commons. $key: $formatted_val";
    }
  } else {
    $page['infos'][] = l10n('Uploaded:')." <a href='{$uploaded['url']}'>{$uploaded['url']}</a>";
  }
}

$commons_url = parse_url($conf[WIKIMEDIACOMMONS_ID]['endpoint'])['host'];
$commons_title = $row['name'].'.'.pathinfo($row['file'], PATHINFO_EXTENSION);
$template->assign(array(
  'ADMIN_PAGE_TITLE' => l10n('Edit photo').' <span class="image-id">#'.$image_id.'</span>',
  'commons_url' => 'https://'.$commons_url,
  'image_url' => DerivativeImage::url(IMG_MEDIUM, $row),
  'username' => $user_prefs['username'] ?? false,
  'login_url' => $login_url,
  'logout_url' => WIKIMEDIACOMMONS_ADMIN.'-oauth&logout&returnto='.urlencode($tabsheet->get_selected()['url']),
  'commons_filename' => $commons_title,
  'caption' => $row['comment'],
  'wikitext' => getWikitext($row),
));
$template_handle = WIKIMEDIACOMMONS_ID.'admin_photo';
$template->set_filename($template_handle, __DIR__.'/photo.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT', $template_handle);

function getWikitext($row): string
{
  $source = get_absolute_root_url().make_picture_url(
    array('image_id' => $row['id'])
  );
  // phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound
  $information = "{{Information\n"
    ."| description = \n"
    ."| date        = {{taken on|".$row['date_creation']."|locationn=}}\n"
    ."| source      = $source\n"
    ."| author      = ".$row['author']."\n"
    ."| permission  = \n"
    ."}}\n";
  // phpcs:enable
  $location = '';
  if (isset($row['latitude'])) {
    $location = "{{location|".$row['latitude']."|".$row['longitude']."}}\n";
  }
  // phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound
  return "== {{int:filedesc}} ==\n"
    .$information
    .$location
    ."\n"
    ."== {{int:license-header}} ==\n"
    // @todo Make the license customisable.
    ."{{cc-by-sa-4.0}}";
  // phpcs:enable
}

/**
 * @param array $row Row from the images table.
 * @param string $title Filename to use on Commons, with file extension.
 * @param string $text Full wikitext to use for the descripiton page.
 * @param string $caption Structured data caption.
 * @return mixed[] With possible keys: 'url', 'filename', 'warnings'.
 * @throws Exception
 */
function uploadToCommons(array $row, string $title, string $text, string $caption): array
{
  global $conf;

  $full_path = realpath($row['path']);
  if (!$full_path) {
    throw new InvalidArgumentException(
      'Unable to find image file at '.$row['path']
    );
  }

  $user_prefs = userprefs_get_param(WIKIMEDIACOMMONS_ID);
  $auth_method = new OAuthOwnerConsumer(
    $conf[WIKIMEDIACOMMONS_ID]['key'],
    $conf[WIKIMEDIACOMMONS_ID]['secret'],
    $user_prefs['access_key'],
    $user_prefs['access_secret']
  );
  $api = MediaWiki::newFromPage(
    $conf[WIKIMEDIACOMMONS_ID]['endpoint'],
    $auth_method
  )->action();
  $uploader = new CommonsFileUploader($api);
  $upload_result = $uploader->uploadWithResult($title, $full_path, $text);
  if (isset($upload_result['upload']['warnings'])) {
    return $upload_result['upload'];
  }

  // The resulting wiki page name, with 'File:' prefix.
  $filename = $upload_result['upload']['imageinfo']['canonicaltitle'];
  $wiki_url = $upload_result['upload']['imageinfo']['descriptionurl'];

  // Get info.
  $info = $api->request(ActionRequest::simpleGet('query')
    ->setParam('titles', $filename));
  if (!isset($info['query']['pages'])) {
    throw new Exception('Unable to get info about '.$wiki_url);
  }
  $page_info = array_shift($info['query']['pages']);

  // Caption.
  $media_id = 'M'.$page_info['pageid'];
  $params = array(
    'language' => 'en',
    'id' => $media_id,
    'value' => $caption,
    'token' => $api->getToken(),
  );
  $api->request(ActionRequest::simplePost('wbsetlabel', $params));

  return array(
    'filename' => $filename,
    'url' => $wiki_url,
  );
}
