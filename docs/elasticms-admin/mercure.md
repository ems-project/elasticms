# Mercure

Since version 6.4.0 the core can publish message on the mercure hub.

## Requirements

- Define [EMSCO_URL_USER](/elasticms-admin/environment-variables.md#EMSCO_URL_USER) environment variable.
- Define mercure [environment variables](/elasticms-admin/environment-variables.md#symfony-mercure) are defined.

## Example implementation

We could add the following javascript code in a dashboard or view

```javascript
const userId = {{ app.user.getId }};
    
fetch('/mercure/token')
    .then(response => response.json())
    .then(data => {
        const url = new URL(data.url);
        url.searchParams.append('topic', 'http://localhost:8881/notifications');
        url.searchParams.append('topic', `http://localhost:8881/user/${userId}`);
        url.searchParams.append('authorization', data.token);

        const eventSource = new EventSource(url);
        eventSource.onmessage = event => {
            console.log('Received:', event.data, event.type);
        };
        eventSource.onerror = (error) => {
            console.error('EventSource failed:', error);
        };
    });
```

On the service side we can you the Mercure service for publishing data to the hub.

```php
use EMS\CoreBundle\Core\Mercure\MercureService;
use EMS\CoreBundle\Core\User\UserManager;

# Send a message to all Users
new MercureService()->publish(['message' => 'Hello'], MercureService::TOPIC_NOTIFICATIONS);

# Send a message to the authenticated user.
$user = new UserManager()->getAuthenticatedUser();
new MercureService()->publishForUser($user, ['message' => "Hello {$user->getDisplayName()}"]);
```
