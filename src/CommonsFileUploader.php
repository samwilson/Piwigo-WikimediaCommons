<?php

namespace Piwigo\Plugin\WikimediaCommons;

use Addwiki\Mediawiki\Api\Client\Action\ActionApi;
use Addwiki\Mediawiki\Api\Client\Action\Request\ActionRequest;
use Addwiki\Mediawiki\Api\Service\FileUploader;

/**
 * A wrapper for FileUploader::upload() that lets us get the API response.
 * @TODO Remove after https://github.com/addwiki/addwiki/issues/86 is resolved.
 */
class CommonsFileUploader extends FileUploader
{
  public function __construct(ActionApi $api)
  {
    parent::__construct($api);
    $this->setChunkSize(90 * 1024 * 1024);
  }

  /**
   * @param string $target_name
   * @param string $location
   * @param string $text
   * @param string $comment
   * @return mixed
   */
  public function uploadWithResult(
    string $target_name,
    string $location,
    string $text = '',
    string $comment = ''
  )
  {
    $params = array(
      'filename' => $target_name,
      'token' => $this->api->getToken(),
      'text' => $text,
      'comment' => $comment,
      'filesize' => filesize($location),
      'file' => fopen($location, 'r'),
    );
    return $this->api->request(ActionRequest::simplePost(
      'upload',
      $this->uploadByChunks($params)
    ));
  }
}
