# Webhook

If you are ready to expose and HTTP endpoint you can register your application to ElasticMS admin's
webhook events.

## Subscribe to a webhook event

Register to the event `content.published.live` and `content.published.staging` :

```shell
curl -X POST -H "X-Auth-Token: EDcTszIHnaaDCpvpi+dJeakj6uOsDqtvSY6rqJyDR3baPpnFA+6u4UAaPcMuJIAfwTs=" http://localhost:8881/api/webhook-subscriptions -d '{
  "endpointUrl": "https://exposed-domain/_admin_webhook",
  "events": [
    "content.published.live",
    "content.published.staging"
  ]
}'
```

In case of success you'll get a JSON with those fields:

- `id`: a UUID of the subscription you just created
- `secret`: a secret that will be used to sign calls to the provided endpoint URL

## Delete a subscription (unsubscribe)

This HTTP call will unsubscribe the subscription for the provided UUID:

```shell
curl -X DELETE -H "X-Auth-Token: EDcTszIHnaaDCpvpi+dJeakj6uOsDqtvSY6rqJyDR3baPpnFA+6u4UAaPcMuJIAfwTs=" http://localhost:8881/api/webhook-subscriptions/08543760-55e5-40e0-8c4f-f3413fad0cb2
```

## Verify calls to the exposed endpoint

Every HTTP call will contain those headers:

- `X-Webhook-Event`: The type of the event
- `X-Webhook-Signature`: The signature that needs to be verified
- `X-Webhook-Subscription-Id`: The UUID of the subscription

At the subscription you received a subscription UUID and a secret. You can use the UUID to retrieve
the secret and check the signature.

Here is PHP/Symfony example of this request validation:

```php
    private function validateWebHookCall(Request $request): void
    {
        $signature = Type::string($request->headers->get('X-Webhook-Signature'));
        $subscriptionId = Type::string($request->headers->get('X-Webhook-Subscription-Id'));
        $secret = $this->cacheManager->getItem(\sprintf('webhook_secret_%s', $subscriptionId)); // Retrieve the corresponding secret from a cache
        if (!$secret->isHit()) {
            throw new GoneHttpException('Unknown webhook subscription'); //returns a 410: Gone HTTP return code
        }
        $hash = \hash_hmac('sha256', Type::string($request->getContent()), Type::string($secret->get()));
        if ($hash !== $signature) {
            throw new Exception(401, 'Invalid Signature'); //returns a 4101: Unauthorized HTTP return code
        }
    }
```

## Structure of a webhook call

Each webhook call's body contains a JSON with two fields:

- `event`: a string containing the type of the webhook event
- `data`: An array specific to event type. See [events](#events) for more information

## Events

### Content Publish to

To register to this event, you have to specify on which environment you want by ending the string
`content.published.` with the environment name. E.g. `content.published.live`.

The structure of the webhook `data` is:

- `environment`: name of the admin's publish environment (e.g. `staging`, `live`, ...). FYI, a
  simple finalize won't fire a `content.published.` webhook for the default environment, see
  [finalize](#finalize).
- `alias`: the elasticsearch's alias corresponding to the publish environment
- `content_type`: name of the content type
- `ouuid`: OUUID of the document published
- `raw_data`: Raw data (array) of the revision published

### Content Unpublish

Type of this event `content.unpublish`. Fire each time that a document draft is unpublished.

The structure of the webhook `data` is:

- `environment`: name of the admin's unpublish environment (e.g. `staging`, `live`, ...). FYI,
  delete won't fire a `content.unpublish.` webhook for the default environment, see delete webhook.
- `alias`: the elasticsearch's alias corresponding to the unpublish environment
- `content_type`: name of the content type
- `ouuid`: OUUID of the document unpublished

### Finalize

Type of this event `content.finalize`. Fire each time that a document draft is finalized.

The structure of the webhook `data` is:

- `environment`: name of the admin's content type default environment (e.g. `preview`, `default`,
  ...).
- `alias`: the elasticsearch's alias corresponding to the content type default environment
- `content_type`: name of the content type
- `ouuid`: OUUID of the document finalized
- `raw_data`: Raw data (array) of the revision finalized
