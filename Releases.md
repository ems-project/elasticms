## Release 1.15.18
- CoreBundle
  - [1.15.18](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.15.18)
- CommonBundle
  - [1.8.76](https://github.com/ems-project/EMSCommonBundle/releases/tag/1.8.76)

## Release 1.15.17
- CoreBundle
    - [1.15.17](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.15.17)

## Release 1.15.16
- CoreBundle
    - [1.15.16](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.15.16)

## Release 1.15.15
- CoreBundle
    - [1.15.15](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.15.15)

## Release 1.15.14
- CoreBundle
    - [1.15.14](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.15.14)
    
## Release 1.15.13
- CommonBundle
    - [1.8.75](https://github.com/ems-project/EMSCommonBundle/releases/tag/1.8.75)
- ClientHelperBundle
    - [3.7.8](https://github.com/ems-project/EMSClientHelperBundle/releases/tag/3.7.8)

## Release 1.15.12
- CoreBundle
    - [1.15.12](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.15.12)
- ClientHelperBundle
    - [3.7.7](https://github.com/ems-project/EMSClientHelperBundle/releases/tag/3.7.7)

## Release 1.15.11
- CoreBundle
    - [1.15.11](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.15.11)
    
## Release 1.15.10
- CoreBundle
    - [1.15.10](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.15.10)
- CommonBundle
    - [1.8.74](https://github.com/ems-project/EMSCommonBundle/releases/tag/1.8.74)
- ClientHelperBundle
    - [3.7.6](https://github.com/ems-project/EMSClientHelperBundle/releases/tag/3.7.6)

## Release 1.15.9
- CoreBundle
    - [1.15.9](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.15.9)
- CommonBundle
    - [1.8.72](https://github.com/ems-project/EMSCommonBundle/releases/tag/1.8.72)
- ClientHelperBundle
    - [3.7.4](https://github.com/ems-project/EMSClientHelperBundle/releases/tag/3.7.4)

## Release 1.15.6
- CoreBundle
    - [1.15.7](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.15.7)
- CommonBundle
    - [1.8.69](https://github.com/ems-project/EMSCommonBundle/releases/tag/1.8.69)

## Release 1.15.6
- CommonBundle
    - [1.8.68](https://github.com/ems-project/EMSCommonBundle/releases/tag/1.8.68)

## Release 1.15.5
- CoreBundle 
    - [1.15.5](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.15.5)

## Release 1.15.4
- CoreBundle 
    - [1.15.4](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.15.4)
- CommonBundle 
    - [1.8.67](https://github.com/ems-project/EMSCommonBundle/releases/tag/1.8.67)
- ClientHelperBundle 
    - [3.6.3](https://github.com/ems-project/EMSClientHelperBundle/releases/tag/3.6.3)

## Release 1.15.3
- Fixes
    - urlencoded password for doctrine [#115](https://github.com/ems-project/elasticms/pull/115)
- CommonBundle 
    - [1.8.66](https://github.com/ems-project/EMSCommonBundle/releases/tag/1.8.66)

## Release 1.15.2
- CoreBundle
    - [1.15.2](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.15.2)
    
## Release 1.15.1
- Chores
    - typos in documentation [#112](https://github.com/ems-project/elasticms/pull/112)
- CoreBundle
    - [1.15.1](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.15.1)

## Release 1.15.0
- CoreBundle
    - [1.15.0](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.15.0)
    - [1.14.52](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.14.52)
    - [1.14.51](https://github.com/ems-project/EMSCoreBundle/releases/tag/1.14.51)
- ClientHelperBundle 
    - [3.6.0](https://github.com/ems-project/EMSClientHelperBundle/releases/tag/3.6.0)
- CommonBundle 
    - [1.8.63](https://github.com/ems-project/EMSCommonBundle/releases/tag/1.8.63)
    - [1.8.62](https://github.com/ems-project/EMSCommonBundle/releases/tag/1.8.62)
    - [1.8.61](https://github.com/ems-project/EMSCommonBundle/releases/tag/1.8.61)

## Release 1.14.50
- No breaking changes
- Documented that version 1.14.3 has deprecated a lot of environment variables
- New documentation : how to upgrade composer dependencies with our docker php-dev image
- Update to Symfony 4.4.20  
- [X] Common Bundle 1.8.60
    - Fixes:
        - Elastica counter for query with more that 10.000 results
- [X] Core Bundle 1.14.50
    - Features:
        - add command revision time-machine (#607)
        - add ES query option in recompute command (#610)
        - refactored form submission view (#594)
    - Fixes:
        - move interact logic into initialize to correct quiet option bypass (#611)
        - composer install with no reqs (#607)
        - rawDataTransformer isset call (#613)
        - post processing catch fatals (#614)
    - Documentation:
        - add contentType documentation (#612)
    