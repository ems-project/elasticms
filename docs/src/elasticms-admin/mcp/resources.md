# Custom MCP resources

ElasticMS can expose custom MCP resources directly from the admin interface. These resources are
managed in the **MCP Resources** admin screen and are stored as `EMS\CoreBundle\Entity\McpResource`
entities.

Resources are useful for project-specific context that should be available to an MCP client without
requiring a tool call with arguments. Examples include editorial guidelines, writing rules, content
policies, style guides, reusable instructions, or domain documentation.

## Where to configure custom resources

In the ElasticMS admin, open **MCP → MCP resources**.

A custom resource is available to the MCP server when:

- the `enabled` flag is set to `true`
- the current MCP user has the configured `role`
- the resource has a stable `uri`
- the `response` Twig template renders valid content for the configured `mimeType`

## Fields of `McpResource`

The `McpResource` entity contains the fields that define the MCP resource:

- `name`: technical resource name exposed by the MCP server. `McpResourceService` slugifies it with
  `_` separators before persistence.
- `label`: human-readable title exposed to MCP clients.
- `uri`: stable MCP resource URI, for example `elasticms://guidelines/writing/falc`.
- `role`: minimum ElasticMS role required to see and read the resource.
- `description`: concise description shown to MCP clients.
- `mimeType`: MIME type of the rendered resource content, for example `text/markdown`,
  `application/json`, or `text/plain`.
- `response`: Twig template rendering the resource content.
- `enabled`: enables or disables the resource.

Use a specific `mimeType` whenever possible. For Markdown instructions, prefer `text/markdown`.

## Demo example: Easy-to-read writing guidelines

The demo project contains an exported custom MCP resource definition in
[`demo/configs/admin/mcp-resource/easy_to_read_writing_guidelines.json`](../../../../demo/configs/admin/mcp-resource/easy_to_read_writing_guidelines.json).

```json
{
    "class": "EMS\\CoreBundle\\Entity\\McpResource",
    "arguments": [],
    "properties": {
        "name": "easy_to_read_writing_guidelines",
        "label": "Easy-to-read writing guidelines (FALC)",
        "uri": "elasticms://guidelines/writing/falc",
        "role": "ROLE_AUTHOR",
        "description": "Instructions for writing or rewriting CMS content according to Easy-to-Read and Easy-to-Understand (FALC) principles. Use this resource when creating or adapting content for people with intellectual disabilities or reading difficulties.",
        "mimeType": "text/markdown",
        "response": "{{ include('@EMSCH/template_ems/mcp/resources/skills/falc.md.twig') }}",
        "enabled": true
    },
    "replaced": []
}
```

The resource exposes Markdown guidelines from this Twig template:

[`demo/skeleton/template_ems/mcp/resources/skills/falc.md.twig`](../../../../demo/skeleton/template_ems/mcp/resources/skills/falc.md.twig)

## Resource content as Markdown

Unlike MCP descriptions, which should remain short plain text, resource contents can use a dedicated
MIME type. The FALC example uses `text/markdown`, which is appropriate for long-form instructions.

The rendered Markdown starts with front matter:

```markdown
---
name: write-easy-to-read-content
description:
    Write or rewrite content in an easy-to-read and easy-to-understand form (FALC), especially for
    people with intellectual disabilities or reading difficulties. Use when a user requests FALC,
    Easy Read, or content designed for readers who need strong cognitive accessibility.
---
```

Then it provides structured guidance for:

- identifying the reader and purpose
- simplifying words, sentences, dates, and actions
- preserving facts and legal meaning during rewrites
- checking quality before returning the final content
- recommending review by subject-matter experts and target readers

This pattern is useful when a resource behaves like a reusable skill or writing guide for the MCP
client.

## Reading a resource through MCP

After initializing an MCP session, list available resources:

```json
{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "resources/list",
    "params": {}
}
```

The FALC resource appears with:

```json
{
    "name": "easy_to_read_writing_guidelines",
    "title": "Easy-to-read writing guidelines (FALC)",
    "uri": "elasticms://guidelines/writing/falc",
    "mimeType": "text/markdown"
}
```

Read it with `resources/read`:

```json
{
    "jsonrpc": "2.0",
    "id": 2,
    "method": "resources/read",
    "params": {
        "uri": "elasticms://guidelines/writing/falc"
    }
}
```

The response contains the rendered Markdown in `result.contents[0].text`.

## Recommended workflow

1. Create a new entry in **MCP Resources**.
2. Set a stable `name`, `label`, `uri`, and minimum `role`.
3. Choose the correct `mimeType`.
4. Store the long response body in a Twig file and include it from the `response` field.
5. Enable the resource.
6. Test it through `resources/list` and `resources/read` with a user having the required role.
7. Export the entity JSON and commit it with the Twig template.

## Practical advice

- Prefer `include(...)` over large inline Twig blocks in the admin form.
- Use stable URIs and avoid renaming them once clients depend on them.
- Keep descriptions short and readable; put long instructions in the resource content.
- Use `text/markdown` for guidelines, documentation, and reusable instructions.
- Use `application/json` only when the resource content is structured data.
- Restrict access with the smallest useful `role`.
- Version exported resource definitions in Git next to their Twig templates.

See also [Custom MCP prompts](./prompts.md) for reusable workflows that can reference resources by
URI.
