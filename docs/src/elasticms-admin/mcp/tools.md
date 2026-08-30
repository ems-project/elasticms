# Custom MCP tools

ElasticMS can expose custom MCP tools directly from the admin interface. These tools are managed in
the **MCP Tools** admin screen and are stored as
[`EMS\CoreBundle\Entity\McpTool`](../../../../EMS/core-bundle/src/Entity/McpTool.php) entities
edited with the
[`EMS\CoreBundle\Form\Form\McpToolType`](../../../../EMS/core-bundle/src/Form/Form/McpToolType.php)
form.

This makes it possible to add business-specific MCP tools without changing PHP code, as long as the
tool can be described with Twig-rendered JSON schemas and a Twig-rendered JSON response.

## Where to configure custom tools

In the ElasticMS admin, open the **MCP Tools** menu.

Each entry is handled by
[`McpToolController`](../../../../EMS/core-bundle/src/Controller/Admin/McpToolController.php) and
persisted through
[`McpToolService`](../../../../EMS/core-bundle/src/Service/Mcp/McpToolService.php).

A custom tool is available to the MCP server when:

- the `enabled` flag is set to `true`
- the current MCP user has the configured `role`
- the tool definition contains valid Twig-rendered JSON

## Fields of `McpTool`

The `McpTool` entity contains the fields that define the MCP tool:

- `name`: technical tool name exposed by the MCP server. `McpToolService` slugifies it with `_`
  separators before persistence.
- `label`: admin label.
- `role`: minimum ElasticMS role required to see and call the tool.
- `description`: MCP tool description shown to clients.
- `inputSchema`: Twig template returning the JSON Schema for the tool input.
- `outputSchema`: Twig template returning the JSON Schema for the tool output.
- `response`: Twig template returning the actual JSON payload of the tool response.
- `enabled`: enables or disables the tool.

In the admin UI, `McpToolType` exposes these fields through a standard form and uses
`CodeEditorType` with Twig syntax highlighting for `input_schema`, `output_schema`, and `response`.

## Demo example: `list_news`

The demo project contains an exported custom MCP tool definition in
[`demo/configs/admin/mcp-tool/list_news.json`](../../../../demo/configs/admin/mcp-tool/list_news.json).

```json
{
    "class": "EMS\\CoreBundle\\Entity\\McpTool",
    "arguments": [],
    "properties": {
        "name": "list_news",
        "label": "list_news",
        "role": "ROLE_AUTHOR",
        "description": "List news, with 10 items per page.",
        "inputSchema": "{{ include('@EMSCH/template_ems/mcp/tools/list_news/input_schema.json.twig') }}",
        "outputSchema": "{{ include('@EMSCH/template_ems/mcp/tools/list_news/output_schema.json.twig') }}",
        "response": "{{ include('@EMSCH/template_ems/mcp/tools/list_news/response.json.twig') }}",
        "enabled": true
    },
    "replaced": []
}
```

This example shows the intended pattern:

- keep the `McpTool` entity small and readable
- store the actual schemas and response logic in Twig templates
- reuse project Twig helpers and business logic from the skeleton

## Twig templates used by the demo tool

The `list_news` example uses three Twig templates:

- [`demo/skeleton/template_ems/mcp/tools/list_news/input_schema.json.twig`](../../../../demo/skeleton/template_ems/mcp/tools/list_news/input_schema.json.twig)
- [`demo/skeleton/template_ems/mcp/tools/list_news/output_schema.json.twig`](../../../../demo/skeleton/template_ems/mcp/tools/list_news/output_schema.json.twig)
- [`demo/skeleton/template_ems/mcp/tools/list_news/response.json.twig`](../../../../demo/skeleton/template_ems/mcp/tools/list_news/response.json.twig)

### Input schema

`input_schema.json.twig` defines a JSON Schema with two arguments:

- `page`: zero-based page index
- `environment`: `live` or `preview`

This schema is rendered as the MCP `inputSchema` of the tool.

### Output schema

`output_schema.json.twig` defines a JSON Schema returning:

- `news`: array of news items
- `total`: total number of matching documents

Each news item exposes `ouuid`, `publication_date`, `title_fr`, and `title_nl`.

### Response template

`response.json.twig` contains the actual tool logic:

- it executes `emsco_search(...)`
- it limits results to 10 items per page
- it reads only the `news` content type
- it maps each document to a compact MCP response payload
- it returns JSON with `json_encode|raw`

The demo response template is:

```twig
{%- set searchResult = emsco_search(environment|default('live')|emsco_get_environment.alias, contentTypes: ['news'], size: 10, from: (page * 10), sort: {'publication_date': 'DESC'}) -%}
{{- {
    news: searchResult.getDocuments()|map(p => {
        ouuid: p.id,
        publication_date: [p.getData()['publication_date'], 'T12:00:00+02:00']|join|date('Y-m-d', 'UTC'),
        title_fr: p.getData()['fr']['title'],
        title_nl: p.getData()['nl']['title'],
    }),
    total: searchResult.getTotalHits(),
}|json_encode|raw -}}
```

## Recommended workflow

1. Create a new entry in **MCP Tools**.
2. Set a stable `name`, a readable `label`, and the minimum `role`.
3. Write or include a Twig template for `inputSchema`.
4. Write or include a Twig template for `outputSchema`.
5. Write the `response` Twig template so it returns valid JSON.
6. Enable the tool.
7. Test it through the MCP endpoint with a user having the required role.

## Practical advice

- Prefer `include(...)` over large inline Twig blocks in the admin form.
- Keep schemas strict with `additionalProperties: false` when possible.
- Keep the response shape aligned with `outputSchema`.
- Restrict access with the smallest useful `role`.
- Use exported JSON files, like `demo/configs/admin/mcp-tool/list_news.json`, to version custom
  tools in Git.

## Import and versioning

Because `McpTool` is a regular admin entity with JSON import/export support, you can:

- define tools in a project config export
- import them in another environment
- review tool changes in Git
- keep Twig templates in the skeleton next to the rest of the project logic

This is the recommended way to maintain custom MCP tools across environments.
