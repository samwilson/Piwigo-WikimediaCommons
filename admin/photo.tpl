
<p>{'With this form you can upload a photo from here to <a href="%s">Wikimedia Commons</a>.'|translate:$commons_url}</p>

{if $login_url}
  <p>
    <a href="{$login_url}">{'Connect to Wikimedia Commons'|translate}</a>
  </p>
{else}
  <p>
    {'You are logged in as %s.'|translate:$username}
    <a href="{$logout_url}">{'Disconnect from Wikimedia Commons'|translate}</a>
  </p>
{/if}

<div class="wikimediacommons-form-wrapper">
  <div class="wikimediacommons-image">
    <img src="{$image_url}" />
  </div>

  <form method="post" action="" class="wikimediacommons-form">
    <p>
      <label for="commons-filename">{'Filename on Commons:'|translate}</label>
      <input type="text" id="commons-filename" name="commons_filename" value="{$commons_filename}" />
    </p>
    <p>
      <label for="caption">{'Caption:'|translate}</label>
      <input type="text" id="caption" name="caption" value="{$caption}" />
    </p>
    <p>
      <label for="wikitext">{'Page wikitext:'|translate}</label>
      <textarea name="wikitext" id="wikitext" rows="20" cols="24">{$wikitext}</textarea>
    </p>
    <p>
      <input type="submit" value="{'Upload to Commons'|translate}" />
    </p>
  </form>
</div>

{html_head}{literal}
<style>
  .wikimediacommons-form-wrapper {
    display: flex;
    flex-direction: row;
  }
  .wikimediacommons-image {
    margin: 1em;
  }
  .wikimediacommons-form {
    flex: 1;
  }
  .wikimediacommons-form label {
    text-align: left;
    display: block;
  }
  .wikimediacommons-form input,
  .wikimediacommons-form textarea {
    display: block;
    width: 100%;
  }
  .wikimediacommons-form input[type="submit"] {
    width: auto;
  }
</style>
{/literal}{/html_head}
