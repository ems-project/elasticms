# Export as word

This document aims to walk you through the steps of creating a word export feature in the admin and in the frontend.<br/>
In both cases, you will need to create an html template that will used by Word to open the document.<br><br>

For this, we are going to use the _Office URI Scheme_ _ms-word_ (You can find out more about those schemes and the different arguments [here](https://docs.microsoft.com/en-us/office/client-developer/office-uri-schemes)), which follows the notation below:<br>
_< scheme-name >:< command-name >"|"< command-argument-descriptor > "|"< command-argument >_<br><br>

This scheme will then be used as url in a _\<a>_ tag.<br><br>

This feature allows you to export either a page on its own, or a page within a structure. In that case, children of the page will be displayed as appendix at the end of the document.

##Configuration
For both admin and frontend, you are going to need to create an html that can be read by Word.<br>
Note that you can also stylise the document to your needs, but keep in mind that not all CSS is recognized by Word.<br><br>

The CSS needs to be added **inline**.

###1. Route
Create a route, which will query ElasticSearch to look for the page itself or the page within its structure.<br>
Note here that a boost has been added to the structure to get it as first result.
For SEO purposes, you should **_disallow_** this route in your _robots.txt_


`````yaml
export_word:
    config:
        path: '/_export_word/{contentType}/{id}.docx'
        requirements: { path: '^(?!(_wdt|_profiler|file)(\/.*$)?).+', _locale: en|fr|nl|de }
    query: '{"query":{"bool":{"should":[{"bool":{"must":[{"terms":{"_contenttype":["%contentType%"]}},{"term":{"_id":"%ouuid%"}}]}},{"bool":{"must":[{"nested":{"path":"paths","query":{"term":{"paths.target":{"value":"%contentType%:%ouuid%","boost":3}}}}},{"nested":{"path":"paths","query":{"term":{"paths.locale":{"value":"%_locale%"}}}}},{"terms":{"_contenttype":["structure"]}}]}}]}},"size":1}'
    template_static: template/page/word-export/document.html.twig
`````

###2. Implement html to be converted to Word
You can find the template of the word export in _skeleton/template/page/word-export_.<br>

The feature consists of 3 files:<br>

- css.twig : Allows you to stylise the export. The file will then be included inline in the export html structure
- document.html.twig : Holds the html structure. Note that the content to be exported needs to be held in a table, to allow the addition of a header and a footer.<br>
The export demo does not have a header and a footer. To implement it, I invite you to follow [these steps](https://mathieu.dekeyzer.net/blog/2021-05-29/handle-page-margin-with-css)
- export.twig : Holds the variables used in _document.html.twig_

If your page is in a structure, the children of this page will ba added as appendix at the end of the document. Each child will be on a new page.
</ul>


## Use the export in the admin
To use the export in the admin, you'll have to create a new action in the content type that you wish to export.<br>
For that, in the admin, go to _Content types_ menu, and clic on _Actions_ of the CT of your choice.<br>
Clic then on _Add a new action_ and fill in the fields. The _render option_ should be **_Raw HTML_**.<br><br>

The body consists of a link that will target the html created above. <br>
````twig
{%- set locale = 'fr' -%}
{% if attribute(source, locale).slug|default(false) %} 
    {% if 'localhost' in app.request.host %}
        <a href="{{ "ms-word:ofe|u|http://demo-preview.#{target}.localhost/_export_word/#{source._contenttype}/#{object._id}.docx" }}"><i class="fa fa-file-word-o"></i> Export Word FR </a>
    {% else %}
        {% set wordBaseUrl = (environment.name == 'live' ? 'https' : app.request.scheme) ~ "://#{app.request.host}"  %}
        <a href="{{ "ms-word:ofe|u|#{wordBaseUrl}/channel/#{environment.name}/_export_word/#{source._contenttype}/#{object._id}.docx" }}"><i class="fa fa-file-word-o"></i> Export Word FR </a>
    {% endif %}
{% endif %}
````

Save and close.

## Use the export in the frontend
To use the export in the frontend, you'll just have to create an <a> tag pointing to the route you've created in step 1.<br>

Here is an example:
````twig
  {% if 'localhost' in app.request.host %}
      <a href="{{ "ms-word:ofe|u|http://demo-preview.#{target}.localhost/_export_word/#{source._contenttype}/#{object._id}.docx" }}"><i class="fa fa-file-word-o"></i> Export Document </a>
  {% else %}
      {% set wordBaseUrl = (environment.name == 'live' ? 'https' : app.request.scheme) ~ "://#{app.request.host}"  %}
      <a href="{{ "ms-word:ofe|u|#{wordBaseUrl}/channel/#{environment.name}/_export_word/#{source._contenttype}/#{object._id}.docx" }}"><i class="fa fa-file-word-o"></i> Export Document </a>
  {% endif %}
````
