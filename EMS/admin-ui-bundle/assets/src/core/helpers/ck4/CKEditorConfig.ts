export class CKEditorConfig {
  public height: number = 400
  public format_tags: undefined|string = undefined
  public emsAjaxPaste: undefined|string = undefined
  public language: string = 'en'
  public stylesSet: undefined|string = undefined
  public contentsCss: undefined|string = undefined
  public referrerEmsId: undefined|string = undefined
  public div_wrapTable: string = 'true'
  public allowedContent: boolean|undefined = undefined
  public extraAllowedContent: string|undefined = undefined
  public ems: undefined|null|{
    translations: any[]
  } = undefined
}