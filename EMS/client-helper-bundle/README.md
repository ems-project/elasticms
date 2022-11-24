ClientHelperBundle
=============

Coding standards
----------------
PHP Code Sniffer is available via composer, the standard used is defined in phpcs.xml.diff:
````bash
composer phpcs
````

PHPStan is run at level 4, you can check for errors locally using:
`````bash
composer phpstan
`````

If you want to regenerate the PHPStan's baseline run the following command:
`````bash
vendor/bin/phpstan analyse --generate-baseline
`````


Documentation
-------------

- [Routing](../master/doc/routing.md)
- [Search](../master/doc/search.md)
- [Twig documentation](../master/doc/twig.md)

### CommonBundle

The ClientHelperBundle has a strong dependency on CommonBundle.
 
[Twig documentation for CommonBundle](https://github.com/ems-project/EMSCommonBundle/blob/master/doc/twig.md)
