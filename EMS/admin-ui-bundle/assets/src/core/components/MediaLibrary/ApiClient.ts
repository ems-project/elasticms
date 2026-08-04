export default class ApiClient {
    readonly #pathPrefix: string
    readonly #onRequest: () => void

    constructor(pathPrefix: string, onRequest: () => void) {
        this.#pathPrefix = pathPrefix
        this.#onRequest = onRequest
    }

    get pathPrefix() {
        return this.#pathPrefix
    }

    async get(path: string) {
        this.#onRequest()
        const response = await fetch(`${this.#pathPrefix}${path}`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        return response.json()
    }

    async post(path: string, data: unknown = {}, isFormData = false) {
        this.#onRequest()
        let options: RequestInit

        if (isFormData) {
            options = { method: 'POST', body: data as BodyInit }
        } else {
            options = {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            }
        }

        const response = await fetch(`${this.#pathPrefix}${path}`, options)

        return response.ok ? response.json() : Promise.reject(response)
    }
}