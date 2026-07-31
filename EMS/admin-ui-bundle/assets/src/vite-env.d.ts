/// <reference types="vite/client" />

import JsonMenuNestedComponent from './core/components/jsonMenuNestedComponent.ts'
import { AjaxModalLike } from './core/components/MediaLibrary/MediaLibrary.ts'

declare global {
    interface Window {
        jsonMenuNestedComponents: { [key: string]: JsonMenuNestedComponent }
        EmsListeners: typeof EmsListeners
        /**
         * Set by the legacy bootstrap3 core-bundle `helper/ajaxModal.js`, so components
         * loaded through `core-bundle.ts` (e.g. MediaLibrary) can reuse the ajaxModal
         * instance matching that theme instead of the bootstrap5 one.
         */
        ajaxModal?: AjaxModalLike
    }
    interface DocumentEventMap {
        emsAddedDomEvent: CustomEvent<{ target: HTMLElement }>
    }
}
