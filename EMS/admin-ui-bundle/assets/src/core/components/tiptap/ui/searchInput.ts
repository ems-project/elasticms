export type SearchInputConfig = {
    searchUrl: string
    filters?: [string, string][]
    filterLabel: string
    searchLabel: string
    noResultsLabel: string
    initialId?: string
    initialLabel?: string
}

export type SearchInput = {
    element: HTMLElement
    getValue(): string | null
    getLabel(): string
}

export function createSearchInput(config: SearchInputConfig): SearchInput {
    const container = document.createElement('div')
    container.style.cssText = 'display: flex; flex-direction: column; gap: 10px;'

    if (config.filters && config.filters.length > 1) {
        const filterWrapper = document.createElement('div')
        const filterLabel = document.createElement('label')
        filterLabel.textContent = config.filterLabel
        const filterSelect = document.createElement('select')
        filterSelect.id = 'search-input-type'
        config.filters.forEach(([label, value]) => {
            const opt = document.createElement('option')
            opt.value = value
            opt.textContent = label
            filterSelect.appendChild(opt)
        })
        filterWrapper.appendChild(filterLabel)
        filterWrapper.appendChild(filterSelect)
        container.appendChild(filterWrapper)
    }

    const searchWrapper = document.createElement('div')
    const searchLabel = document.createElement('label')
    searchLabel.textContent = config.searchLabel
    const searchInput = document.createElement('input')
    searchInput.type = 'text'
    searchInput.autocomplete = 'off'
    searchInput.value = config.initialLabel ?? ''
    searchWrapper.appendChild(searchLabel)
    searchWrapper.appendChild(searchInput)
    container.appendChild(searchWrapper)

    const results = document.createElement('div')
    results.style.cssText =
        'max-height: 200px; overflow-y: auto; border: 1px solid #ccc; display: none;'
    container.appendChild(results)

    const hiddenId = document.createElement('input')
    hiddenId.type = 'hidden'
    hiddenId.value = config.initialId ?? ''
    container.appendChild(hiddenId)

    const hiddenLabel = document.createElement('input')
    hiddenLabel.type = 'hidden'
    hiddenLabel.value = config.initialLabel ?? ''
    container.appendChild(hiddenLabel)

    const typeSelect = container.querySelector<HTMLSelectElement>('#search-input-type')

    let currentQuery = ''
    let currentPage = 1
    let hasMore = false
    let loading = false

    const appendItems = (items: { id: string; text: string }[]) => {
        items.forEach((item) => {
            const el = document.createElement('div')
            el.style.cssText = 'padding: 8px; cursor: pointer;'
            el.textContent = item.text
            el.addEventListener('mouseenter', () => (el.style.background = '#f0f0f0'))
            el.addEventListener('mouseleave', () => (el.style.background = ''))
            el.addEventListener('click', () => {
                hiddenId.value = item.id
                hiddenLabel.value = item.text
                searchInput.value = item.text
                results.style.display = 'none'
            })
            results.appendChild(el)
        })
    }

    const search = async (q: string, page: number) => {
        if (loading) return
        loading = true
        const url = new URL(config.searchUrl, location.href)
        url.searchParams.set('q', q)
        url.searchParams.set('page', String(page))
        if (typeSelect?.value) url.searchParams.set('type', typeSelect.value)
        try {
            const res = await fetch(url.toString(), { headers: { Accept: 'application/json' } })
            const data = await res.json()
            const items: { id: string; text: string }[] = data.items ?? []
            hasMore = data.incomplete_results === true
            if (page === 1) {
                results.innerHTML = ''
                if (!items.length) {
                    const empty = document.createElement('div')
                    empty.style.cssText = 'padding: 8px; color: #888;'
                    empty.textContent = config.noResultsLabel
                    results.appendChild(empty)
                    results.style.display = 'block'
                    return
                }
            }
            appendItems(items)
            results.style.display = 'block'
        } catch {
            results.style.display = 'none'
        } finally {
            loading = false
        }
    }

    results.addEventListener('scroll', () => {
        if (!hasMore || loading) return
        if (results.scrollTop + results.clientHeight >= results.scrollHeight - 20) {
            currentPage++
            void search(currentQuery, currentPage)
        }
    })

    let timer: ReturnType<typeof setTimeout>

    searchInput.addEventListener('input', () => {
        clearTimeout(timer)
        hiddenId.value = ''
        hiddenLabel.value = ''
        const q = searchInput.value.trim()
        if (q.length < 1) {
            results.style.display = 'none'
            return
        }
        timer = setTimeout(() => {
            currentQuery = q
            currentPage = 1
            hasMore = false
            void search(q, 1)
        }, 300)
    })

    if (typeSelect) {
        typeSelect.addEventListener('change', () => {
            if (currentQuery) {
                currentPage = 1
                hasMore = false
                void search(currentQuery, 1)
            }
        })
    }

    return {
        element: container,
        getValue: () => hiddenId.value.trim() || null,
        getLabel: () => hiddenLabel.value
    }
}
