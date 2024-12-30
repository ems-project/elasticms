/// <reference types="vite/client" />

import JsonMenuNestedComponent from './core/components/jsonMenuNestedComponent.ts'

declare global {
  interface Window {
    jsonMenuNestedComponents: { [key: string]: JsonMenuNestedComponent }
  }
}
