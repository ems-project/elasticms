import { Plugin } from 'ckeditor5/src/core.js'

import AssetManagerUI from './assetManagerUI.js'

export default class AssetManager extends Plugin {
  static get requires() {
    return [AssetManagerUI]
  }

  static get pluginName() {
    return 'AssetManager'
  }
}
