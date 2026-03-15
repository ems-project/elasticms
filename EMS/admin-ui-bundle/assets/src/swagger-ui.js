import SwaggerUI from 'swagger-ui'
import 'swagger-ui/dist/swagger-ui.css'

document.addEventListener('DOMContentLoaded', () => {
  if (!Object.hasOwn(document.body.dataset, 'openapi')) return
  SwaggerUI({
    dom_id: '#swagger-ui',
    url: document.body.dataset.openapi
  })
})
