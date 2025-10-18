# Audit

The command `emscli:web:audit` audits a websites. This command is interruptible and resumable at any moment. This command has 2 outputs:

* Updated documents in a elasticms's admin (via the rest API) compatible with this [content type](/files/contenttype_audit.json ':ignore') 
* An [Excel report](#Report)

This command can perfomed multiple audits but not always all of them on every website's urls. See [Auditors](#Auditors).

Prior using this command, please login to an elasticms admin: `ems:admin:login http://admin.my-elasticms.tld`.

You may want to just an Excel report, without indexing in elasticms, without any instance of elasticms: `emscli:web:audit http://my-website.tld/ --dry-run`

## Report
An Excel report is generated after every tested. The report contains information about the current audit, even is the audit has been resumed. Use the `--continue` flag to resume an audit.

### Broken links's tab
This section reports every broken links. It means http or https links that did returned something else that an 200 as status code.

### Ignored link's tab
This section reports links that have been ignored during the audit. As they have been explicitly excluded (see `--ignore-regex` option). Or as they are not crawlable (such as mailto or javascript urls).

### Warnings
This section reports warning message. Such as the URL is case sensitive or if the request has been redirected.

### Accessibility
This section reports, by auditors, how many accessibility issues ha ve been raised.

### Security
This section reports, by auditors, how many security issues ha ve been raised.

## Auditors

For performance reasons; Tika, pa11y and lighthouse (which are all optionals) audits are performed in parallels. But those auditors are heavy and affects the audit's speed. Turn them on carefully. 

### The request audit

This audit is performed on every crawlable URL (HTTP and HTTPS). Other URLs, such ftp, mailto, javascript are not tested. But they are reported as ignored URLs. See [Report](#Report).

This audit will index in elasticms:

* The url
* The referer's url
* If the URL is case sensitive
* If the request is valid
* If the request returned an error message
* The status code (i.e. 200)
* If some security headers are missing
* The size
* Compute a hash (in order to detect duplicates)


### The HTML crawler audit

This audit is performed on every valid HTML URL's response. This audit is used to introspect internal links in order to add them to the queue.

This audit will index in elasticms:

* External links
* The document's title
* The document's meta title
* The document's canonical link

### The external link audit

This audit is performed on every external link found. It will index in elasticms the:

* The URL
* The status code
* The error message

### The pa11Y audit

This auditor can be activated with the flags `--pa11y` or `--all`.
It only concerns HTML urls.
This auditor collect a [pa11y](https://pa11y.org/) accessibility audit and indexes it in elasticms.  

### The Lighthouse audit

This auditor can be activated with the flags `--lighthouse` or `--all`.
It only concerns HTML urls.
This auditor collect a [Lighhouse](https://developer.chrome.com/docs/lighthouse/overview/) audit and indexes in elasticms:

* The accessibility score
* The performance score
* The SEO score
* The best practice score
* A desktop screenshot
* Warning message
* The report itself

### The tika audit

This auditor can be activated with the flags `--tika` or `--all`. This auditor collect a [tika](https://developer.chrome.com/docs/lighthouse/overview/) audit and indexes in elasticms:

* The locale
* The indexable content
* The document's title
* The document's author

This auditor is also used to extract links/urls form non html responses (i.e. PDF, .docx, ...) in order to test if they are broken. And, for internal links, to add them to the queue.

An additional `--tika-base-url` parameter allows to specify a Tika Server. It's set by default to `http://localhost:9998/`.

So if you have docker you can easily start an instance of Tika Server compatible with this default value:

```bash
docker run -d -p 9998:9998 apache/tika
```
