import '../../../../../css/core/components/tiptap/_search_input.scss'

export type SearchInputItem<T = unknown> = { id: string; text: string; data?: T }
export type SearchInputValue<T = unknown> = { id: string; label: string; data: T | null } | null

export type SearchInputConfig<T = unknown> = {
    searchUrl: string
    searchLabel: string
    searchPlaceholder: string
    noResultsLabel: string
    initialId?: string
    extraParams?: Record<string, string>
    onChange: (value: SearchInputValue<T>) => void
}

export type SearchInput = {
    element: HTMLElement
    setExtraParams: (params: Record<string, string>) => void
    clear: () => void
}

export function createSearchInput<T = unknown>(config: SearchInputConfig<T>): SearchInput {
    const container = document.createElement('div')
    container.className = 'search-input'

    const label = document.createElement('label')
    label.textContent = config.searchLabel
    container.appendChild(label)

    const inputWrapper = document.createElement('div')
    inputWrapper.className = 'search-wrapper'

    const input = document.createElement('input')
    input.type = 'text'
    input.autocomplete = 'off'
    input.placeholder = config.searchPlaceholder

    const display = document.createElement('div')
    display.className = 'search-display'

    const clearBtn = document.createElement('button')
    clearBtn.type = 'button'
    clearBtn.textContent = '×'
    clearBtn.className = 'search-clear'
    clearBtn.style.display = 'none'

    inputWrapper.appendChild(input)
    inputWrapper.appendChild(display)
    inputWrapper.appendChild(clearBtn)
    container.appendChild(inputWrapper)

    const results = document.createElement('div')
    results.className = 'search-results'
    container.appendChild(results)

    let currentQuery = '*'
    let currentPage = 1
    let extraParams = config.extraParams ?? {}
    let hasMore = false
    let loading = false
    let hasSelection = false
    let currentSelection: { label: string; html: string } | null = null

    const selectItem = (id: string, label: string, html: string, data: T | null) => {
        hasSelection = true
        currentSelection = { label, html }
        input.value = label
        input.style.display = 'none'
        display.innerHTML = html
        display.style.display = 'flex'
        clearBtn.style.display = 'block'
        results.style.display = 'none'
        config.onChange({ id, label, data })
    }

    const clearSelection = () => {
        hasSelection = false
        currentSelection = null
        input.value = ''
        input.style.display = 'block'
        display.innerHTML = ''
        display.style.display = 'none'
        clearBtn.style.display = 'none'
        config.onChange(null)
        input.focus()
        currentQuery = '*'
        currentPage = 1
        hasMore = false
        void search('*', 1)
        results.style.display = 'block'
    }

    clearBtn.addEventListener('click', clearSelection)

    const appendItems = (items: SearchInputItem<T>[]) => {
        items.forEach((item) => {
            const el = document.createElement('div')
            el.className = 'search-item'
            el.tabIndex = 0
            el.innerHTML = item.text
            const pick = () => selectItem(item.id, el.innerText, item.text, item.data ?? null)
            el.addEventListener('click', pick)
            el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault()
                    pick()
                }
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
        Object.entries(extraParams).forEach(([k, v]) => url.searchParams.set(k, v))
        try {
            const res = await fetch(url.toString(), { headers: { Accept: 'application/json' } })
            const data = await res.json()
            const items: SearchInputItem<T>[] = data.items ?? []
            hasMore = data.incomplete_results === true
            if (page === 1) {
                results.innerHTML = ''
                if (!items.length) {
                    const empty = document.createElement('div')
                    empty.className = 'search-empty'
                    empty.textContent = config.noResultsLabel
                    results.appendChild(empty)
                    return
                }
            }
            appendItems(items)
        } catch {
            results.innerHTML = ''
        } finally {
            loading = false
        }
    }

    const fetchInitial = async (id: string) => {
        const url = new URL(config.searchUrl, location.href)
        url.searchParams.set('dataLink', id)
        try {
            const res = await fetch(url.toString(), { headers: { Accept: 'application/json' } })
            const data = await res.json()
            const item: (SearchInputItem<T> & { title?: string }) | undefined = data.items?.[0]
            if (item) selectItem(item.id, item.title ?? item.text, item.text, item.data ?? null)
        } catch {
            console.error('Failed to fetch initial search-input item')
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

    input.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault()
            const first = results.querySelector<HTMLElement>('[tabindex="0"]')
            first?.focus()
        }
    })

    display.addEventListener('click', () => {
        input.style.display = 'block'
        input.focus()
    })

    input.addEventListener('focus', () => {
        display.style.display = 'none'
        clearBtn.style.display = 'none'
        input.value = ''
        currentQuery = '*'
        currentPage = 1
        hasMore = false
        void search('*', 1)
        results.style.display = 'block'
    })

    input.addEventListener('blur', () => {
        setTimeout(() => {
            results.style.display = 'none'
            if (hasSelection && currentSelection) {
                input.value = currentSelection.label
                input.style.display = 'none'
                display.innerHTML = currentSelection.html
                display.style.display = 'flex'
                clearBtn.style.display = 'block'
            }
        }, 150)
    })

    input.addEventListener('input', () => {
        clearTimeout(timer)
        results.style.display = 'block'
        const q = input.value.trim()
        const query = q.length < 1 ? '*' : q
        timer = setTimeout(() => {
            currentQuery = query
            currentPage = 1
            hasMore = false
            void search(query, 1)
        }, 300)
    })

    if (config.initialId) void fetchInitial(config.initialId)

    return {
        element: container,
        setExtraParams: (params) => {
            extraParams = params
            currentPage = 1
            hasMore = false
            void search(currentQuery, 1)
        },
        clear: clearSelection
    }
}