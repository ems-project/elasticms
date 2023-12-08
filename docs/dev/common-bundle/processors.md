# Images processor

## Create a link to an asset

All file field (indexed file field and file field) share the same base structure:

 - filename: mainly the original file name uploaded by the used (but it can be adjusted by the user)
 - mimetype: mainly the original mimetype identified by the user's computer (but it can be adjusted by the user). If missing it will be guessed from the filename.
 - sha1: the file's hash
 - filesize: the file size
 
 Example of a JSON containing a `file` file field:
 ```json
{
    "_contenttype": "asset",
    "_finalization_datetime": "2020-09-29T12:43:50+0200",
    "file": {
        "filename": "Stakeholders_DE.png",
        "filesize": 122941,
        "mimetype": "image\/png",
        "sha1": "aa74ddf53ca612aaf35ca12fd5ceecd8cffe10f8"
    },
    "_published_datetime": "2020-09-29T12:43:50+0200"
}
```

Inside Twig, when you want to generate a link to an asset you can call the twig function `ems_asset_path`. Example:

```twig
<a href="{{ ems_asset_path(source.file) }}">{{ 'download.link'|trans }}</a>
```

## Processor
Regarding the file's type, it's possible to generate response from a source file. I.e. it's possible to crop an image directly from the twig. But there are parameters that works regardless the file's type:
 - `_disposition`: will specify to the browser if it should render the asset or ask the user to save it locally:
    - with the value `attachment` the browser will propose to save it to the user with a `save as ...` dialog
    - with the value `inline` (default value) the browser will try to display it directly in the browser if the mime-type is supported
 - `_file_names` (array of file path) if provided the first file found will be used instead of the original asset 
 - `_config_type` specify the processor that should process the asset. If not define the asset itself will be delivered
    - `image` will generate an image (PNG, SVG or JPEG) using the [image processor](#Image processor)
    - `zip` will generate a ZIP archive the [ZIP processor](#ZIP processor)
 - `_get_file_path` if set to true will generate a server path to a file. Not an url path. To use in case of PDF generation or for local reports.
 - `_mime_type` Can be used to override the file field's `mimetype` value.
 - `_username` if defined the asset will be password protected with the provided username.
 - `_password` if defined the asset will be password protected with the provided password.
 - `_before` if defined the asset will be available until before the UNIX epoch integer or the strtotime string provided.
 - `_after` if defined the asset will be available after the UNIX epoch integer or the strtotime string provided.
 
In the following example the path generated will force to download the asset:
```twig
<a href="{{ ems_asset_path(source.file, {
   _disposition: 'attachment' 
}) }}">{{ 'download.link'|trans }}</a>
```

And in this sample il will generate an url to a file on the sever file system:
```twig
<a href="{{ ems_asset_path({
   filename: 'Rapport.pdf',
   mimetype: 'application/pdf',
   sha1: 'dummy'
}, {
   _file_names: [
       '/opt/files/rapport.pdf',
       'c:\\dev\\assets\\test_data\\rapport.pdf'
   ]
}) }}">{{ 'download.link'|trans }}</a>
```

## Image processor

With this processor you'll be able to generate images from a source asset:
 - `_resize` will resize the image using one of those algorithms. Default value `fill`. This parameter des not apply on SVG images:
     - `fill` will leave margins in the color defined by the `_border_color` parameter (or transparent if the `_quality` parameter is set to zero)
     - `fillArea` will crop to best fill the generated image without distort the image and without leave margins 
     - `free` will distort the image to fill the image
     - `ratio` finds optimal crops for images, based on Jonas Wagner's [smartcrop.js](https://github.com/jwagner/smartcrop.js)
     - `smartCrop` will compute the width or height in order to keep the original image ratio. Set `_width` or `_height` to `0` to specify the auto-size on the width or the height
 - `_gravity` will specify the gravity to use if `fillArea` the image. Default value `center`. Possible values:
     - `center`
     - `north`
     - `south`
     - `east`
     - `north-west`
     - `south-west`
     - `north-east`
     - `south-east`
 - `_quality` is an integer between 0 and 100. Default value `0`. Is set to 0 a PNG will be generated, otherwise is refers to the quality of the JPEG generated. 
 - `_background` color used to replace (semi-)transparent pixels in the format `#000000`. Default value `#FFFFFFFF`
 - `_height` define the height (in pixel) of the generated image is `_resize` is defined. Default value `200`. If not define it will be computed from the `_width` parameter in order to preserve the initial proportion.
 - `_width` define the height (in pixel) of the generated image is `_resize` is defined. Default value `300`. If not define it will be computed from the `_width` parameter in order to preserve the initial proportion.
 - `_radius` make rounded corners to the image using the `_border_color`
 - `_radius_geometry` define which corners must be rounded if `_radius` is defined. It's an array with the list of corners to treat. Default value `['topleft', 'topright', 'bottomright', 'bottomleft']` Possible values:
     - `'topleft'`
     - `'topright'`
     - `'bottomright'`
     - `'bottomleft'`
 - `_watermark_hash` hash of a PNG file to watermark the image. The PNG must present in one of the storage services defined.
 - `_rotate` The source image will be rotated (in degrees). Default value `0`. Must contain an integer or a float.
 - `_auto_rotate` If set to `true`, the source image will be rotated following the `Orientation` defined in the image's metadata (if defined). Default value `true`. Possible values are `true` or `false`.
 - `_flip_horizontal` If set to `true`, the source image will be horizontally flipped. Default value `false`. Possible values are `true`or `false`.
 - `_flip_vertical` If set to `true`, the source image will be vertically flipped. Default value `false`. Possible values are `true`or `false`.
 - `_image_format` By default the image processor generate jpeg files, and png if the compression quality is set to 0. But you may want to use image formats by setting this parameter. Default value `null`. Possible values are:
   - `null` -> will generate a jpg or a png if `_quality` is equal to `0`
   - `webp`
   - `jpeg`
   - `png` -> `_quality` is ignored
   - `bmp` -> `_quality` is ignored
   - `gif` -> `_quality` is ignored

In this example it will generate a PNG of 400 pixels of width. The height will be defined by the proportion of the original image:
```twig
<img src="{{ ems_asset_path(source.file, {
   _config_type: 'image',
   _resize: 'ratio',
   _width: 400,
   _height: 0,
   _quality: 0        
}) }}">
```

## ZIP processor

```twig
{{ ems_asset_path({
    filename: 'download.zip'
}, {
   _config_type: 'zip',
   _disposition: 'attachment',       
   _files: [
       document.file_csv,
       document.file_pdf,       
   ]
}) }}
```
