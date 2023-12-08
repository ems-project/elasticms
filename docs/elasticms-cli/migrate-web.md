# WebToElasticms

With this Symfony single command, you can update elasticms documents by tracking web resources.

Usage 
 - `php bin/console emscli:web:migrate https://my-elasticms.com /path/to/a/json/config/file.json`

If you are not using a Linux environment, we suggest you to use a PHP docker image. I.e. under Windows with Docker Desktop: 

`docker run -it -v %cd%:/opt/src -w /opt/src elasticms/base-php-dev:7.4`
`php -d memory_limit=-1 bin/console ems:admin:login https://my-elasticms.com` 
`php -d memory_limit=-1 bin/console ems:admin:migrate /opt/src/config.json --cache-folder=/opt/src/cache --rapports-folder=/opt/src`

The JSON config file list all web resources to synchronise for each document.

```json
{
  "documents": [
    {
      "resources": [
        {
          "url": "https://fqdn.com/fr/page",
          "locale": "fr",
          "type": "infopage"
        },
        {
          "url": "https://fqdn.com/nl/page",
          "locale": "nl",
          "type": "infopage"
        },
        {
          "resources": [
            {
              "url": "http://www.inami.fgov.be/fr/themes/grossesse-naissance/maternite/Pages/repos-maternite-salariees-chomeuses.aspx",
              "locale": "fr",
              "type": "link"
            }
          ],
          "type": "link",
          "defaultData": {
            "fr": {
              "url": "http://www.inami.fgov.be/fr/themes/grossesse-naissance/maternite/Pages/repos-maternite-salariees-chomeuses.aspx",
              "label": "Repos de maternit\u00e9 pour les salari\u00e9es (INAMI)"
            },
            "nl": {
              "url": "http://www.inami.fgov.be/nl/themas/zwangerschap-geboorte/moederschap/Paginas/moederschapsrust-werkneemsters-werklozen.aspx",
              "label": "Moederschapsrust voor werkneemsters (RIZIV)"
            },
            "de": {
              "url": "http://www.inami.fgov.be/nl/themas/zwangerschap-geboorte/moederschap/Paginas/moederschapsrust-werkneemsters-werklozen.aspx",
              "label": "Repos de maternit\u00e9 pour les salari\u00e9es (LIKIV)"
            }
          }
        }
      ]
    }
  ],
  "analyzers": [
    {
      "name": "infopage",
      "type": "html",
      "extractors": [
        {
          "selector": "div.field-name-body div.field-item",
          "property": "[%locale%][body]",
          "filters": [
            "internal-link",
            "style-cleaner",
            "class-cleaner",
            "tag-cleaner"
          ]
        },
        {
          "selector": "h1",
          "property": "[%locale%][title]",
          "filters": [
            "striptags"
          ]
        },
        {
          "selector": "#block-system-main > div > ul > li > a",
          "property": "[internal_links]",
          "filters": [
            "data-link:link"
          ],
          "attribute": "href",
          "strategy": "n"
        },
        {
          "selector": "#block-system-main > div > div.institutions > div > div > ul > li",
          "property": "[author]",
          "filters": [
            "data-link:institution"
          ],
          "attribute": null,
          "strategy": "n"
        },
        {
          "selector": "#slwp_ctl00_PlaceHolderLeftNavBar_PlaceHolderQuickLaunchBottom_page_navigation_pagelinks_page_navigation_pagelinks > div > div > ul > li > div > a",
          "property": "[temp][links][%locale%]",
          "filters": [],
          "attribute": "href",
          "strategy": "n"
        },
        {
          "selector": "#slwp_ctl00_PlaceHolderLeftNavBar_PlaceHolderQuickLaunchBottom_page_navigation_pagelinks_page_navigation_pagelinks > div > div > ul > li > div > a",
          "property": "[temp][links_label][%locale%]",
          "filters": [],
          "attribute": null,
          "strategy": "n"
        },
        {
            "selector": "#block-system-main > article > section > div.field.field-name-document-files-associated.field-type-ds.field-label-hidden > div > div > div > div > div > div.views-field.views-field-field-date > span > span",
            "property": "[temp][files][file_info][date]",
            "filters": [],
            "attribute": "content",
            "strategy": "n"
         },
         {
            "selector": "#block-system-main > article > section > div.field.field-name-document-files-associated.field-type-ds.field-label-hidden > div > div > div > div > div > div.views-field.views-field-nothing-1 > span > a",
            "property": "[temp][files][file_info][%locale%][long_title]",
            "filters": [],
            "attribute": null,
            "strategy": "n"
         },
         {
            "selector": "#block-system-main > article > section > div.field.field-name-document-files-associated.field-type-ds.field-label-hidden > div > div > div > div > div > div.views-field.views-field-nothing-1 > span > a",
            "property": "[temp][files][file_info][%locale%][file]",
            "filters": [
               "src"
            ],
            "attribute": "href",
            "strategy": "n"
         },
         {
            "selector": "div#relatedPages ul li a",
            "property": "[temp][%locale%][related_pages]",
            "filters": [
               "data-link:link"
            ],
            "attribute": "href",
            "strategy": "n"
         }
      ]
    },
    {
      "name": "link",
      "type": "empty-extractor",
      "extractors": []
    }
  ],
  "validClasses": ["toc"],
  "styleValidTags": [
    "table",
    "th",
    "tr",
    "td",
    "img"
  ],
  "linkToClean": ["/^\\/fr\\/glossaire/"],
  "types": [
    {
      "defaultData": [],
      "name": "infopage",
      "computers": [
        {
          "property": "[en][show]",
          "expression": "data.get('en.title') !== null",
          "jsonDecode": false,
          "condition": "true"
        },
        {
          "property": "[en][aspx_url]",
          "expression": "document.getResourcePathFor('en')",
          "jsonDecode": false,
          "condition": "true"
        },
        {
          "property": "[themes]",
          "expression": "data.get('themes') == '' ? null : datalinks(split('/([a-zA-Z\u00e9\u00e8\u00e0\\-][a-zA-Z \u00e9\u00e8\u00e0\\-]+)\\\\|[0-9a-f]{8}\\-[0-9a-f]{4}\\-[0-9a-f]{4}\\-[0-9a-f]{4}\\-[0-9a-f]{12} */',data.get('themes')),'taxonomy')",
          "jsonDecode": false,
          "condition": "true"
        },
        {
          "property": "[target_groups]",
          "expression": "data.get('target_groups') == '' ? null : datalinks(match('/\\\\|(?P<matches>[0-9a-f]{8}\\-[0-9a-f]{4}\\-[0-9a-f]{4}\\-[0-9a-f]{4}\\-[0-9a-f]{12})/',data.get('themes')),'taxonomy')",
          "jsonDecode": false,
          "condition": "true"
        },
        {
          "property": "[links]",
          "expression": "list_to_json_menu_nested(data.get('temp.links'), 'link_url', 'link', data.get('temp.links_label'), 'label', true)",
          "jsonDecode": false,
          "condition": "true"
        },
        {
           "property": "[files]",
           "expression": "array_to_json_menu_nested(data.get('temp.files'),  { 'file_info' : [ 'date' , { 'fr' : ['long_title', 'file'] } , { 'nl' : ['long_title', 'file'] } ] })",
           "jsonDecode": false,
           "condition": "true"
        },
        {
           "property": "[related_pages]",
           "expression": "merge(data.get('temp.fr.related_pages'), data.get('temp.nl.related_pages'))",
           "jsonDecode": false,
           "condition": "true"
        }
      ],
      "tempFields": [
        "temp"
      ]
    }
  ],
  "urlsNotFound": [
    "\/fr\/page-not-found"
  ],
  "linksByUrl": {
    "\/": "ems:\/\/object:page:xaO1YHoBFgLgfwq-PbIl"
  },
  "documentsToClean": {
    "page": [
      "w9WS4X0BFgLgfwq-9hDd",
      "y9YG4X0BeD9wLAROUfIV"
    ]
  },
  "dataLinksByUrl": {
    "institution": {
      "https://www.mi-is.be/": "institution:8OCq1H4BFgLgfwq-rYNZ",
      "CAAMI - HZIV": "institution:EuCt1H4BFgLgfwq-dYSB",
      "FEDRIS": "institution:Yd81vH4BFgLgfwq-nlw3"
    },
    "link": {
      "https://www.socialsecurity.be/citizen/fr/static/infos/general/index.htm": "link:X2AZan8BEIZ5tnyYFMjp",
      "https://www.socialsecurity.be/citizen/nl/static/infos/general/index.htm": "link:X2AZan8BEIZ5tnyYFMjp"
    }, 
    "taxonomy": {
      "Professionnel de la sant\u00e9": "taxonomy:225a10bd9a798223bebd6706ff33d906612db064",
      "Zorgverlener": "taxonomy:225a10bd9a798223bebd6706ff33d906612db064",
      "Fournisseurs de logiciels": "taxonomy:c9351d239bc898074b1d792719a13aa88d10db1a",
      "Softwareleveranciers": "taxonomy:c9351d239bc898074b1d792719a13aa88d10db1a",
      "Accidents m\u00e9dicaux": "taxonomy:3544b80796d20c4c2dbee140f362cfdd64f5e5c1",
      "Medische ongevallen": "taxonomy:3544b80796d20c4c2dbee140f362cfdd64f5e5c1",
      "Contr\u00f4le": "taxonomy:e988877e606a48a1886f7c89a1ac5c1e463e2e31",
      "Controle": "taxonomy:e988877e606a48a1886f7c89a1ac5c1e463e2e31"
    }
  },
  "cleanTags": [
    "h1",
    "img"
  ]
}
```

## Import assets as documents

Instead of migrate asset files (PDF, docx, ...), that are in WYSIWYG field, has raw asset, you may want to migrate them in specific content types.
To do so you can define the `htmlAsset2Document` configuration's attribute like this:

```json
{
   ...,
   "htmlAsset2Document": [
      {
         "file_field": "media_file",
         "folder_field": "media_folder",
         "path_field": "media_path",
         "regex": "/^\\/sites\\/default\\/files\\/assets\\//",
         "content_type": "media_file"
      }
   ]
}
```

Each time that an internal link, starting by /sites/default/files/assets/, is found in the WYSIWYG the asset will be imported as a `media_file` document.
And the link, within the WYSIWYG field will be replaced by `ems://object:media_file:ouuid-aaaaaaa` link.
Instead of a `ems://asset:filehash` link.
The `media_file` documents generated are compatible with the `media_library` component.
Also the migration command will ensure that a `media_library` exists for all parent's directories.


## Filters

### class-cleaner

This filter remove all html class but the ones defined in the top level `validClasses` attribute. 

### internal-link

This filter convert internal links. A link is considered as an internal link if the link is relative, absolute or share the host with at least one resource. Internal link are converted following the ordered rules :
 - Link with a path matching at least on regex defined in the top level `linkToClean` attribute.
 - Link where the path match one of the resource with be converted to an ems link to document containing the resource
 - Link to an asset that is not a text/html are converte to an ems link to the asset (and the asset is uplaoded)

### style-cleaner

This filter remove all style attribute. 


### striptags

This filter extract the text and remove all the rest

### tag-cleaner

The filter remove all tag html define in cleanTags (h1 are a value by default) .

### data-link => data-link:category

This filter convert a string to data link. Data link are converted following the ordered rules :
- string matching at least defined in `dataLinksByUrl` for a given category in filter `data-link:category`.
- string maybe a path and where the path match one of the resource with be converted to a data link to document containing the resource


## Types

### tempFields

Array of string used to remove field from the data in order to not sent them to elasticms. It may append that you used temporary fields in order to save extractor values and used those values in computers. 

### Computer

#### Expression

Those parameters are using the [Symfony expression syntax](https://symfony.com/doc/current/components/expression_language/syntax.html)

Functions available: 
 - `uuid()`: generate a unique identifier
 - `json_escape(str)`: JSON escape a string 
 - `date(format, timestamp)`: Format a date 
 - `strtotime(str)`: Convert a string into a date 
 - `pa11y(url)`: Use the Pa11y npm package to accessibility audit the url. Returns a json string 
 - `dom_to_json_menu(html, tag, fieldName, typeName, labelField)`: Convert an HTML/WYSIWYG string into a JSON nested menu
   - `html`: The HTML string to convert
   - `tag`: Will split into item each time that this tag is meet. The text value will be used as item's label
   - `fieldName`: The WYSIWYG item object's field
   - `typeName`: The item's type (see in the JSONNestedMenu configuration)
   - `labelField`: May also copy the label into another object text field
   - Example `dom_to_json_menu(data.get('temp.fr.body'), 'h2', 'body', 'paragraph', 'title')`
 - `split(pattern, str, limit = -1, flags = PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY)`: Split string by a regular expression (preg_split)
 - `datalinks(values, type)`: values(string|array) find each key=>value in `dataLinksByUrl[type]`
 - `list_to_json_menu_nested(values, fieldName, typeName, labels, labelFields, multiplex)`:
   - `values`: Array of values
   - `fieldName`: The item object's field
   - `typeName`: The item's type (see in the JSONNestedMenu configuration)
   - `labels`: Array of labels (corresponding to array of values)
   - `labelField`: May also copy the label into another object text field
   - `multiplex`: Boolean - indicates if include in multiplex field (need to extract locale in last position `[temp][links][%locale%]`)
   - Example `list_to_json_menu_nested(data.get('temp.links'), 'link_url', 'link', data.get('temp.links_label'), 'label', true)`
 - `array_to_json_menu_nested($values, $keys)`: construct a json menu nested with several fields in object
   - `values`: Array of values
   - `keys`: Array of keys (first element key are type of name of key = name of field need to be imported and need exactly the same in values)
   - Example `"array_to_json_menu_nested(data.get('temp.files'),  { 'file_info' : [ 'date' , { 'fr' : ['long_title', 'file'] } , { 'nl' : ['long_title', 'file'] } ] })"`
 - `merge(arr1,arr2)`: Merge arrays

Variable available
 - `data` an instance of [ExpressionData](https://github.com/ems-project/elasticms/blob/4.x/elasticms-cli/src/Client/WebToElasticms/Helper/ExpressionData.php)
 - `document` an instance of [Document](https://github.com/ems-project/elasticms/tree/4.x/elasticms-cli/src/Client/WebToElasticms/Config)

