import {InlineElement} from "../types";

interface ApiServiceOptions {
    onRenderResponse: (response: RenderResponse) => void
}

interface ApiRequest {
    method: 'GET' | 'POST',
    endpoint: string,
    body?: object
}

export interface DraftResponse extends RenderResponse {
    draftId: string;
}
export interface InitResponse extends RenderResponse {
    render: Record<string, string>;
    elements: string[];
}
export interface RenderResponse {
    render: Record<string, string>;
}

export class ApiService {
    constructor(private readonly options: ApiServiceOptions)
    {
    }

    public async init(elements: InlineElement[]): Promise<InitResponse> {
        return this.request<InitResponse>({
            method: 'POST',
            endpoint: '/inline-edit/api/init',
            body: { elements}
        })
    }

    public async draft(element: InlineElement): Promise<DraftResponse>
    {
        return this.request({
            method: 'POST',
            endpoint: '/draft',
            body: { element }
        });
    }

    private async request<T>(request: ApiRequest): Promise<T> {
        const response = await fetch(`${request.endpoint}`, {
            method: request.method,
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(request.body)
        });

        if (!response.ok) {
            throw new Error(`API Error: ${response.statusText} (${response.status})`);
        }

        const data = await response.json();

        if (data && data.render) {
            this.options.onRenderResponse(data);
        }

        return data;
    }
}