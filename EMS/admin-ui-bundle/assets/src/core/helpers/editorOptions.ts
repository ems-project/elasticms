import type { EditorConfig } from 'ckeditor5/src/core.d.ts'

export class EditorOptions implements EditorConfig {
  licenseKey: string = ''
  toolbar: any
  style: any
  heading: any
  htmlSupport: any
  plugins: any
  language: any
  image: any
  table: any
  extraPlugins: any
}
