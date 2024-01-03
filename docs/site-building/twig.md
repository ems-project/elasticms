# Twig

The ems project heavily uses [TWIG](https:///twig.symfony.com).

- ElasticMS Admin uses twig for: custom dashboards, views, actions, postprocessing, ..
- ElasticMS Web uses twig for creating webpages, redirect, pdf exports, file serving

<!-- TOC -->
* [Twig](#twig)
  * [Twig cheatsheet](#twig-cheatsheet)
  * [Symfony cheatsheet](#symfony-cheatsheet)
  * [elasticMS Common Bundle Extension](#common-bundle) for both ElasticMS Admin and ElasticMS Web
<!-- TOC -->

## Twig cheatsheet

| Tags                                                                |                                                                     |                                                           |                                                               |                                                                 |
|---------------------------------------------------------------------|---------------------------------------------------------------------|-----------------------------------------------------------|---------------------------------------------------------------|-----------------------------------------------------------------|
| [apply](https://twig.symfony.com/doc/3.x/tags/apply.html)           | [deprecated](https://twig.symfony.com/doc/3.x/tags/deprecated.html) | [flush](https://twig.symfony.com/doc/3.x/tags/flush.html) | [import](https://twig.symfony.com/doc/3.x/tags/import.html)   | [use](https://twig.symfony.com/doc/3.x/tags/use.html)           |
| [autoescape](https://twig.symfony.com/doc/3.x/tags/autoescape.html) | [do](https://twig.symfony.com/doc/3.x/tags/do.html)                 | [for](https://twig.symfony.com/doc/3.x/tags/for.html)     | [include](https://twig.symfony.com/doc/3.x/tags/include.html) | [verbatim](https://twig.symfony.com/doc/3.x/tags/verbatim.html) |
| [block](https://twig.symfony.com/doc/3.x/tags/block.html)           | [embed](https://twig.symfony.com/doc/3.x/tags/embed.html)           | [from](https://twig.symfony.com/doc/3.x/tags/from.html)   | [macro](https://twig.symfony.com/doc/3.x/tags/macro.html)     | [with](https://twig.symfony.com/doc/3.x/tags/with.html)         |
| [cache](https://twig.symfony.com/doc/3.x/tags/cache.html)           | [extends](https://twig.symfony.com/doc/3.x/tags/extends.html)       | [if](https://twig.symfony.com/doc/3.x/tags/if.html)       | [set](https://twig.symfony.com/doc/3.x/tags/set.html)         |                                                                 |

| Functions                                                                              |                                                                              |                                                                    |                                                                                              |
|----------------------------------------------------------------------------------------|------------------------------------------------------------------------------|--------------------------------------------------------------------|----------------------------------------------------------------------------------------------|
| [attribute](https://twig.symfony.com/doc/3.x/functions/attribute.html)                 | [cycle](https://twig.symfony.com/doc/3.x/functions/cycle.html)               | [include](https://twig.symfony.com/doc/3.x/functions/include.html) | [random](https://twig.symfony.com/doc/3.x/functions/random.html)                             |
| [block](https://twig.symfony.com/doc/3.x/functions/block.html)                         | [date](https://twig.symfony.com/doc/3.x/functions/date.html)                 | [max](https://twig.symfony.com/doc/3.x/functions/max.html)         | [range](https://twig.symfony.com/doc/3.x/functions/range.html)                               |
| [constant](https://twig.symfony.com/doc/3.x/functions/constant.html)                   | [dump](https://twig.symfony.com/doc/3.x/functions/dump.html)                 | [min](https://twig.symfony.com/doc/3.x/functions/min.html)         | [source](https://twig.symfony.com/doc/3.x/functions/source.html)                             |
| [country_timezones](https://twig.symfony.com/doc/3.x/functions/country_timezones.html) | [html_classes](https://twig.symfony.com/doc/3.x/functions/html_classes.html) | [parent](https://twig.symfony.com/doc/3.x/functions/parent.html)   | [template_from_string](https://twig.symfony.com/doc/3.x/functions/template_from_string.html) |

| Filters                                                                            |                                                                                    |                                                                                    |                                                                              |                                                                              |
|------------------------------------------------------------------------------------|------------------------------------------------------------------------------------|------------------------------------------------------------------------------------|------------------------------------------------------------------------------|------------------------------------------------------------------------------|
| [abs](https://twig.symfony.com/doc/3.x/filters/abs.html)                           | [escape](https://twig.symfony.com/doc/3.x/filters/escape.html)                     | [join](https://twig.symfony.com/doc/3.x/filters/join.html)                         | [number_format](https://twig.symfony.com/doc/3.x/filters/number_format.html) | [timezone_name](https://twig.symfony.com/doc/3.x/filters/timezone_name.html) |     
| [batch](https://twig.symfony.com/doc/3.x/filters/batch.html)                       | [filter](https://twig.symfony.com/doc/3.x/filters/filter.html)                     | [json_encode](https://twig.symfony.com/doc/3.x/filters/json_encode.html)           | [raw](https://twig.symfony.com/doc/3.x/filters/raw.html)                     | [title](https://twig.symfony.com/doc/3.x/filters/title.html)                 |     
| [capitalize](https://twig.symfony.com/doc/3.x/filters/capitalize.html)             | [first](https://twig.symfony.com/doc/3.x/filters/first.html)                       | [keys](https://twig.symfony.com/doc/3.x/filters/keys.html)                         | [reduce](https://twig.symfony.com/doc/3.x/filters/reduce.html)               | [trim](https://twig.symfony.com/doc/3.x/filters/trim.html)                   |  
| [column](https://twig.symfony.com/doc/3.x/filters/column.html)                     | [format](https://twig.symfony.com/doc/3.x/filters/format.html)                     | [language_name](https://twig.symfony.com/doc/3.x/filters/language_name.html)       | [replace](https://twig.symfony.com/doc/3.x/filters/replace.html)             | [u](https://twig.symfony.com/doc/3.x/filters/u.html)                         |   
| [convert_encoding](https://twig.symfony.com/doc/3.x/filters/convert_encoding.html) | [format_currency](https://twig.symfony.com/doc/3.x/filters/format_currency.html)   | [last](https://twig.symfony.com/doc/3.x/filters/last.html)                         | [reverse](https://twig.symfony.com/doc/3.x/filters/reverse.hml)              | [upper](https://twig.symfony.com/doc/3.x/filters/upper.html)                 |   
| [country_name](https://twig.symfony.com/doc/3.x/filters/country_name.html)         | [format_date](https://twig.symfony.com/doc/3.x/filters/format_date.html)           | [length](https://twig.symfony.com/doc/3.x/filters/length.html)                     | [round](https://twig.symfony.com/doc/3.x/filters/round.html)                 | [url_encode](https://twig.symfony.com/doc/3.x/filers/url_encode.html)        |   
| [currency_name](https://twig.symfony.com/doc/3.x/filters/currency_name.html)       | [format_datetime](https://twig.symfony.com/doc/3.x/filters/format_datetime.html)   | [locale_name](https://twig.symfony.com/doc/3.x/filters/locale_name.html)           | [slice](https://twig.symfony.com/doc/3.x/filters/slice.html)                 |                                                                              |    
| [currency_symbol](https://twig.symfony.com/doc/3.x/filters/currency_symbol.html)   | [format_number](https://twig.symfony.com/doc/3.x/filters/format_number.html)       | [lower](https://twig.symfony.com/doc/3.x/filters/lower.html)                       | [slug](https://twig.symfony.com/doc/3.x/filters/slug.html)                   |                                                                              |    
| [data_uri](https://twig.symfony.com/doc/3.x/filters/data_uri.html)                 | [format_time](https://twig.symfony.com/doc/3.x/filters/format_time.html)           | [map](https://twig.symfony.com/doc/3.x/filters/map.html)                           | [sort](https://twig.symfony.com/doc/3.x/filters/sort.html)                   |                                                                              |  
| [date](https://twig.symfony.com/doc/3.x/filters/date.html)                         | [html_to_markdown](https://twig.symfony.com/doc/3.x/filters/html_to_markdown.html) | [markdown_to_html](https://twig.symfony.com/doc/3.x/filters/markdown_to_html.html) | [spaceless](https://twig.symfony.com/doc/3.x/filters/spceless.html)          |                                                                              |   
| [date_modify](https://twig.symfony.com/doc/3.x/filters/date_modify.html)           | [inky_to_html](https://twig.symfony.com/doc/3.x/filters/inky_to_html.html)         | [merge](https://twig.symfony.com/doc/3.x/filters/merge.html)                       | [split](https://twig.symfony.com/doc/3.x/filters/plit.html)                  |                                                                              |     
| [default](https://twig.symfony.com/doc/3.x/filters/default.html)                   | [inline_css](https://twig.symfony.com/doc/3.x/filters/inline_css.html)             | [nl2br](https://twig.symfony.com/doc/3.x/filters/nl2br.html)                       | [striptags](https://twig.symfony.com/doc/3.x/filters/striptags.html)         |                                                                              |

| Tests                                                                  |                                                                  |                                                              |
|------------------------------------------------------------------------|------------------------------------------------------------------|--------------------------------------------------------------|
| [constant](https://twig.symfony.com/doc/3.x/tests/constant.html)       | [empty](https://twig.symfony.com/doc/3.x/tests/empty.html)       | [null](https://twig.symfony.com/doc/3.x/tests/null.html)     |
| [defined](https://twig.symfony.com/doc/3.x/tests/defined.html)         | [even](https://twig.symfony.com/doc/3.x/tests/even.html)         | [odd](https://twig.symfony.com/doc/3.x/tests/odd.html)       |
| [divisibleby](https://twig.symfony.com/doc/3.x/tests/divisibleby.html) | [iterable](https://twig.symfony.com/doc/3.x/tests/iterable.html) | [sameas](https://twig.symfony.com/doc/3.x/tests/sameas.html) |

| Operators                                                                                                                                |
|------------------------------------------------------------------------------------------------------------------------------------------|
| [in](https:///twig.symfony.com/doc/3.x/templates.html#containment-operator)                                                              |
| [is](https:///twig.symfony.com/doc/3.x/templates.html#test-operator)                                                                     |
| [Math](https:///twig.symfony.com/doc/3.x/templates.html#math) (+, -, /, %, //, *, **)                                                    |                                                    
| [Logic](https:///twig.symfony.com/doc/3.x/templates.html#logic) (and, or, not, (), b-and, b-xor, b-or)                                   |                                   
| [Comparisons](https:///twig.symfony.com/doc/3.x/templates.html#comparisons) (==, !=, <, >, >=, <=, ===, starts with, ends with, matches) |
| [Others](https:///twig.symfony.com/doc/3.x/templates.html#other-operators) (.., , ~, ., [], ?:, ??)                                      |

## Symfony cheatsheet

| Tags                                                                                    |
|-----------------------------------------------------------------------------------------|
| [trans](https://twig.symfony.com/doc/3.x/tags/trans.html)                               | 
| [trans_default_domain](https://twig.symfony.com/doc/3.x/tags/trans_default_domain.html) | 

| Filters                                                                  |                                                                      |
|--------------------------------------------------------------------------|----------------------------------------------------------------------|
| [humanize](https://twig.symfony.com/doc/3.x/filters/humanize.html)       | [trans](https://twig.symfony.com/doc/3.x/filters/trans.html)         |
| [yaml_encode](https://twig.symfony.com/doc/3.x/filters/yaml_encode.html) | [yaml_dump](https://twig.symfony.com/doc/3.x/filters/yaml_dump.html) |
| [serialize](https://twig.symfony.com/doc/3.x/filters/serialize.html)     |                                                                      |

| Functions                                                                      |                                                                            |                                                                            |                                                                            |                                                                                |
|--------------------------------------------------------------------------------|----------------------------------------------------------------------------|----------------------------------------------------------------------------|----------------------------------------------------------------------------|--------------------------------------------------------------------------------|
| [absolute_url](https://twig.symfony.com/doc/3.x/functions/absolute_url.html)   | [expression](https://twig.symfony.com/doc/3.x/functions/expression.html)   | [form_label](https://twig.symfony.com/doc/3.x/functions/form_label.html)   | [form_widget](https://twig.symfony.com/doc/3.x/functions/form_widget.html) | [relative_path](https://twig.symfony.com/doc/3.x/functions/relative_path.html) |
| [asset](https://twig.symfony.com/doc/3.x/functions/asset.html)                 | [form](https://twig.symfony.com/doc/3.x/functions/form.html)               | [form_parent](https://twig.symfony.com/doc/3.x/functions/form_parent.html) | [is_granted](https://twig.symfony.com/doc/3.x/functions/is_granted.html)   | [render](https://twig.symfony.com/doc/3.x/functions/render.html)               |
| [asset_version](https://twig.symfony.com/doc/3.x/functions/asset_version.html) | [form_end](https://twig.symfony.com/doc/3.x/functions/form_end.html)       | [form_rest](https://twig.symfony.com/doc/3.x/functions/form_rest.html)     | [logout_path](https://twig.symfony.com/doc/3.x/functions/logout_path.html) | [render_esi](https://twig.symfony.com/doc/3.x/functions/render_esi.html)       |
| [controller](https://twig.symfony.com/doc/3.x/functions/controller.html)       | [form_errors](https://twig.symfony.com/doc/3.x/functions/form_errors.html) | [form_row](https://twig.symfony.com/doc/3.x/functions/form_row.html)       | [logout_url](https://twig.symfony.com/doc/3.x/functions/logout_url.html)   | [url](https://twig.symfony.com/doc/3.x/functions/url.html)                     |
| [csrf_token](https://twig.symfony.com/doc/3.x/functions/csrf_token.html)       | [form_help](https://twig.symfony.com/doc/3.x/functions/form_help.html)     | [form_start](https://twig.symfony.com/doc/3.x/functions/form_start.html)   | [path](https://twig.symfony.com/doc/3.x/functions/path.html)               |                                                                                |


## Common Bundle

The common's twig filters and functions are available in both ElasticMS Admin and in ElasticMS Web


### Filter

#### ems_dom_crawler

This filter parses a string and returns a [Symfony DomCrawler](https://symfony.com/doc/current/components/dom_crawler.html)

Useful to extract content from a html string: `{% set firstP = data.body_fr|ems_dom_crawler.filter('p').first.text %}`

### Function

#### ems_template_exists

Returns true if the template exists. Also for only locally defined templates.

```twig 
{% set templateExists = ems_template_exists('@EMSCH/template/example.twig') %}
```












































































