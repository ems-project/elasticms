# Custom MCP prompts

ElasticMS can expose custom MCP prompts directly from the admin interface. These prompts are managed
in **MCP → MCP prompts** and are stored as `EMS\CoreBundle\Entity\McpPrompt` entities.

Prompts are useful for reusable instructions that guide an MCP client through a specific editorial
or business workflow. Unlike tools, prompts do not execute an action directly. They return one or
more messages that the client can use as a starting point, optionally with arguments provided by the
user.

## Where to configure custom prompts

In the ElasticMS admin, open **MCP → MCP prompts**.

A custom prompt is available to the MCP server when:

- the `enabled` flag is set to `true`
- the current MCP user has the configured `role`
- the `arguments` Twig template is empty or renders a valid JSON array of MCP prompt arguments
- the `response` Twig template renders a valid JSON array of prompt messages

## Fields of `McpPrompt`

The `McpPrompt` entity contains the fields that define the MCP prompt:

- `name`: technical prompt name exposed by the MCP server. `McpPromptService` slugifies it with `_`
  separators before persistence.
- `label`: human-readable title exposed to MCP clients.
- `role`: minimum ElasticMS role required to see and get the prompt.
- `description`: concise description shown to MCP clients.
- `arguments`: Twig template returning a JSON array of prompt arguments.
- `response`: Twig template returning the prompt messages.
- `enabled`: enables or disables the prompt.

The `arguments` field is optional. Use it when the prompt needs values such as a topic, locale,
audience, document identifier, or editorial objective.

## Demo example: News editor

The demo project contains an exported custom MCP prompt definition in
[`demo/configs/admin/mcp-prompt/news_editor.json`](../../../../demo/configs/admin/mcp-prompt/news_editor.json).

```json
{
    "class": "EMS\\CoreBundle\\Entity\\McpPrompt",
    "arguments": [],
    "properties": {
        "name": "news_editor",
        "label": "News editor",
        "role": "ROLE_AUTHOR",
        "description": "Help authors draft an accessible demo news article using existing news and writing guidelines.",
        "arguments": "{{ include('@EMSCH/template_ems/mcp/prompts/news_editor/arguments.json.twig') }}",
        "response": "{{ include('@EMSCH/template_ems/mcp/prompts/news_editor/response.json.twig') }}",
        "enabled": true
    },
    "replaced": []
}
```

This prompt helps authors draft an accessible news article. It asks the MCP client to:

- inspect existing news with the `list_news` tool before drafting
- avoid duplicate content
- follow the custom MCP resources for accessibility, links, and FALC writing guidelines
- return an editorial proposal instead of saving content immediately

## Twig templates used by the demo prompt

The `news_editor` example uses two Twig templates:

- [`demo/skeleton/template_ems/mcp/prompts/news_editor/arguments.json.twig`](../../../../demo/skeleton/template_ems/mcp/prompts/news_editor/arguments.json.twig)
- [`demo/skeleton/template_ems/mcp/prompts/news_editor/response.json.twig`](../../../../demo/skeleton/template_ems/mcp/prompts/news_editor/response.json.twig)

### Arguments template

`arguments.json.twig` defines the prompt inputs exposed to MCP clients:

```json
[
    {
        "name": "topic",
        "description": "News topic to draft",
        "required": true
    },
    {
        "name": "locale",
        "description": "Target locale: fr, nl, en or de",
        "required": true
    },
    {
        "name": "audience",
        "description": "Target audience",
        "required": false
    }
]
```

Each argument supports:

- `name`: argument name passed to the response Twig template.
- `description`: short explanation shown to MCP clients.
- `required`: whether the client should require the argument.

### Response template

`response.json.twig` returns the prompt messages. The demo uses `json_encode|raw` so dynamic values
remain valid JSON:

```twig
{{- [
    {
        role: 'user',
        content: 'Draft a ' ~ locale ~ ' news article about "' ~ topic ~ '" for ' ~ audience|default('the demo website audience') ~ '. First inspect existing news with the list_news tool to avoid duplicates. Follow the MCP resources elasticms://guidelines/writing/accessibility, elasticms://resources/links and elasticms://guidelines/writing/falc. Return a proposed title, short summary, body structure, SEO title and SEO description. Do not save anything without confirmation.',
    },
]|json_encode|raw -}}
```

When a client calls `prompts/get` with:

```json
{
    "name": "news_editor",
    "arguments": {
        "topic": "ElasticMS content governance",
        "locale": "en",
        "audience": "web editors"
    }
}
```

the rendered prompt message asks the client to draft an English news article for web editors, using
the existing demo tools and resources.

## Getting a prompt through MCP

After initializing an MCP session, list available prompts:

```json
{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "prompts/list",
    "params": {}
}
```

The demo prompt appears with:

```json
{
    "name": "news_editor",
    "title": "News editor",
    "description": "Help authors draft an accessible demo news article using existing news and writing guidelines."
}
```

Get it with `prompts/get`:

```json
{
    "jsonrpc": "2.0",
    "id": 2,
    "method": "prompts/get",
    "params": {
        "name": "news_editor",
        "arguments": {
            "topic": "ElasticMS content governance",
            "locale": "en",
            "audience": "web editors"
        }
    }
}
```

The response contains the rendered messages in `result.messages`.

## Recommended workflow

1. Create a new entry in **MCP prompts**.
2. Set a stable `name`, readable `label`, concise `description`, and minimum `role`.
3. Define prompt `arguments` only when the prompt needs user-provided values.
4. Store the response body in a Twig file and include it from the `response` field.
5. Return valid JSON prompt messages from the response template.
6. Enable the prompt.
7. Test it through `prompts/list` and `prompts/get` with a user having the required role.
8. Export the entity JSON and commit it with the Twig templates.

## Practical advice

- Prefer `include(...)` over large inline Twig blocks in the admin form.
- Use `json_encode|raw` for response templates containing dynamic argument values.
- Keep descriptions short; put detailed workflow instructions in the response template.
- Reference existing MCP tools and resources by their stable names and URIs.
- Make prompts propose changes before saving content unless the workflow is intentionally automated.
- Restrict access with the smallest useful `role`.
- Version exported prompt definitions in Git next to their Twig templates.

See also [Custom MCP tools](./tools.md) for callable actions and
[Custom MCP resources](./resources.md) for reusable context that prompts can reference.
