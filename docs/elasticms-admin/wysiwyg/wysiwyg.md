# WYSIWYG

In elasticms you can configure WYSIWYG [profiles](#profiles) and [style sets](#style-sets).
These are used for configurating and styling the [CKEditors](https://ckeditor.com/).

<!-- TOC -->
* [WYSIWYG](#wysiwyg)
  * [Profiles](#profiles)
    * [EMS settings](#ems-settings)
      * [Paste](#paste)
  * [Style sets](#style-sets)
    * [Styles set preview](#styles-set-preview)
<!-- TOC -->

## Profiles

Profiles are attached to an elasticms [user](../user/user.md). The configuration is applied on all WYSIWYG fields.

A profile has a required `name` and `config` json field.

For building the json, the ckeditor [Toolbar Configurator](https://ckeditor.com/latest/samples/toolbarconfigurator/index.html#basic) can be helpful.

[Example full profile config json](../wysiwyg/example_profile.md).

### EMS settings

EMS settings are used over customizing the CKEditor experience.

| Property              | Description                                                                |
|-----------------------|----------------------------------------------------------------------------|
| urlTypes              | Limit the url types  `["url", "anchor", "localPage", "fileLink", "email"]` |
| urlAllContentTypes    | Disable the option `All ContentTypes` on internal url                      |
| urlTargetDefaultBlank | Set target default to _blank. Array with contentType name or/and urlTypes  |
| translations          | See [translations](#translations) section                                  |
| paste                 | See [paste](#paste) section                                                |
| paste.sanitize        | Call html standard [sanitize](../dev/helpers/standard.md#sanitize)         |
| paste.prettyPrint     | Call html standard [prettyPrint](../dev/helpers/standard.md#prettyPrint)   |

```json
{
  "ems": {
    "urlTypes": ["url", "anchor", "localPage", "fileLink", "email"],
    "urlTargetDefaultBlank": ["url", "fileLink", "media_file", "asset"],
    "urlAllContentTypes": true,
    "translations": {
      "nl": {
        "adv_link.selectFileLabel": "Bestand uploaden",
        "common.browseServer": "Bladeren op de server"
      }
    },
    "paste": {
      "sanitize": {
        "block_elements": ["a"],
        "classes": { "allow": ["heading", "paragraph"] }
      },
      "prettyPrint": {
        "drop-empty-elements": true
      }
    }
  }
}
```

#### Paste
If defined on paste (ctrl+v) an ajax call will be preformed, for sanitizing and/or pretty print the paste value.
Only if the value is a html.

**IMPORTANT**: disable the default filtering from ckeditor: `pasteFiler: false`.

### Translations
We can overwrite the labels used inside dialogs, for example the browser server button.
The translation key is prefix by the CKEditor section (pluginName,common,...).
For the moment translations for toolbar buttons is not supported.

## Style sets

Style sets are attached to a WYSIWYG field.
Can be used to overwrite CKEditor settings and user profiles.

| Property                      | Info                                    |
|-------------------------------|-----------------------------------------|
| Name                          | required style set name                 |
| Format tags                   | overwrite CKEditor format tags          |
| Default table class attribute | overwrite default class in table dialog |
| Assets                        | Zip asset file                          |
| Content CSS                   | CSS path inside asset zip               |
| Content JS                    | JS path inside asset zip                |
| Save dir                      | Public path for symlink unzipped asset  |

### Styles set preview

Using the style on a **WysiwygFieldType** you can enable `Styles set preview`.
It will load the CSS and JS files when using the WYSIWYG field.
In revision detail the text content will be rendered inside an iframe including the CSS and JS files.

Since version 5.22.0 the iframe body tag contains data attributes `field`, `field-path`, and `document-url`.
Needed if we want to enable javascript code based on other document fields.

Example generate table of content in preview

```javascript
window.addEventListener("load", () => {
  const lang = document.documentElement.lang
  const { documentUrl: url, field } = document.body.dataset

  if (!url || !lang || !['content_fr', 'content_nl'].includes(field)) return

  async function getDocument(url) {
    const response = await fetch(url)
    return response.ok ? response.json() : null
  }

  getDocument(url).then((json) => {
    const { success, revision } = json
    if (!success || !revision) return

    const generateToc = revision[`generate_toc_${lang}`]
    if (!generateToc) return

    createToc(document.body, options);
    window.parent.postMessage("resize"); // resize the iframe because createToc() will inject new html tags 
    
  }).catch((error) => console.error('Error fetching document:', error));
});
```

> **TIP** 
> 
> The unzipping of the assets zip will be triggered when loading the CKEditor for the first time.
> 
> Updating the asset zip requires loading the WysiwygFieldType (make a new draft and discard).
