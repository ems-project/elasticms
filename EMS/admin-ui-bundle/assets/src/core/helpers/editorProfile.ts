export class EditorProfile {
  editor: string = ''
  styles: {
    name: string
    config: object
  }[] = []
  config: {
    emsBrowsers: undefined|{
      browser_object: undefined|{
        url: string
        label: string
      }
      browser_file: undefined|{
        url: string
        label: string
      }
      browser_image: undefined|{
        url: string
        label: string
      }
    }
  } = {
    emsBrowsers: undefined
  }
}
