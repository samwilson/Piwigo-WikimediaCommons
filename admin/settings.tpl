<div class="wikimediacommons-settings">
  <h3>{'Configure Piwigo to connect to Wikimedia Commons'|translate}</h3>
  <p>{'In order to export photos to Wikimedia Commons you must first authorize Piwigo.'|translate}</p>
  <p>
      {capture name=some_content assign=oauth_link}
      <a href="https://meta.wikimedia.org/wiki/Special:OAuthConsumerRegistration/propose/oauth1a">
          {'go to Meta Wiki to set up a new OAuth 1.0a consumer'|translate}
        </a>
      {/capture}
      {'To do this, first %s with the following grants:'|translate:$oauth_link}
  </p>
  <ul>
    {* @todo Pull translations for these from MediaWiki. *}
    <li>Edit existing pages</li>
    <li>'Create, edit, and move pages</li>
    <li>Upload new files</li>
    <li>Upload, replace, and move files</li>
  </ul>
  <p>{'Set the callback URL to:'|translate}</p>
  <blockquote><code>{$callback_url}</code></blockquote>
  <p>{'Make sure that <em>"Allow consumer to specify a callback in requests"</em> is checked.'|translate}</p>
  <p>{'Meta Wiki will give you a <em>key</em> and a <em>secret</em>; add these to the form below.'|translate}</p>
  <p>{'Even though you register the OAuth consumer on Meta Wiki, you should use the Commons domain name in the <em>Endpoint URL</em> here, so that your users are directed there to authorise instead of to Meta Wiki.'|translate}</p>

  <form action="{$admin_url}" method="post">
    <input type="hidden" name="page" value="{$wikimediacommons_page}" />
    <input type="hidden" name="action" value="save" />
    <p>
      <label for="endpoint">{'Endpoint URL:'|translate}</label>
      <input type="text" size="80" name="WikimediaCommons[endpoint]" id="endpoint" required="required" value="{$wikimediacommons_conf.endpoint}" />
    </p>
    <p>
      <label for="key">{'Key:'|translate}</label>
      <input type="text" size="80" name="WikimediaCommons[key]" id="key" required="required" value="{$wikimediacommons_conf.key}" />
    </p>
    <p>
      <label for="secret">{'Secret:'|translate}</label>
      <input type="text" size="80" name="WikimediaCommons[secret]" id="secret" required="required" value="{$wikimediacommons_conf.secret}" />
    </p>
    <p class="wikimediacommons-save">
      <input type="submit" value="{'Save'|translate}" />
      <a href="{$admin_url}" class="buttonLike">{'Cancel'|translate}</a>
    </p>
  </form>
</div>

{html_style}
  .wikimediacommons-settings { max-width:60em; margin:auto; }
  .wikimediacommons-settings p,
  .wikimediacommons-settings form p { text-align:left; margin:0.5em 0; }
  .wikimediacommons-settings label { display:block; }
  .wikimediacommons-settings form { margin:0; }
{/html_style}
