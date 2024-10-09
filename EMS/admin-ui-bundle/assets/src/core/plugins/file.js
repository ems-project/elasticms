import $ from 'jquery'
import FileUploader from '@elasticms/file-uploader'
import { AddedDomEvent } from '../events/addedDomEvent'
import PickFileFromServer from '../helpers/pickFileFromServer'
import '../../../css/core/plugins/file.scss'
import ajaxRequest from '../components/ajaxRequest'

class File {
  constructor() {
    const primaryBox = $('body')
    this.initUpload = primaryBox.data('init-upload')
    this.fileExtract = primaryBox.data('file-extract')
    this.fileExtractForced = primaryBox.data('file-extract-forced')
    this.hashAlgo = primaryBox.data('hash-algo')
  }

  load(target) {
    const query = $(target)
    const self = this
    const pickFileFromServer = new PickFileFromServer()
    pickFileFromServer.load(target)

    query.find('.file-uploader-row').on('updateAssetData', function (event) {
      self.onAssetData(this, event.originalEvent.detail)
    })

    query.find('.extract-file-info').click(function () {
      const query = $(this).closest('.modal-content')
      self.fileDataExtrator(query, true)
    })

    query.find('.clear-asset-button').click(function () {
      const parent = $(this).closest('.file-uploader-row')
      const sha1Input = $(parent).find('.sha1')
      const typeInput = $(parent).find('.type')
      const nameInput = $(parent).find('.name')
      const progressBar = $(parent).find('.progress-bar')
      const progressText = $(parent).find('.progress-text')
      const progressNumber = $(parent).find('.progress-number')
      const previewTab = $(parent).find('.asset-preview-tab')
      const uploadTab = $(parent).find('.asset-upload-tab')
      const assetHashSignature = $(parent).find('.asset-hash-signature')
      const dateInput = $(parent).find('.date')
      const authorInput = $(parent).find('.author')
      const languageInput = $(parent).find('.language')
      const contentInput = $(parent).find('.content')
      const titleInput = $(parent).find('.title')

      $(parent).find('.file-uploader-input').val('')
      sha1Input.val('')
      assetHashSignature.empty()
      typeInput.val('')
      nameInput.val('')
      $(dateInput).val('')
      $(authorInput).val('')
      $(languageInput).val('')
      $(contentInput).val('')
      $(titleInput).val('')
      $(progressBar).css('width', '0%')
      $(progressText).html('')
      $(progressNumber).html('')
      previewTab.addClass('hidden')
      uploadTab.removeClass('hidden')
      $(parent).find('.view-asset-button').addClass('disabled')
      $(this).addClass('disabled')
      return false
    })

    const fileUploaderInputs = target.getElementsByClassName('file-uploader-input')

    ;[].forEach.call(fileUploaderInputs, function (fileUploaderInput) {
      fileUploaderInput.onchange = () => {
        self.initFilesUploader(fileUploaderInput.files, fileUploaderInput)
      }
    })

    query.find('.file-uploader-row').each(function () {
      // file drop
      this.addEventListener('dragover', self.fileDragHover, false)
      this.addEventListener('dragleave', self.fileDragHover, false)
      this.addEventListener(
        'drop',
        function (e) {
          self.fileDragHover(e)
          const files = e.target.files || e.dataTransfer.files
          self.initFilesUploader(files, this)
        },
        false
      )
    })
  }

  initFilesUploader(files, context) {
    const container = $(context).closest('.file-uploader-row')
    const template = container.data('multiple')
    const previewTab = container.find('.tab-pane.asset-preview-tab')
    const uploadTab = container.find('.tab-pane.asset-upload-tab')
    const listTab = container.find('.tab-pane.asset-list-tab > ol')

    if (typeof template !== 'undefined') {
      listTab.removeClass('hidden')
      previewTab.addClass('hidden')
      uploadTab.addClass('hidden')
    }

    let nextId = parseInt(listTab.attr('data-file-list-index'))
    listTab.attr('data-file-list-index', nextId + files.length)

    for (let i = 0; i < files.length; ++i) {
      if (!Object.hasOwn(files, i)) {
        continue
      }

      if (typeof template !== 'undefined') {
        const subContainer = $(template.replace(/__name__/g, nextId++))
        listTab.append(subContainer)
        const event = new AddedDomEvent(subContainer.get(0))
        event.dispatch()
        this.initFileUploader(files[i], subContainer)
      } else {
        this.initFileUploader(files[i], container)
        break
      }
    }
  }

  initFileUploader(fileHandler, container) {
    const mainDiv = $(container)
    const metaFields = typeof mainDiv.data('meta-fields') !== 'undefined'
    const sha1Input = mainDiv.find('.sha1')
    const typeInput = mainDiv.find('.type')
    const nameInput = mainDiv.find('.name')
    const progressBar = mainDiv.find('.progress-bar')
    const progressText = mainDiv.find('.progress-text')
    const progressNumber = mainDiv.find('.progress-number')
    const viewButton = mainDiv.find('.view-asset-button')
    const clearButton = mainDiv.find('.clear-asset-button')
    const previewTab = mainDiv.find('.asset-preview-tab')
    const uploadTab = mainDiv.find('.asset-upload-tab')
    const previewLink = mainDiv.find('.img-responsive')
    const assetHashSignature = mainDiv.find('.asset-hash-signature')
    const dateInput = mainDiv.find('.date')
    const authorInput = mainDiv.find('.author')
    const languageInput = mainDiv.find('.language')
    const contentInput = mainDiv.find('.content')
    const titleInput = mainDiv.find('.title')
    const self = this

    previewTab.addClass('hidden')
    uploadTab.removeClass('hidden')

    const fileUploader = new FileUploader({
      file: fileHandler,
      algo: this.hashAlgo,
      initUrl: this.initUpload,
      emsListener: this,
      onHashAvailable: function (hash, type, name) {
        $(sha1Input).val(hash)
        $(assetHashSignature).empty().append(hash)
        $(typeInput).val(type)
        $(nameInput).val(name)
        $(dateInput).val('')
        $(authorInput).val('')
        $(languageInput).val('')
        $(contentInput).val('')
        $(titleInput).val('')
        $(viewButton).addClass('disabled')
        $(clearButton).addClass('disabled')
      },
      onProgress: function (status, progress, remaining) {
        if (status !== 'Computing hash' && $(sha1Input).val() !== fileUploader.hash) {
          $(sha1Input).val(fileUploader.hash)
          console.log('Sha1 mismatch!')
        }
        const percentage = Math.round(progress * 100)
        $(progressBar).css('width', percentage + '%')
        $(progressText).html(status)
        $(progressNumber).html(remaining)
      },
      onUploaded: function (assetUrl, previewUrl) {
        viewButton.attr('href', assetUrl)
        previewLink.attr('src', previewUrl)
        viewButton.removeClass('disabled')
        clearButton.removeClass('disabled')
        previewTab.removeClass('hidden')
        uploadTab.addClass('hidden')

        if (metaFields && $(contentInput).length) {
          self.fileDataExtrator(container)
        } else if (typeof self.onChangeCallback === 'function') {
          self.onChangeCallback()
        }
      },
      onError: function (message, code) {
        $(progressBar).css('width', '0%')
        $(progressText).html(message)
        if (code === undefined) {
          $(progressNumber).html('')
        } else {
          $(progressNumber).html('Error code : ' + code)
        }
        $(sha1Input).val('')
        $(assetHashSignature).empty()
        $(typeInput).val('')
        $(nameInput).val('')
        $(dateInput).val('')
        $(authorInput).val('')
        $(languageInput).val('')
        $(contentInput).val('')
        $(titleInput).val('')
        $(viewButton).addClass('disabled')
        $(clearButton).addClass('disabled')
      }
    })
  }

  fileDataExtrator(container, forced = false) {
    const self = this

    const sha1Input = $(container).find('.sha1')
    const nameInput = $(container).find('.name')

    const dateInput = $(container).find('.date')
    const authorInput = $(container).find('.author')
    const languageInput = $(container).find('.language')
    const contentInput = $(container).find('.content')
    const titleInput = $(container).find('.title')

    const progressText = $(container).find('.progress-text')
    const progressNumber = $(container).find('.progress-number')
    const previewTab = $(container).find('.asset-preview-tab')
    const uploadTab = $(container).find('.asset-upload-tab')

    const urlPattern = (forced ? this.fileExtractForced : this.fileExtract)
      .replace(/__file_identifier__/g, $(sha1Input).val())
      .replace(/__file_name__/g, $(nameInput).val())

    $(progressText).html('Extracting information from asset...')
    $(progressNumber).html('')
    uploadTab.show()
    previewTab.hide()

    ajaxRequest
      .get(urlPattern)
      .success(function (response) {
        $(dateInput).val(response.date)
        $(authorInput).val(response.author)
        $(languageInput).val(response.language)
        $(contentInput).val(response.content)
        $(titleInput).val(response.title)
      })
      .always(function () {
        $(progressText).html('')
        uploadTab.hide()
        previewTab.show()
        if (typeof self.onChangeCallback === 'function') {
          self.onChangeCallback()
        }
      })
  }

  fileDragHover(e) {
    e.stopPropagation()
    e.preventDefault()
  }

  onAssetData(row, data) {
    const mainDiv = $(row)
    const sha1Input = mainDiv.find('.sha1')
    const metaFields = typeof mainDiv.data('meta-fields') !== 'undefined'
    const typeInput = mainDiv.find('.type')
    const nameInput = mainDiv.find('.name')
    const assetHashSignature = mainDiv.find('.asset-hash-signature')
    const dateInput = mainDiv.find('.date')
    const authorInput = mainDiv.find('.author')
    const languageInput = mainDiv.find('.language')
    const contentInput = mainDiv.find('.content')
    const titleInput = mainDiv.find('.title')
    const viewButton = mainDiv.find('.view-asset-button')
    const clearButton = mainDiv.find('.clear-asset-button')
    const previewTab = mainDiv.find('.asset-preview-tab')
    const uploadTab = mainDiv.find('.asset-upload-tab')
    const previewLink = mainDiv.find('.img-responsive')
    sha1Input.val(data.sha1)
    assetHashSignature.empty().append(data.sha1)
    typeInput.val(data.mimetype)
    nameInput.val(data.filename)
    viewButton.attr('href', data.view_url)
    previewLink.attr('src', data.preview_url)
    dateInput.val('')
    authorInput.val('')
    languageInput.val('')
    contentInput.val('')
    titleInput.val('')
    viewButton.removeClass('disabled')
    clearButton.removeClass('disabled')
    previewTab.removeClass('hidden')
    uploadTab.addClass('hidden')

    if (metaFields) {
      this.fileDataExtrator(row)
    } else if (typeof this.onChangeCallback === 'function') {
      this.onChangeCallback()
    }
  }
}

export default File
