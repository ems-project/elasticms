export interface EmsModule {
    load(target: Document | HTMLElement): void | Promise<void>
}