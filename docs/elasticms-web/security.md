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
     {% if is_granted('ROLE_TASK_MANAGER') %}<li>Task admin</li>{% endif %}
   </ul>
   <a href="{{ path("emsch_logout") }}">Logout</a>
{% endif %}
```

## SSO (Single sign on)

We have two SSO authenticators SAML and OAuth2:
- if enabled the login, logout and callback routes will be registered.
- can be used together, but without login page the entry point will call the Oauth2 first

For implementing an SSO, we need a IDP (Identity Provider).
Enable a dev IDP see [dev-env](/getting-started/dev-env.md#identity-provider-idp-keycloak).

Note: the current SSO implementation does only support the login. The logout on the IDP was not required.

## Implementation

> See the demo project for a full implementation.

1) For forcing authentication on a route set default **_authenticated** to true.
   See [Routing](/dev/client-helper-bundle/routing.md) for more information.
2) Enable OAuth2 or SAML
3) Visit the authentication route, if both protocols are enabled only OAuth2 will be called.
4) Add buttons on login page (optional)
   ```twig
   {% if app.user %}
      <h1>Welcome {{ app.user.userIdentifier }}</h1>
      <a href="{{ path("emsch_logout") }}">Logout</a>
   {% else %}
      {% if 'true' == app.request.server.get('EMSCH_SAML') %}
          <a href="{{ path('emsch_saml_login') }}">SSO SAML</a>
      {% endif %}
      {% if 'true' == app.request.server.get('EMSCH_OAUTH2') %}
          <a href="{{ path('emsch_oauth2_login') }}">SSO OAuth2</a>
      {% endif %}
   {% endif %}
   ```

## OAuth2 (OpenId connect)

> OIDC is a simple identity layer built on top of OAuth 2.0, adding authentication capabilities.

Elasticms web has support for:

- Keycloak [stevenmaguire/oauth2-keycloak](https://github.com/stevenmaguire/oauth2-keycloak)
- Azure [TheNetworg/oauth2-azure](https://github.com/TheNetworg/oauth2-azure)

These clients are just an implementation of [thephpleague/oauth2-client](https://github.com/thephpleague/oauth2-client)

### Keycloak

| Name                              | Description                    |
|-----------------------------------|--------------------------------|
| EMSCH_OAUTH2                      | bool for enabling OAUTH2       |
| EMSCH_OAUTH2_AUTH_SERVER          | Keycloak server url            |
| EMSCH_OAUTH2_REALM                | Keycloak REALM                 |
| EMSCH_OAUTH2_CLIENT_ID            | Keycloak client id             |
| EMSCH_OAUTH2_CLIENT_SECRET        | Keycloak client secret         |
| EMSCH_OAUTH2_REDIRECT_URI         | https://mywebsite/callback-url |
| EMSCH_OAUTH2_VERSION              | Optional: keycloak version     |
| EMSCH_OAUTH2_ENCRYPTION_ALGORITHM | Optional: RS256,ES256,...      |
| EMSCH_OAUTH2_ENCRYPTION_KEY       | Optional: base64 encode        |

> For encryption see [Identity provider (IDP) (Keycloak)](http://localhost:3000/#/getting-started/dev-env?id=identity-provider-idp-keycloak)

### Azure

| Name                       | Description                                           |
|----------------------------|-------------------------------------------------------|
| EMSCH_OAUTH2               | bool for enabling OAUTH2                              |
| EMSCH_OAUTH2_PROVIDER      | 'azure' (default = 'keycloak')                        |
| EMSCH_OAUTH2_REALM         | Tenant id                                             |
| EMSCH_OAUTH2_CLIENT_ID     | Client id                                             |
| EMSCH_OAUTH2_CLIENT_SECRET | Client secret value                                   |
| EMSCH_OAUTH2_REDIRECT_URI  | https://mywebsite/callback-url                        |
| EMSCH_OAUTH2_VERSION       | Default value = 2.0                                   |
| EMSCH_OAUTH2_SCOPES        | Default value = ["openid","profile","offline_access"] |

Create an app in the [Microsoft entra admin centrum](https://entra.microsoft.com) (Home > App registrations)

Tenant id & client id you should find on the overview page of the newly created app.

Client secret should be created (App registrations > my app > Certificates & secrets)

Important:
- for retrieving a `refresh_token`, scope offline_access is required
- add optional claim for `upn` (App registrations > my app > Token configuration),
  because elasticms uses the upn for the username
- Add yammer scopes will create a new service token:
  EMSCH_OAUTH2_SCOPES='["openid","profile","offline_access","https://api.yammer.com/user_impersonation"]'
  
#### Azure twig example

```twig
{% set oauth2 = app.token %}

{% set me = ems_http(
   'https://graph.microsoft.com/v1.0/me', 
   'GET',
   {'headers': { 'Authorization': "Bearer #{oauth2.token}" } }
).content|ems_json_decode %}

{% set events = ems_http(
   'https://graph.microsoft.com/v1.0/me/events?$select=subject,body,bodyPreview,organizer,attendees,start,end,location',
   'GET',
   { 'headers': { 'Authorization': "Bearer #{oauth2.token}" } }
).content|ems_json_decode %}

{# for yammer EMSCH_OAUTH2_SCOPES needs to contain https://api.yammer.com/user_impersonation #}
{% set yammerUser = ems_http(
   'https://www.yammer.com/api/v1/users/current.json',
   'GET',
   { 'headers': { 'Authorization': "Bearer #{oauth2.token('api.yammer.com')}" } }
).content|ems_json_decode %}

{% set yammerMessages = ems_http(
   'https://www.yammer.com/api/v1/messages.json',
   'GET',
   { 'headers': { 'Authorization': "Bearer #{oauth2.token('api.yammer.com')}" } }
).content|ems_json_decode %}
```

## SAML (Security Assertion Markup Language)

> SAML is an XML-based framework for exchanging authentication and authorization data between parties.

We use the following library [onelogin/php-saml](https://github.com/SAML-Toolkits/php-saml/tree/4.1.0)

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
