import {EditorRevisionOptions} from "./editorRevisionOptions.ts";
import {EditorProfile} from "./editorProfile.ts";
declare var CKEDITOR: { replace: (element: HTMLElement) => void };

export default class Ckeditor4 {

  private options: EditorRevisionOptions
  private element: HTMLElement
  private profile: EditorProfile
  constructor(element: HTMLElement, options: EditorRevisionOptions | null, profile: EditorProfile) {
    this.options = options ?? ({} as EditorRevisionOptions)
    this.element = element
    this.profile = profile
    this.create()
  }

  private create() {
    CKEDITOR.replace(this.element)
    console.log(this.options)
    console.log(this.profile)
  }
}