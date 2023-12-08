# Security

## Form login (coreAPI)

Since version 5.8 an elasticms web application can be secured with a form login.

On submit the application will preform a coreApi login to the environment api.

!> User need be **enabled** and the role api is **not** required for logging in.

1) For forcing authentication on a route set default **_authenticated** to true.
   See [Routing](/dev/client-helper-bundle/routing.md) for more information.

2) create the login route
   
   For using a different name change the environment variable **EMSCH_SECURITY_ROUTE_LOGIN**, default value `emsch_login`.

   ```yaml
      emsch_login:
         config:
            controller: 'emsch.controller.security.login'
            path: '/{_locale}/login'
            requirements: { _locale: 'nl|fr' }
            defaults: { _locale: 'nl' }
            method: [POST,GET]
         template_static: template/login.html.twig
   ```
   
3) define a login template
   ```twig
   {% extends '@EMSCH/template/base.html.twig' %}
   {% trans_default_domain trans_default_domain %}
   
   {% block body %}
       <h1>Login</h1>
       {% if error %}
           <div class="alert alert-danger" role="alert">
               {{ error.message|trans }}
           </div>
       {% endif %}
       {{ form_start(form, { 'attr': { 'class': 'form' } }) }}
       {{ form_row(form.username, { 'row_attr': { 'class': 'form-group' }, 'attr': { 'class': 'form-control' } }) }}
       {{ form_row(form.password, { 'row_attr': { 'class': 'form-group' }, 'attr': { 'class': 'form-control' } }) }}
       {{ form_row(form.submit, { 'attr': { 'class': 'btn btn-primary' } }) }}
       {{ form_end(form) }}
   {% endblock body %}
   ```
   
4) define login translations
   ```yaml
   emsch:
       security:
           exception:
               bad_credentials: 'Bad credentials!'
               error: 'Something went wrong!'
           form:
               username: Username
               password: Password
               submit: Login
   ```

### Example using authenticated user attributes

```twig
{% if app.user %}
   <ul>
     <li>{{ app.user.userIdentifier }}</li>
     <li>{{ app.user.email }}</li>
     <li>{{ app.user.displayName }}</li>
     <li>{{ app.user.circles|join(' | ') }}</li>
     <li>{{ app.user.roles|join(' | ') }}</li>
     {% if is_granted('ROLE_TASK_MANAGER') %}<li>Task manager</li>{% endif %}
   </ul>
   <a href="{{ path("emsch_logout") }}">Logout</a>
{% endif %}
```

## SAML (Single sign on)

For implementing an SSO with SAML protocol, we need a IDP (Identity Provider).
Enable a dev IDP see [dev-env](/getting-started/dev-env.md#identity-provider-idp-keycloak).

The current SSO implementation does only support the login. The logout on the IDP was not required.

We use the following library [onelogin/php-saml](https://github.com/SAML-Toolkits/php-saml/tree/4.1.0)

### Implementation

1) For forcing authentication on a route set default **_authenticated** to true.
   See [Routing](/dev/client-helper-bundle/routing.md) for more information.

2) Create a login and logout button in twig
   ```twig
        {% if app.user %}
            <h1>Welcome {{ app.user.userIdentifier }}</h1>
            <a href="{{ path("emsch_logout") }}">Logout</a>
        {% else %}
            <a href="{{ path("emsch_saml_login") }}">Login</a>
        {% endif %}
   ```
   
3) Combine with a form login

   Place a SAML login button on the form login template.


### Environment variables

| Name                      | Description                                                                         |
|---------------------------|-------------------------------------------------------------------------------------|
| EMSCH_SAML                | bool for enabling SAML                                                              |
| EMSCH_SAML_SP_ENTITY_ID   | unique name of our application known by the IDP                                     |
| EMSCH_SAML_SP_PUBLIC_KEY  | skeleton public key hosted `/saml/metadata`                                         |
| EMSCH_SAML_SP_PRIVATE_KEY | skeleton private key                                                                |
| EMSCH_SAML_IDP_ENTITY_ID  | client name in the IDP                                                              |
| EMSCH_SAML_IDP_PUBLIC_KEY | IDP public key                                                                      |
| EMSCH_SAML_IDP_SSO        | url to the IDP                                                                      |
| EMSCH_SAML_SECURITY       | overwrite [settings](https://github.com/SAML-Toolkits/php-saml/tree/4.0.0#settings) |