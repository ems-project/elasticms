# Installation
## Submissions
The form configuration needs submission handlers in order to work (at least if you want to send your data somewhere).

You can install our default implementations using [SubmissionBundle](https://github.com/ems-project/EMSSubmissionBundle), or build your own handlers (see the docs at [SubmissionBundle](https://github.com/ems-project/EMSSubmissionBundle)).

The SubmissionBundle loads the dependency to this FormBundle! Make sure to install or provide one and then configure the FormBundle as described below.
## Endpoint
Load the iframe endpoint in `routes.yaml`:
```yaml
forms:
  resource: '@EMSFormBundle/Resources/config/routing/form.xml'
```

## Framework configuration
```yaml
framework:
    validation:
        enabled:              true
        translation_domain:   validators
        email_validation_mode: html5
    assets:
        packages:
            emsform:
                json_manifest_path: '%kernel.project_dir%/public/bundles/emsform/manifest.json'
```
