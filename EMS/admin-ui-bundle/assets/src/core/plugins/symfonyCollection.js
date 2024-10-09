import $ from 'jquery'
import a2lixLib from '@a2lix/symfony-collection/src/a2lix_sf_collection.ts'

export default class SymfonyCollection {
  load(target) {
    $(target)
      .find('.a2lix_lib_sf_collection')
      .each(function () {
        a2lixLib.sfCollection.init({
          collectionsSelector: '#' + $(this).attr('id'),
          manageRemoveEntry: true,
          lang: {
            add: $(this).data('lang-add'),
            remove: $(this).data('lang-remove')
          }
        })
      })
  }
}
