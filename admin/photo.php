<?php

defined('PHPWG_ROOT_PATH') or exit(1);

use Addwiki\Mediawiki\Api\Client\Action\Request\ActionRequest;
use Addwiki\Mediawiki\Api\Client\Auth\OAuthOwnerConsumer;
use Addwiki\Mediawiki\Api\Client\MediaWiki;
use Piwigo\Plugin\WikimediaCommons\CommonsFileUploader;

check_status(ACCESS_ADMINISTRATOR);

include_once(WIKIMEDIACOMMONS_PATH.'vendor/autoload.php');

$_GET['image_id'] = $_GET['tab'];
check_input_parameter('image_id', $_GET, false, PATTERN_ID);
$admin_photo_base_url = get_root_url().'admin.php?page=photo-'.$_GET['image_id'];

// check_input_parameter('tab', $_GET, false, PATTERN_ID);
// $_GET['image_id'] = $imageId;

$imageId = $_GET['tab'];

$page['active_menu'] = get_active_menu('photo');

$tabsheet = new tabsheet();
$tabsheet->set_id('photo');
$tabsheet->select(WIKIMEDIACOMMONS_ID);
$tabsheet->assign();

// OAuth consumer config.
if (!isset($conf[WIKIMEDIACOMMONS_ID])) {
//  $conf[WIKIMEDIACOMMONS_ID] = safe_unserialize($conf[WIKIMEDIACOMMONS_ID]);
//} else {
  if (is_autorize_status(ACCESS_WEBMASTER)) {
    $page['warnings'] = l10n('Please set up the Wikimedia Commons OAuth consumer in the plugin settings.');
  } else {
    $page['errors'] = l10n('Please tell your site administrator to set up the Wikimedia Commons OAuth consumer in the plugin settings.');
  }
}

$userPrefs = userprefs_get_param(WIKIMEDIACOMMONS_ID);
$loginUrl = false;
if (!isset($userPrefs['access_key'])) {
  $loginUrl = WIKIMEDIACOMMONS_ADMIN.'-oauth';
}

$query = 'SELECT * FROM '.IMAGES_TABLE.' WHERE id = '.$imageId.';';
$row = pwg_db_fetch_assoc(pwg_query($query));

if (isset($_POST['commons_filename'])) {
  try {
    $uploaded = uploadToCommons($row, $_POST['commons_filename'], $_POST['wikitext'], $_POST['caption']);
  } catch (Exception $e) {
    $uploaded['warnings'] = [$e->getCode() => $e->getMessage()];
  }
  if (isset($uploaded['warnings'])) {
    foreach ($uploaded['warnings'] as $key => $val) {
      $formattedVal = is_string($val) ? $val : var_export($val, true);
      $page['warnings'][] = "Unable to upload to Commons. $key: $formattedVal";
    }
  } else {
    $page['infos'][] = "Uploaded: <a href='{$uploaded['url']}'>{$uploaded['url']}</a>";
  }
}

$template->assign([
  'ADMIN_PAGE_TITLE' => l10n('Edit photo').' <span class="image-id">#'.$imageId.'</span>',
  'commons_url' => 'https://'.parse_url($conf[WIKIMEDIACOMMONS_ID]['endpoint'])['host'],
  'image_url' => DerivativeImage::url(IMG_MEDIUM, $row),
  'username' => $userPrefs['username'] ?? false,
  'login_url' => $loginUrl,
  'logout_url' => get_root_url().'admin.php?page=plugin-wikimediacommons-oauth&logout',
  'commons_filename' => $row['name'].'.'.pathinfo($row['file'], PATHINFO_EXTENSION),
  'caption' => $row['comment'],
  'wikitext' => getWikitext($row),
]);
$template_handle = WIKIMEDIACOMMONS_ID.'admin_photo';
$template->set_filename($template_handle, __DIR__ . '/photo.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT', $template_handle);

function getWikitext($row): string
{
  $information = "{{Information\n"
    . "| description    = \n"
    . "| date           = {{taken on|" . $row['date_creation'] . "|locationn=}}\n"
    . "| source         = " . get_absolute_root_url().make_picture_url(['image_id' => $row['id']]) . "\n"
    . "| author         = " . $row['author'] . "\n"
    . "| permission     = \n"
    . "}}\n";
  $location = '';
  if (isset($row['latitude'])) {
    $location = "{{location|".$row['latitude']."|".$row['longitude']."}}\n";
  }
  return "== {{int:filedesc}} ==\n"
    . $information
    . $location
    . "\n"
    . "== {{int:license-header}} ==\n"
    // @todo Make the license customisable.
    . "{{cc-by-sa-4.0}}";
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

  $fullPath = realpath($row['path']);
  if (!$fullPath) {
    throw new InvalidArgumentException('Unable to find image file at '.$row['path']);
  }
  
  $userPrefs = userprefs_get_param(WIKIMEDIACOMMONS_ID);
  $authMethod = new OAuthOwnerConsumer(
    $conf[WIKIMEDIACOMMONS_ID]['key'],
    $conf[WIKIMEDIACOMMONS_ID]['secret'],
    $userPrefs['access_key'],
    $userPrefs['access_secret']
  );
  $api = MediaWiki::newFromPage($conf[WIKIMEDIACOMMONS_ID]['endpoint'], $authMethod)->action();
  $uploader = new CommonsFileUploader($api);
  $uploadResult = $uploader->uploadWithResult($title, $fullPath, $text);
  if (isset($uploadResult['upload']['warnings'])) {
    return $uploadResult['upload'];
  }

  // The resulting wiki page name, with 'File:' prefix.
  $filename = $uploadResult['upload']['imageinfo']['canonicaltitle'];
  $wikiUrl = $uploadResult['upload']['imageinfo']['descriptionurl'];

  // Get info.
  $info = $api->request(ActionRequest::simpleGet('query')
    ->setParam('titles', $filename));
  if (!isset($info['query']['pages'])) {
    throw new Exception('Unable to get info about ' . $wikiUrl);
  }
  $pageInfo = array_shift($info['query']['pages']);

  // Caption.
  $mediaId = 'M' . $pageInfo['pageid'];
  $params = [
    'language' => 'en',
    'id' => $mediaId,
    'value' => $caption,
    'token' => $api->getToken(),
  ];
  $api->request(ActionRequest::simplePost('wbsetlabel', $params));

  return [
    'filename' => $filename,
    'url' => $wikiUrl,
  ];
}
