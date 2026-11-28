import type { EmsModule } from '../types/emsModule'

const SELECTOR = '#managed_alias_align_indexes'
const INDEX_SELECTOR = 'input.align-index'

export default class ManagedAlias implements EmsModule {
    public load(target: Document | HTMLElement): void {
        target.querySelectorAll<HTMLSelectElement>(SELECTOR).forEach((select) => {
            select.addEventListener('change', () => this.align(select))
        })
    }

    private align(select: HTMLSelectElement): void {
        const raw = select.selectedOptions[0]?.dataset.indexes

        if (raw === undefined) {
            return
        }

        const indexes = JSON.parse(raw) as string[]

        select.form?.querySelectorAll<HTMLInputElement>(INDEX_SELECTOR).forEach((checkbox) => {
            checkbox.checked = indexes.includes(checkbox.value)
        })
    }
}
