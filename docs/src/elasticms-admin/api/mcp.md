# Model Context Protocol

The MCP endpoint exposes a minimal HTTP server for ElasticMS Admin at `POST /api/mcp`.

Every request must be authenticated with an API token. Prefer the standard
`Authorization: Bearer <token>` header. The historical `X-Auth-Token` header is still supported for
backward compatibility.

In the examples below, the token is available in the `AUTH_TOKEN` environment variable:

```shell
export AUTH_TOKEN='nlpUnMR/W8bgSSclYXI2G0dP5REdp5yhvaXfMDV/he+XgQgI7pIRqkuNqsJRJzoYvYM='
```

The current MVP exposes these MCP operations:

- `tools/list`
- `tools/call`

And these tools:

- `get_current_user`
- `get_document_<contentType>`
- `create_document_<contentType>`

> Note: ElasticMS can also expose project-specific custom MCP tools. See
> [Custom MCP tools](../mcp/tools.md).

Non-`initialize` requests require a valid MCP session id in the `Mcp-Session-Id` header.

## Initialize a new session

```shell
curl -i \
    -X POST \
    http://localhost:8881/api/mcp \
    -H "Authorization: Bearer ${AUTH_TOKEN}" \
    -H "Content-Type: application/json" \
    -d '{
      "jsonrpc":"2.0",
      "id":1,
      "method":"initialize",
      "params":{
        "protocolVersion":"2025-03-26",
        "capabilities":{},
        "clientInfo":{
          "name":"curl",
          "version":"1.0"
        }
      }
    }' -w '\n'
```

The response body confirms that the server supports the MCP `tools` capability. The list of
available tools is retrieved separately with `tools/list`.

Retrieve the session id from the `Mcp-Session-Id` response header.

In the following examples, the session id is saved in a `SESSION_ID` environment variable:

`export SESSION_ID=8885b2e5-89a6-4716-8a34-a85fc4abd38f`

## List available tools

```shell
curl \
    -X POST \
    http://localhost:8881/api/mcp \
    -H "Authorization: Bearer ${AUTH_TOKEN}" \
    -H "Mcp-Session-Id: ${SESSION_ID}" \
    -H "Content-Type: application/json" \
    -d '{
      "jsonrpc":"2.0",
      "id":2,
      "method":"tools/list",
      "params":{}
    }' -w '\n'
```

Expected tools:

- `get_current_user`
- one `get_<contentType>` tool for each content type that the authenticated user is allowed to view
- one `create_document_<contentType>` tool for each content type that the authenticated user is
  allowed to create

## Call `get_current_user`

```shell
curl \
    -X POST \
    http://localhost:8881/api/mcp \
    -H "Authorization: Bearer ${AUTH_TOKEN}" \
    -H "Mcp-Session-Id: ${SESSION_ID}" \
    -H "Content-Type: application/json" \
    -d '{
      "jsonrpc":"2.0",
      "id":3,
      "method":"tools/call",
      "params":{
        "name":"get_current_user",
        "arguments":{}
      }
    }'
```

## Call `get_news`

Use `get_news` to read one document in the `news` content type by `ouuid`, with the permissions of
the authenticated user.

Each readable content type exposes its own `get_<contentType>` tool.

```shell
curl \
    -X POST \
    http://localhost:8881/api/mcp \
    -H "Authorization: Bearer ${AUTH_TOKEN}" \
    -H "Mcp-Session-Id: ${SESSION_ID}" \
    -H "Content-Type: application/json" \
    -d '{
      "jsonrpc":"2.0",
      "id":4,
      "method":"tools/call",
      "params":{
        "name":"get_news",
        "arguments":{
          "ouuid":"97591e4d-c71a-48ae-8504-67d09df595c2"
        }
      }
    }' -w '\n'
```

## Call `save_news`

Use `save_news` to create a draft in the `news` content type. The request is allowed only if the
authenticated user has the same creation rights as in the Admin API.

The `rawData` schema is generated recursively from the target ElasticMS content type, so different
content types can expose different payload structures for nested objects, collections, and scalar
fields.

```shell
curl \
    -X POST \
    http://localhost:8881/api/mcp \
    -H "Authorization: Bearer ${AUTH_TOKEN}" \
    -H "Mcp-Session-Id: ${SESSION_ID}" \
    -H "Content-Type: application/json" \
    -d '{
      "jsonrpc":"2.0",
      "id":5,
      "method":"tools/call",
      "params":{
        "name":"save_news",
        "arguments":{
          "rawData":{
            "title":"MCP News Draft"
          }
        }
      }
    }' -w '\n'
```

## Configure the MCP inspector

Before starting the MCP inspector, create a `.mcp.json` file at the root of the monorepo. The
inspector setup expects this file to define an `mcpServers` object with an `elasticms` server using
the Streamable HTTP transport, the ElasticMS Admin MCP endpoint, and the authentication headers.

Example:

```json
{
    "mcpServers": {
        "elasticms": {
            "type": "streamable-http",
            "url": "http://host.docker.internal:8881/api/mcp",
            "headers": {
                "Authorization": "Bearer <your_api_token>"
            }
        }
    }
}
```

You can adapt the `url` or add query parameters if needed for local debugging, but the file must
exist before running `make start/mcp`.

An MCP inspector is available in the monorepo. Start it with `make start/mcp`. The MCP inspector
will then be available at [http://mcp-inspect.localhost/](http://mcp-inspect.localhost/).

Choose these options:

- Transport Type: `Streamable HTTP`
- URL: `http://host.docker.internal:8881/api/mcp`
- Connection Type: `Via Proxy`
- Custom Headers: `Authorization: Bearer TOKEN`
- Inspector Proxy Address: `http://mcp-inspect-proxy.localhost`
- Proxy Session Token: paste the session token visible in the logs with `make docker-logs/mcp`
