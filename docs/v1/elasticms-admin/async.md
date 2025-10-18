# Async (Messenger & Mercure)

Starting from version 6.4.0, the admin can dispatch messages to both a queue and
Mercure.

The Messenger component enables offloading tasks to a queue.
Supported queues include (but are not limited to) database, Redis, and RabbitMQ.
We prefer Redis as it is already used for caching in our system.

If a worker fails to consume or handle a message, the message is
stored in the `messenger_messages` table in the database. This allows for easy
debugging of failed messages using Symfony commands.

Mercure is used to dispatch messages back to the client.
The client first requests a token (to subscribe) and will then receive real-time
updates when the message is handled. This uses the
standard [EventSource](https://developer.mozilla.org/en-US/docs/Web/API/EventSource)
JavaScript API.

When you run `make start` in the mono repository, the Mercure service will be
started automatically, along with a background worker process for the Messenger
component. This ensures that dispatched messages are processed and real-time
updates are delivered to subscribed clients.

## Requirements

Define the following environment variables:

* [EMSCO_URL_USER](/elasticms-admin/environment-variables.md#EMSCO_URL_USER)
* [Mercure](/elasticms-admin/environment-variables.md#symfony-mercure)
* [Messenger](/elasticms-admin/environment-variables.md#symfony-messenger)

## Messenger commands

```bash
cd elasticms-admin
# Stats
php bin/console messenger:stats
# Gracefully stop all workers (it will first handle running jobs)
php bin/console messenger:stop-workers
# Start a consume worker in verbose mode
php bin/console messenger:consume async -vvv
# Show failed messages
php bin/console messenger:failed:show
# Show failed message with id 50
php bin/console messenger:failed:show 50 -vvv
# Delete all failed messages
php bin/console messenger:failed:remove --all
```

To profile, run the following command. It will stop after handling a message,
allowing you to visit the Symfony profiler.

```bash
cd elasticms-admin
# Start a consume for profiling
php bin/console  messenger:consume async --limit=1 --profile --no-reset
```

## First implementation example

We introduced a field type called `ActionType`. This field renders as a
button that triggers an asynchronous action. The button is only available in
draft mode (edit/create).

When the user clicks the button, a new `ActionMessage` is dispatched to the
queue. The message includes a request based on the input fields and defines a
response schema.

In this example, we use the OpenAI platform to tryout translation requests.

This action requires an `openai` entry in your keystore.

```.dotenv
EMS_KEY_STORE='{"openai":"...."}'
```

### Action config for the field

```json
{
  "ai": {
    "provider": "openai",
    "request": {
      "model": "gpt-4.1-nano",
      "input": [
        {
          "role": "system",
          "content": "You are a translation engine that translates text from Dutch to French. Keep all HTML tags and entities exactly as they are. Only translate the visible text content. Do not add or remove any tags."
        },
        {
          "role": "user",
          "content": "%inputJson%"
        }
      ]
    }
  },
  "input": [
    "page_title_nl",
    "page_description_nl"
  ],
  "output": [
    "page_title_fr",
    "page_description_fr"
  ]
}
```
