const iconLoaders: Record<string, () => Promise<{ default: string }>> = {
    home: () => import('@tabler/icons/outline/home.svg?raw'),
    delete: () => import('@tabler/icons/outline/x.svg?raw'),

    folder: () => import('@tabler/icons/outline/folder.svg?raw'),
    'folder-delete': () => import('@tabler/icons/outline/folder-x.svg?raw'),
    'folder-rename': () => import('@tabler/icons/outline/folder-pin.svg?raw'),
    'folder-move': () => import('@tabler/icons/outline/folder-symlink.svg?raw'),
    upload: () => import('@tabler/icons/outline/file-upload.svg?raw'),
    'file-delete': () => import('@tabler/icons/outline/file-x.svg?raw'),
    'file-rename': () => import('@tabler/icons/outline/file-pencil.svg?raw'),
    'file-move': () => import('@tabler/icons/outline/file-symlink.svg?raw'),
    'file-view': () => import('@tabler/icons/outline/file-info.svg?raw'),
    revision: () => import('@tabler/icons/outline/archive.svg?raw'),
    'chevrons-right': () => import('@tabler/icons/outline/chevrons-right.svg?raw'),
    'chevrons-left': () => import('@tabler/icons/outline/chevrons-left.svg?raw')
}

const cache = new Map<string, string>()

async function loadIcon(name: string) {
    if (cache.has(name)) return cache.get(name)!
    const loader = iconLoaders[name]
    if (!loader) return null
    const svg = (await loader()).default.replace('<svg', '<svg class="icon"')
    cache.set(name, svg)
    return svg
}

export function initIcons(root: ParentNode & Node = document.body) {
    const render = (el: HTMLElement) => {
        const name = el.dataset.icon
        if (!name) return
        const position = el.dataset.iconPosition === 'end' ? 'beforeend' : 'afterbegin'
        delete el.dataset.icon
        delete el.dataset.iconPosition
        loadIcon(name).then((svg) => {
            if (svg) el.insertAdjacentHTML(position, svg)
        })
    }

    root.querySelectorAll<HTMLElement>('[data-icon]').forEach(render)

    new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (!(node instanceof HTMLElement)) return
                if (node.matches('[data-icon]')) render(node)
                node.querySelectorAll<HTMLElement>('[data-icon]').forEach(render)
            })
        })
    }).observe(root, { childList: true, subtree: true })
}
