import { InlineElement } from '../types'

interface ApiServiceOptions {
  onRenderResponse: (response: RenderResponse) => void
}

interface ApiRequest {
  method: 'GET' | 'POST' | 'DELETE'
  endpoint: string
  body?: object
}

export interface SuccessResponse {
  success: boolean
}

export interface EditResponse extends RenderResponse {
  draftId: string
}
export interface InitResponse extends RenderResponse {
  render: Record<string, string>
  elements: string[]
}
export interface RenderResponse {
  render: Record<string, string>
}

export class ApiService {
  constructor(private readonly options: ApiServiceOptions) {}

  public async init(elements: InlineElement[]): Promise<InitResponse> {
    return this.request<InitResponse>({
      method: 'POST',
      endpoint: '/inline-edit/api/init',
      body: { elements }
    })
  }

  public async edit(element: InlineElement): Promise<EditResponse> {
    return this.request({
      method: 'POST',
      endpoint: '/inline-edit/api/edit',
      body: { element }
    })
  }

  public async discard(draftId: string): Promise<SuccessResponse> {
    return this.request({
      method: 'DELETE',
      endpoint: `/inline-edit/api/discard/${draftId}`
    })
  }

  public async autoSave(draftId: string, element: InlineElement, content: string): Promise<SuccessResponse> {
    return this.request({
      method: 'POST',
      endpoint: `/inline-edit/api/auto-save/${draftId}`,
      body: { draftId, element, content }
    })
  }

  private async request<T>(request: ApiRequest): Promise<T> {
    const response = await fetch(`${request.endpoint}`, {
      method: request.method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(request.body)
    })

    if (!response.ok) {
      throw new Error(`API Error: ${response.statusText} (${response.status})`)
    }

    const data = await response.json()

    if (data && data.render) {
      this.options.onRenderResponse(data)
    }

    return data
  }
}
