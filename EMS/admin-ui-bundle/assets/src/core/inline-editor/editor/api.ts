import { EmsId, InlineCollection, InlineElement } from '../types'

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
    data: Record<string, string>
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

    public async init(collection: InlineCollection): Promise<InitResponse> {
        return this.request<InitResponse>({
            method: 'POST',
            endpoint: '/inline-edit/api/init',
            body: { collection }
        })
    }

    public async edit(emsId: EmsId, elements: InlineElement[]): Promise<EditResponse> {
        return this.request({
            method: 'POST',
            endpoint: '/inline-edit/api/edit',
            body: { emsId, elements }
        })
    }

    public async publish(collection: InlineCollection): Promise<SuccessResponse> {
        return this.request({
            method: 'POST',
            endpoint: `/inline-edit/api/publish`,
            body: { collection }
        })
    }

    public async discard(collection: InlineCollection): Promise<SuccessResponse> {
        return this.request({
            method: 'DELETE',
            endpoint: `/inline-edit/api/discard`,
            body: { collection }
        })
    }

    public async autoSave(
        element: InlineElement,
        content: string
    ): Promise<SuccessResponse> {
        return this.request({
            method: 'POST',
            endpoint: `/inline-edit/api/auto-save`,
            body: { element, content }
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
