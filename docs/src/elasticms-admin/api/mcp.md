# Model Context Protocol

## Initialise a new session

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

Retrieve the session id from the `Mcp-Session-Id` header.

In the following example we'll consider that the session id is saved in a `SESSION_ID` environment variable:

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

## Call example

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

## Configure the MCP inspector

An MCP inspector is available in the monorepo. You can start it with the command: `make start/mcp`.
The MCP inspector will be available at [http://mcp-inspect.localhost/](http://mcp-inspect.localhost/).

Choose those options :

* Transport Type: `Streamable HTTP`
* URL: `http://host.docker.internal:8881/api/mcp`
* Connection Type: `Via Proxy`
* Custom Headers:
  * `Authorization`
  * `Bearer TOKEN`: Replace `TOKEN` by an authentication token
* Inspector Proxy Address: `http://mcp-inspect-proxy.localhost`
* Proxy Session Token: Paste the session token that you can see in the log's output: `make docker-logs/mcp`