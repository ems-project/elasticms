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
- one `get_document_<contentType>` tool for each content type that the authenticated user is
  allowed to view
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

## Call `get_document_news`

Use `get_document_news` to read one document in the `news` content type by `ouuid`, with the
permissions of the authenticated user.

Each readable content type exposes its own `get_document_<contentType>` tool.

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
        "name":"get_document_news",
        "arguments":{
          "ouuid":"97591e4d-c71a-48ae-8504-67d09df595c2"
        }
      }
    }' -w '\n'
```

## Call `create_document_news`

Use `create_document_news` to create a draft in the `news` content type. The request is allowed
only if the authenticated user has the same creation rights as in the Admin API.

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
        "name":"create_document_news",
        "arguments":{
          "rawData":{
            "title":"MCP News Draft"
          }
        }
      }
    }' -w '\n'
```

## Configure the MCP inspector

An MCP inspector is available in the monorepo. You can start it with the command: `make start/mcp`.
The MCP inspector will be available at
[http://mcp-inspect.localhost/](http://mcp-inspect.localhost/).

Choose these options:

- Transport Type: `Streamable HTTP`
- URL: `http://host.docker.internal:8881/api/mcp`
- Connection Type: `Via Proxy`
- Custom Headers: `Authorization: Bearer TOKEN`
- Inspector Proxy Address: `http://mcp-inspect-proxy.localhost`
- Proxy Session Token: paste the session token visible in the logs with `make docker-logs/mcp`
