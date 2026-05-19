import IconSearch from '@tabler/icons/outline/zoom.svg?raw'
import IconReplace from '@tabler/icons/outline/zoom-replace.svg?raw'
import { Extension } from '@tiptap/core'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import { Decoration, DecorationSet } from '@tiptap/pm/view'
import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'

type Tab = 'find' | 'replace'
type SearchOptions = { caseSensitive: boolean; wholeWord: boolean; wrapAround: boolean }
type Match = { from: number; to: number }
type PluginState = { matches: Match[]; current: number }

const pluginKey = new PluginKey<PluginState>('findReplace')

const SearchHighlight = Extension.create({
    name: 'findReplaceHighlight',
    addProseMirrorPlugins() {
        return [
            new Plugin<PluginState>({
                key: pluginKey,
                state: {
                    init: () => ({ matches: [], current: -1 }),
                    apply(tr, value) {
                        const meta = tr.getMeta(pluginKey)
                        if (meta) return meta
                        if (tr.docChanged && value.matches.length) return { matches: [], current: -1 }
                        return value
                    }
                },
                props: {
                    decorations(state) {
                        const data = pluginKey.getState(state)
                        if (!data || !data.matches.length) return null
                        return DecorationSet.create(
                            state.doc,
                            data.matches.map((m, i) =>
                                Decoration.inline(m.from, m.to, {
                                    class: i === data.current ? 'findr-match findr-match--current' : 'findr-match'
                                })
                            )
                        )
                    }
                }
            })
        ]
    }
})

export const findReplaceModule: TiptapModule = {
    extensions: [SearchHighlight],
    toolbar: {
        group: 'find',
        items: [
            {
                name: 'Find',
                icon: IconSearch,
                tooltip: 'find',
                order: 1,
                command: (e) => openDialog(e, 'find'),
                isActive: () => false
            },
            {
                name: 'Replace',
                icon: IconReplace,
                tooltip: 'replace',
                order: 2,
                command: (e) => openDialog(e, 'replace'),
                isActive: () => false
            }
        ]
    }
}

const STYLES = `
    <style>    
        .findr-tabs { display: flex; gap: 4px; padding: 0 20px; border-bottom: 1px solid #ccc; }
        .findr-tab { padding: 6px 16px; cursor: pointer; background: #f0f0f0; border: 1px solid #ccc; border-radius: 4px 4px 0 0; margin-bottom: -1px; }
        .findr-tab--active { background: #fff; border-bottom-color: #fff; }
        .findr-panel { display: none; padding: 16px 20px 0; width: 450px; height: 220px; box-sizing: border-box; }
        .findr-panel--active { display: block; }
        .findr-row { display: flex; gap: 8px; margin-bottom: 8px; align-items: center; }
        .findr-row > label { min-width: 110px; }
        .findr-row input[type="text"] { flex: 1; height: 32px; padding: 0 8px; box-sizing: border-box; }
        .findr-btn { height: 32px; padding: 0 12px; min-width: 100px; background: #f4f4f4; border: 1px solid #ced4da; border-radius: 4px; cursor: pointer; white-space: nowrap; box-sizing: border-box; }
        .findr-btn:hover { background: #e8e8e8; }
        .findr-options { display: flex; flex-direction: column; gap: 8px; border: 1px solid #ccc; border-radius: 4px; padding: 8px 12px; margin: 8px 0; }
        .findr-options legend { width: auto; margin: 0; padding: 0 6px; font-size: inherit; border-bottom: 0; }
        .findr-option { display: flex; align-items: center; gap: 8px; }
        .findr-status { margin-top: 8px; font-size: 12px; color: #666; min-height: 16px; }
        .findr-match { background: #fff3a0; }
        .findr-match--current { background: #ffae42; }
        
        .dialog-body .findr-option input[type="checkbox"] { width: auto; margin: 0; }
        .dialog-body .findr-option label { margin: 0; line-height: 1; }
    </style>
`

function optionsHtml(e: TiptapEditor, name: Tab): string {
    return `
        <fieldset class="findr-options">
            <legend>${e.trans('find_options')}</legend>
            <div class="findr-option">
                <input type="checkbox" class="findr-opt-case" id="findr-opt-case-${name}">
                <label for="findr-opt-case-${name}">${e.trans('find_match_case')}</label>
            </div>
            <div class="findr-option">
                <input type="checkbox" class="findr-opt-word" id="findr-opt-word-${name}">
                <label for="findr-opt-word-${name}">${e.trans('find_match_word')}</label>
            </div>
            <div class="findr-option">
                <input type="checkbox" class="findr-opt-wrap" id="findr-opt-wrap-${name}" checked>
                <label for="findr-opt-wrap-${name}">${e.trans('find_match_wrap')}</label>
            </div>
        </fieldset>
    `
}

function panelHtml(e: TiptapEditor, name: Tab, withReplace: boolean): string {
    const rows = withReplace
        ? `<div class="findr-row">
               <label>${e.trans('find_what')}:</label>
               <input type="text" class="findr-find-input">
               <button type="button" class="findr-btn findr-btn-replace">${e.trans('replace')}</button>
           </div>
           <div class="findr-row">
               <label>${e.trans('replace_with')}:</label>
               <input type="text" class="findr-replace-input">
               <button type="button"  class="findr-btn findr-btn-replace-all">${e.trans('replace_all')}</button>
           </div>`
        : `<div class="findr-row">
               <label>${e.trans('find_what')}:</label>
               <input type="text" class="findr-find-input">
               <button type="button" class="findr-btn findr-btn-find">${e.trans('find')}</button>
           </div>`

    return `
        <div class="findr-panel" data-panel="${name}">
            ${rows}
            ${optionsHtml(e, name)}
            <div class="findr-status"></div>
        </div>
    `
}

function buildContent(e: TiptapEditor): string {
    return `
        ${STYLES}
        <div class="findr-tabs">
            <button type="button" class="findr-tab" data-tab="find">${e.trans('find')}</button>
            <button type="button" class="findr-tab" data-tab="replace">${e.trans('replace')}</button>
        </div>
        ${panelHtml(e, 'find', false)}
        ${panelHtml(e, 'replace', true)}
    `
}

function findMatches(e: TiptapEditor, query: string, opts: SearchOptions): Match[] {
    if (!query) return []
    const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
    const pattern = opts.wholeWord ? `\\b${escaped}\\b` : escaped
    const regex = new RegExp(pattern, opts.caseSensitive ? 'g' : 'gi')
    const matches: Match[] = []
    e.tiptap.state.doc.descendants((node, pos) => {
        if (!node.isText || !node.text) return
        let m: RegExpExecArray | null
        while ((m = regex.exec(node.text)) !== null) {
            matches.push({ from: pos + m.index, to: pos + m.index + m[0].length })
        }
    })
    return matches
}

function setPluginState(e: TiptapEditor, matches: Match[], current: number) {
    e.tiptap.view.dispatch(e.tiptap.state.tr.setMeta(pluginKey, { matches, current }))
}

function selectMatch(e: TiptapEditor, m: Match) {
    e.tiptap.chain().setTextSelection({ from: m.from, to: m.to }).scrollIntoView().run()
}

function setupTabs(el: HTMLElement, initial: Tab) {
    const tabs = el.querySelectorAll<HTMLButtonElement>('.findr-tab')
    const panels = el.querySelectorAll<HTMLElement>('.findr-panel')
    const activate = (name: Tab) => {
        const current = el.querySelector<HTMLElement>('.findr-panel--active')
        const currentValue = current?.querySelector<HTMLInputElement>('.findr-find-input')?.value
        tabs.forEach((tab) => tab.classList.toggle('findr-tab--active', tab.dataset.tab === name))
        panels.forEach((p) => p.classList.toggle('findr-panel--active', p.dataset.panel === name))
        if (currentValue) {
            const next = el.querySelector<HTMLInputElement>(`[data-panel="${name}"] .findr-find-input`)
            if (next) next.value = currentValue
        }
    }
    tabs.forEach((tab) => tab.addEventListener('click', () => activate(tab.dataset.tab as Tab)))
    activate(initial)
}

function setupPanel(panel: HTMLElement, e: TiptapEditor) {
    const findInput = panel.querySelector<HTMLInputElement>('.findr-find-input')!
    const replaceInput = panel.querySelector<HTMLInputElement>('.findr-replace-input')
    const status = panel.querySelector<HTMLElement>('.findr-status')!
    const state = { matches: [] as Match[], current: -1, lastQuery: '', lastOpts: '' }

    const getOpts = (): SearchOptions => ({
        caseSensitive: panel.querySelector<HTMLInputElement>('.findr-opt-case')!.checked,
        wholeWord: panel.querySelector<HTMLInputElement>('.findr-opt-word')!.checked,
        wrapAround: panel.querySelector<HTMLInputElement>('.findr-opt-wrap')!.checked
    })

    const refresh = (opts: SearchOptions) => {
        state.matches = findMatches(e, findInput.value, opts)
        state.current = -1
        state.lastQuery = findInput.value
        state.lastOpts = JSON.stringify(opts)
    }

    const findNext = () => {
        const query = findInput.value
        if (!query) return
        const opts = getOpts()
        const optsKey = JSON.stringify(opts)
        if (query !== state.lastQuery || optsKey !== state.lastOpts) refresh(opts)

        if (!state.matches.length) {
            status.textContent = e.trans('find_no_matches')
            setPluginState(e, [], -1)
            return
        }

        const next = state.current + 1
        if (next >= state.matches.length) {
            if (!opts.wrapAround) {
                status.textContent = e.trans('find_end_reached')
                return
            }
            state.current = 0
        } else {
            state.current = next
        }

        setPluginState(e, state.matches, state.current)
        selectMatch(e, state.matches[state.current])
        status.textContent = `${state.current + 1} / ${state.matches.length}`
    }

    const replaceCurrent = () => {
        if (state.current < 0 || !state.matches.length) {
            findNext()
            return
        }
        const m = state.matches[state.current]
        e.tiptap.view.dispatch(e.tiptap.state.tr.insertText(replaceInput?.value ?? '', m.from, m.to))
        state.lastQuery = ''
        findNext()
    }

    const replaceAll = () => {
        const query = findInput.value
        if (!query) return
        const matches = findMatches(e, query, getOpts())
        if (!matches.length) {
            status.textContent = e.trans('find_no_matches')
            return
        }
        const text = replaceInput?.value ?? ''
        const tr = e.tiptap.state.tr
        for (let i = matches.length - 1; i >= 0; i--) {
            tr.insertText(text, matches[i].from, matches[i].to)
        }
        e.tiptap.view.dispatch(tr)
        status.textContent = `${matches.length} ${e.trans('find_replaced')}`
        state.lastQuery = ''
        setPluginState(e, [], -1)
    }

    findInput.addEventListener('input', () => { state.lastQuery = '' })
    findInput.addEventListener('keydown', (ev) => {
        if (ev.key === 'Enter') { ev.preventDefault(); findNext() }
    })
    panel.querySelector('.findr-btn-find')?.addEventListener('click', findNext)
    panel.querySelector('.findr-btn-replace')?.addEventListener('click', replaceCurrent)
    panel.querySelector('.findr-btn-replace-all')?.addEventListener('click', replaceAll)
}

function openDialog(e: TiptapEditor, initialTab: Tab) {
    const dialog = e.createDialog('find_replace', 'flush-x')
    dialog.setContent(buildContent(e))
    dialog
        .onClose(() => setPluginState(e, [], -1))
        .addButton({
            label: e.trans('button_close'),
            variant: 'secondary',
            onClick: (d) => d.close()
        })
        .open()

    const el = dialog.element
    setupTabs(el, initialTab)
    el.querySelectorAll<HTMLElement>('.findr-panel').forEach((p) => setupPanel(p, e))
    el.querySelector<HTMLInputElement>(`[data-panel="${initialTab}"] .findr-find-input`)?.focus()
}