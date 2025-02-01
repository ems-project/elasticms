import {EditorRevisionOptions} from "./editorRevisionOptions.ts";

import {EditorProfile} from "./editorProfile.ts";

export default class Ckeditor4Ems {

  private options: EditorRevisionOptions
  private element: HTMLElement
  private profile: EditorProfile
  constructor(element: HTMLElement, options: EditorRevisionOptions | null, profile: EditorProfile) {
    this.options = options ?? ({} as EditorRevisionOptions)
    this.element = element
    this.profile = profile
    this.create(element)
  }

  private create(element: HTMLElement) {
    console.log('cke4')
  }
}