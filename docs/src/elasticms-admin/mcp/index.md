# Model Context Protocol in ElasticMS Admin

ElasticMS Admin exposes a Model Context Protocol (MCP) server at `POST /api/mcp`.

The endpoint lets MCP clients discover and use ElasticMS capabilities with the permissions of the
authenticated API user. ElasticMS provides built-in tools and resources, and can also expose
project-specific MCP tools, prompts, and resources configured in the CMS.

## MCP concepts

ElasticMS uses three MCP concepts for custom CMS integrations:

| Concept  | Use it for                                                                                                                   | ElasticMS entity |
| -------- | ---------------------------------------------------------------------------------------------------------------------------- | ---------------- |
| Tool     | An action the model can call with arguments, such as searching content or creating a draft.                                  | `McpTool`        |
| Prompt   | A reusable instruction or workflow the model can load with arguments, such as drafting a localized news article.             | `McpPrompt`      |
| Resource | Reference material the model can read, such as guidelines, documentation, prompt instructions, or reusable Markdown content. | `McpResource`    |

Use a **tool** when the model must execute logic with input parameters. Use a **prompt** when the
model needs a reusable task instruction. Use a **resource** when the model needs contextual
information it can read by URI.

## Admin configuration

The admin sidebar contains an **MCP** section with:

- **MCP tools** for custom callable tools.
- **MCP prompts** for custom reusable prompt workflows.
- **MCP resources** for custom readable resources.

Both are regular ElasticMS admin entities. They can be exported to JSON, versioned in Git, and
imported across environments.

## Recommended project structure

Keep exported CMS definitions under `configs/admin`, and keep the rendered Twig templates in the
project skeleton:

```text
demo/configs/admin/mcp-tool/
demo/configs/admin/mcp-prompt/
demo/configs/admin/mcp-resource/
demo/skeleton/template_ems/mcp/tools/
demo/skeleton/template_ems/mcp/prompts/
demo/skeleton/template_ems/mcp/resources/
```

This keeps the CMS entity small while the actual response logic remains readable and testable in
Twig files.

## Pages

- [Custom MCP tools](./tools.md)
- [Custom MCP prompts](./prompts.md)
- [Custom MCP resources](./resources.md)

For low-level endpoint usage and curl examples, see the [MCP API](../api/mcp.md).
