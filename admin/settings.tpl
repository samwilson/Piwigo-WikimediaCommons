<h3>{'Wikimedia Commons'|translate}</h3>

<div class="wikimediacommons-settings">
  <p>{'In order to export photos to Wikimedia Commons you must first authorize Piwigo.'|translate}</p>
  <p>
      {'To do this, first'|translate}
      <a href="https://meta.wikimedia.org/wiki/Special:OAuthConsumerRegistration/propose/oauth1a">
          {'go to Meta Wiki to set up a new OAuth consumer'|translate}
      </a> with the following permissions:
  </p>
  <ul>
  <li>{'Edit existing pages'|translate}</li>
  <li>{'Create, edit, and move pages'|translate}</li>
  <li>{'Upload new files'|translate}</li>
  <li>{'Upload, replace, and move files'|translate}</li>
  </ul>
  <p>{'Set the callback URL to:'|translate} <code>{$callback_url}</code></p>
  <p>{'Meta Wiki will give you a <em>key</em> and a <em>secret</em>; add these to the form below.'|translate}</p>
  <p>{'Even though you register the OAuth consumer on Meta Wiki, you should use the Commons domain name in the <em>Endpoint URL</em> here, so that your users are directed there to authorise instead of to Meta Wiki.'|translate}</p>

  <form action="{$admin_url}" method="post">
    <input type="hidden" name="page" value="{$wikimediacommons_page}" />
    <input type="hidden" name="action" value="save" />
    <p>
      <label for="endpoint">{'Endpoint URL:'}</label>
      <input type="text" size="80" name="WikimediaCommons[endpoint]" id="endpoint" required="required" value="{$wikimediacommons_conf.endpoint}" />
    </p>
    <p>
      <label for="key">{'Key:'}</label>
      <input type="text" size="80" name="WikimediaCommons[key]" id="key" required="required" value="{$wikimediacommons_conf.key}" />
    </p>
    <p>
      <label for="secret">{'Secret:'}</label>
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
