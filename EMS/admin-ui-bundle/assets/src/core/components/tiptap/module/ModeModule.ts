import { TiptapModule } from '../types.ts'

export const ModeModule: TiptapModule = {
    name: 'mode',
    extensions: [],
    groups: {
        mode: ['source', 'maximize']
    },
    actions: {
        source: {
            icon: 'source',
            tooltip: 'Source Code',
            command: (_editor, ctx) => {
                ctx.isSourceView = !ctx.isSourceView
                const container = ctx.element.parentElement
                const toolbar = container?.querySelector('.wysiwyg-toolbar') as HTMLElement
                const sourceBtn = toolbar?.querySelector('[data-action="source"]')

                if (container) {
                    container.classList.toggle('is-source-mode', ctx.isSourceView)
                    if (ctx.isSourceView) {
                        ctx.element.value = ctx.innerEditor?.getHTML() || ''
                        sourceBtn?.classList.add('is-active')
                        ctx.setToolbarDisabled(toolbar, true)
                    } else {
                        ctx.innerEditor?.commands.setContent(ctx.element.value)
                        sourceBtn?.classList.remove('is-active')
                        ctx.setToolbarDisabled(toolbar, false)
                    }
                }
            },
            isActive: (_editor, ctx) => ctx?.isSourceView ?? false
        },
        maximize: {
            icon: 'maximize',
            tooltip: 'Maximize / Fullscreen',
            command: (_editor, ctx) => {
                ctx.isMaximized = !ctx.isMaximized
                const container = ctx.element.parentElement
                const btn = container?.querySelector('[data-action="maximize"]') as HTMLElement

                if (container) {
                    container.classList.toggle('is-maximized', ctx.isMaximized)
                    btn.innerHTML = ctx.isMaximized ? ctx.icons.minimize : ctx.icons.maximize
                    document.body.style.overflow = ctx.isMaximized ? 'hidden' : ''
                }
            },
            isActive: (_editor, ctx) => ctx?.isMaximized ?? false
        }
    }
}
