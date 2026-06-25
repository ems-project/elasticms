import '../../../../../css/core/components/tiptap/_search_input.scss'

export type SearchInputConfig = {
    searchUrl: string
    filters?: [string, string][]
    filterLabel: string
    selectedLabel: string
    searchLabel: string
    searchPlaceholder: string
    noSelectionLabel: string
    noResultsLabel: string
    initialId?: string
}

export type SearchInput = {
    element: HTMLElement
    getValue(): string | null
    getLabel(): string
}

export function createSearchInput(config: SearchInputConfig): SearchInput {
    const container = document.createElement('div')
    container.className = 'search-input'

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
    searchInput.placeholder = config.searchPlaceholder
    searchWrapper.appendChild(searchLabel)
    searchWrapper.appendChild(searchInput)

    const results = document.createElement('div')
    results.className = 'search-input__results'

    searchWrapper.appendChild(results)
    container.appendChild(searchWrapper)

    const selectedWrapper = document.createElement('div')
    const selectedLabel = document.createElement('label')
    selectedLabel.textContent = config.selectedLabel
    const selected = document.createElement('div')
    selected.className = 'search-input__selected'
    const selectedContent = document.createElement('span')
    const clearBtn = document.createElement('button')
    clearBtn.type = 'button'
    clearBtn.textContent = '×'
    clearBtn.className = 'search-input__clear'
    selected.appendChild(selectedContent)
    selected.appendChild(clearBtn)
    selectedWrapper.appendChild(selectedLabel)
    selectedWrapper.appendChild(selected)
    container.appendChild(selectedWrapper)

    const hiddenId = document.createElement('input')
    hiddenId.type = 'hidden'
    hiddenId.value = config.initialId ?? ''
    container.appendChild(hiddenId)

    const hiddenLabel = document.createElement('input')
    hiddenLabel.type = 'hidden'
    container.appendChild(hiddenLabel)

    const typeSelect = container.querySelector<HTMLSelectElement>('#search-input-type')

    let currentQuery = '*'
    let currentPage = 1
    let hasMore = false
    let loading = false

    const setNoSelection = () => {
        selectedContent.innerHTML = ''
        selectedContent.textContent = config.noSelectionLabel
        selectedContent.classList.add('search-input__selected--placeholder')
        clearBtn.style.display = 'none'
    }

    const setSelected = (html: string, label: string) => {
        hiddenLabel.value = label
        selectedContent.innerHTML = html
        selectedContent.classList.remove('search-input__selected--placeholder')
        clearBtn.style.display = 'block'
    }

    const clearSelection = () => {
        hiddenId.value = ''
        hiddenLabel.value = ''
        setNoSelection()
        void search('*', 1)
    }

    clearBtn.addEventListener('click', clearSelection)

    const appendItems = (items: { id: string; text: string }[]) => {
        items.forEach((item) => {
            const el = document.createElement('div')
            el.className = 'search-input__results--item'
            el.tabIndex = 0
            el.innerHTML = item.text
            const select = () => {
                hiddenId.value = item.id
                searchInput.value = ''
                results.style.display = 'none'
                setSelected(item.text, el.innerText)
            }
            el.addEventListener('click', select)
            el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault()
                    select()
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
                    empty.className = 'search-input__results--empty'
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
            const item = data.items?.[0]
            if (item) {
                setSelected(item.text, item.title ?? item.text)
            } else {
                setNoSelection()
            }
        } catch {
            /* empty */
        }
        void search('*', 1)
    }

    results.addEventListener('scroll', () => {
        if (!hasMore || loading) return
        if (results.scrollTop + results.clientHeight >= results.scrollHeight - 20) {
            currentPage++
            void search(currentQuery, currentPage)
        }
    })

    let timer: ReturnType<typeof setTimeout>

    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault()
            const first = results.querySelector<HTMLElement>('[tabindex="0"]')
            first?.focus()
        }
    })

    searchInput.addEventListener('focus', () => {
        if (!searchInput.value.trim()) {
            currentQuery = '*'
            currentPage = 1
            hasMore = false
            void search('*', 1)
        }
        results.style.display = 'block'
    })

    searchInput.addEventListener('input', () => {
        clearTimeout(timer)
        results.style.display = 'block'
        const q = searchInput.value.trim()
        const query = q.length < 1 ? '*' : q
        timer = setTimeout(() => {
            currentQuery = query
            currentPage = 1
            hasMore = false
            void search(query, 1)
        }, 300)
    })

    if (typeSelect) {
        typeSelect.addEventListener('change', () => {
            currentPage = 1
            hasMore = false
            void search(currentQuery, 1)
        })
    }

    if (config.initialId) {
        void fetchInitial(config.initialId)
    } else {
        setNoSelection()
        void search('*', 1)
    }

    return {
        element: container,
        getValue: () => hiddenId.value.trim() || null,
        getLabel: () => hiddenLabel.value
    }
}
