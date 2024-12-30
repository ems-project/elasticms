import { ChangeEvent } from '../events/changeEvent'
import '../../../css/core/plugins/codeEditor.scss'

const modules: Record<string, () => Promise<{ default: string }>> = {
  'ace/ext/beautify': () => import('ace-builds/src-noconflict/ext-beautify.js?url'),
  'ace/ext/code_lens': () => import('ace-builds/src-noconflict/ext-code_lens.js?url'),
  'ace/ext/command_bar': () => import('ace-builds/src-noconflict/ext-command_bar.js?url'),
  'ace/ext/elastic_tabstops_lite': () =>
    import('ace-builds/src-noconflict/ext-elastic_tabstops_lite.js?url'),
  'ace/ext/emmet': () => import('ace-builds/src-noconflict/ext-emmet.js?url'),
  'ace/ext/error_marker': () => import('ace-builds/src-noconflict/ext-error_marker.js?url'),
  'ace/ext/hardwrap': () => import('ace-builds/src-noconflict/ext-hardwrap.js?url'),
  'ace/ext/inline_autocomplete': () =>
    import('ace-builds/src-noconflict/ext-inline_autocomplete.js?url'),
  'ace/ext/keyboard_menu': () => import('ace-builds/src-noconflict/ext-keybinding_menu.js?url'),
  'ace/ext/language_tools': () => import('ace-builds/src-noconflict/ext-language_tools.js?url'),
  'ace/ext/linking': () => import('ace-builds/src-noconflict/ext-linking.js?url'),
  'ace/ext/modelist': () => import('ace-builds/src-noconflict/ext-modelist.js?url'),
  'ace/ext/options': () => import('ace-builds/src-noconflict/ext-options.js?url'),
  'ace/ext/prompt': () => import('ace-builds/src-noconflict/ext-prompt.js?url'),
  'ace/ext/rtl': () => import('ace-builds/src-noconflict/ext-rtl.js?url'),
  'ace/ext/searchbox': () => import('ace-builds/src-noconflict/ext-searchbox.js?url'),
  'ace/ext/settings_menu': () => import('ace-builds/src-noconflict/ext-settings_menu.js?url'),
  'ace/ext/simple_tokenizer': () => import('ace-builds/src-noconflict/ext-simple_tokenizer.js?url'),
  'ace/ext/spellcheck': () => import('ace-builds/src-noconflict/ext-spellcheck.js?url'),
  'ace/ext/split': () => import('ace-builds/src-noconflict/ext-split.js?url'),
  'ace/ext/static_highlight': () => import('ace-builds/src-noconflict/ext-static_highlight.js?url'),
  'ace/ext/statusbar': () => import('ace-builds/src-noconflict/ext-statusbar.js?url'),
  'ace/ext/textarea': () => import('ace-builds/src-noconflict/ext-textarea.js?url'),
  'ace/ext/themelist': () => import('ace-builds/src-noconflict/ext-themelist.js?url'),
  'ace/ext/whitespace': () => import('ace-builds/src-noconflict/ext-whitespace.js?url'),
  'ace/keyboard/emacs': () => import('ace-builds/src-noconflict/keybinding-emacs.js?url'),
  'ace/keyboard/sublime': () => import('ace-builds/src-noconflict/keybinding-sublime.js?url'),
  'ace/keyboard/vim': () => import('ace-builds/src-noconflict/keybinding-vim.js?url'),
  'ace/keyboard/vscode': () => import('ace-builds/src-noconflict/keybinding-vscode.js?url'),
  'ace/mode/abap': () => import('ace-builds/src-noconflict/mode-abap.js?url'),
  'ace/mode/abc': () => import('ace-builds/src-noconflict/mode-abc.js?url'),
  'ace/mode/actionscript': () => import('ace-builds/src-noconflict/mode-actionscript.js?url'),
  'ace/mode/ada': () => import('ace-builds/src-noconflict/mode-ada.js?url'),
  'ace/mode/alda': () => import('ace-builds/src-noconflict/mode-alda.js?url'),
  'ace/mode/apache_conf': () => import('ace-builds/src-noconflict/mode-apache_conf.js?url'),
  'ace/mode/apex': () => import('ace-builds/src-noconflict/mode-apex.js?url'),
  'ace/mode/applescript': () => import('ace-builds/src-noconflict/mode-applescript.js?url'),
  'ace/mode/aql': () => import('ace-builds/src-noconflict/mode-aql.js?url'),
  'ace/mode/asciidoc': () => import('ace-builds/src-noconflict/mode-asciidoc.js?url'),
  'ace/mode/asl': () => import('ace-builds/src-noconflict/mode-asl.js?url'),
  'ace/mode/assembly_x86': () => import('ace-builds/src-noconflict/mode-assembly_x86.js?url'),
  'ace/mode/astro': () => import('ace-builds/src-noconflict/mode-astro.js?url'),
  'ace/mode/autohotkey': () => import('ace-builds/src-noconflict/mode-autohotkey.js?url'),
  'ace/mode/batchfile': () => import('ace-builds/src-noconflict/mode-batchfile.js?url'),
  'ace/mode/bibtex': () => import('ace-builds/src-noconflict/mode-bibtex.js?url'),
  'ace/mode/c9search': () => import('ace-builds/src-noconflict/mode-c9search.js?url'),
  'ace/mode/c_cpp': () => import('ace-builds/src-noconflict/mode-c_cpp.js?url'),
  'ace/mode/cirru': () => import('ace-builds/src-noconflict/mode-cirru.js?url'),
  'ace/mode/clojure': () => import('ace-builds/src-noconflict/mode-clojure.js?url'),
  'ace/mode/cobol': () => import('ace-builds/src-noconflict/mode-cobol.js?url'),
  'ace/mode/coffee': () => import('ace-builds/src-noconflict/mode-coffee.js?url'),
  'ace/mode/coldfusion': () => import('ace-builds/src-noconflict/mode-coldfusion.js?url'),
  'ace/mode/crystal': () => import('ace-builds/src-noconflict/mode-crystal.js?url'),
  'ace/mode/csharp': () => import('ace-builds/src-noconflict/mode-csharp.js?url'),
  'ace/mode/csound_document': () => import('ace-builds/src-noconflict/mode-csound_document.js?url'),
  'ace/mode/csound_orchestra': () =>
    import('ace-builds/src-noconflict/mode-csound_orchestra.js?url'),
  'ace/mode/csound_score': () => import('ace-builds/src-noconflict/mode-csound_score.js?url'),
  'ace/mode/csp': () => import('ace-builds/src-noconflict/mode-csp.js?url'),
  'ace/mode/css': () => import('ace-builds/src-noconflict/mode-css.js?url'),
  'ace/mode/curly': () => import('ace-builds/src-noconflict/mode-curly.js?url'),
  'ace/mode/cuttlefish': () => import('ace-builds/src-noconflict/mode-cuttlefish.js?url'),
  'ace/mode/d': () => import('ace-builds/src-noconflict/mode-d.js?url'),
  'ace/mode/dart': () => import('ace-builds/src-noconflict/mode-dart.js?url'),
  'ace/mode/diff': () => import('ace-builds/src-noconflict/mode-diff.js?url'),
  'ace/mode/django': () => import('ace-builds/src-noconflict/mode-django.js?url'),
  'ace/mode/dockerfile': () => import('ace-builds/src-noconflict/mode-dockerfile.js?url'),
  'ace/mode/dot': () => import('ace-builds/src-noconflict/mode-dot.js?url'),
  'ace/mode/drools': () => import('ace-builds/src-noconflict/mode-drools.js?url'),
  'ace/mode/edifact': () => import('ace-builds/src-noconflict/mode-edifact.js?url'),
  'ace/mode/eiffel': () => import('ace-builds/src-noconflict/mode-eiffel.js?url'),
  'ace/mode/ejs': () => import('ace-builds/src-noconflict/mode-ejs.js?url'),
  'ace/mode/elixir': () => import('ace-builds/src-noconflict/mode-elixir.js?url'),
  'ace/mode/elm': () => import('ace-builds/src-noconflict/mode-elm.js?url'),
  'ace/mode/erlang': () => import('ace-builds/src-noconflict/mode-erlang.js?url'),
  'ace/mode/flix': () => import('ace-builds/src-noconflict/mode-flix.js?url'),
  'ace/mode/forth': () => import('ace-builds/src-noconflict/mode-forth.js?url'),
  'ace/mode/fortran': () => import('ace-builds/src-noconflict/mode-fortran.js?url'),
  'ace/mode/fsharp': () => import('ace-builds/src-noconflict/mode-fsharp.js?url'),
  'ace/mode/fsl': () => import('ace-builds/src-noconflict/mode-fsl.js?url'),
  'ace/mode/ftl': () => import('ace-builds/src-noconflict/mode-ftl.js?url'),
  'ace/mode/gcode': () => import('ace-builds/src-noconflict/mode-gcode.js?url'),
  'ace/mode/gherkin': () => import('ace-builds/src-noconflict/mode-gherkin.js?url'),
  'ace/mode/gitignore': () => import('ace-builds/src-noconflict/mode-gitignore.js?url'),
  'ace/mode/glsl': () => import('ace-builds/src-noconflict/mode-glsl.js?url'),
  'ace/mode/gobstones': () => import('ace-builds/src-noconflict/mode-gobstones.js?url'),
  'ace/mode/golang': () => import('ace-builds/src-noconflict/mode-golang.js?url'),
  'ace/mode/graphqlschema': () => import('ace-builds/src-noconflict/mode-graphqlschema.js?url'),
  'ace/mode/groovy': () => import('ace-builds/src-noconflict/mode-groovy.js?url'),
  'ace/mode/haml': () => import('ace-builds/src-noconflict/mode-haml.js?url'),
  'ace/mode/handlebars': () => import('ace-builds/src-noconflict/mode-handlebars.js?url'),
  'ace/mode/haskell': () => import('ace-builds/src-noconflict/mode-haskell.js?url'),
  'ace/mode/haskell_cabal': () => import('ace-builds/src-noconflict/mode-haskell_cabal.js?url'),
  'ace/mode/haxe': () => import('ace-builds/src-noconflict/mode-haxe.js?url'),
  'ace/mode/hjson': () => import('ace-builds/src-noconflict/mode-hjson.js?url'),
  'ace/mode/html': () => import('ace-builds/src-noconflict/mode-html.js?url'),
  'ace/mode/html_elixir': () => import('ace-builds/src-noconflict/mode-html_elixir.js?url'),
  'ace/mode/html_ruby': () => import('ace-builds/src-noconflict/mode-html_ruby.js?url'),
  'ace/mode/ini': () => import('ace-builds/src-noconflict/mode-ini.js?url'),
  'ace/mode/io': () => import('ace-builds/src-noconflict/mode-io.js?url'),
  'ace/mode/ion': () => import('ace-builds/src-noconflict/mode-ion.js?url'),
  'ace/mode/jack': () => import('ace-builds/src-noconflict/mode-jack.js?url'),
  'ace/mode/jade': () => import('ace-builds/src-noconflict/mode-jade.js?url'),
  'ace/mode/java': () => import('ace-builds/src-noconflict/mode-java.js?url'),
  'ace/mode/javascript': () => import('ace-builds/src-noconflict/mode-javascript.js?url'),
  'ace/mode/jexl': () => import('ace-builds/src-noconflict/mode-jexl.js?url'),
  'ace/mode/json': () => import('ace-builds/src-noconflict/mode-json.js?url'),
  'ace/mode/json5': () => import('ace-builds/src-noconflict/mode-json5.js?url'),
  'ace/mode/jsoniq': () => import('ace-builds/src-noconflict/mode-jsoniq.js?url'),
  'ace/mode/jsp': () => import('ace-builds/src-noconflict/mode-jsp.js?url'),
  'ace/mode/jssm': () => import('ace-builds/src-noconflict/mode-jssm.js?url'),
  'ace/mode/jsx': () => import('ace-builds/src-noconflict/mode-jsx.js?url'),
  'ace/mode/julia': () => import('ace-builds/src-noconflict/mode-julia.js?url'),
  'ace/mode/kotlin': () => import('ace-builds/src-noconflict/mode-kotlin.js?url'),
  'ace/mode/latex': () => import('ace-builds/src-noconflict/mode-latex.js?url'),
  'ace/mode/latte': () => import('ace-builds/src-noconflict/mode-latte.js?url'),
  'ace/mode/less': () => import('ace-builds/src-noconflict/mode-less.js?url'),
  'ace/mode/liquid': () => import('ace-builds/src-noconflict/mode-liquid.js?url'),
  'ace/mode/lisp': () => import('ace-builds/src-noconflict/mode-lisp.js?url'),
  'ace/mode/livescript': () => import('ace-builds/src-noconflict/mode-livescript.js?url'),
  'ace/mode/logiql': () => import('ace-builds/src-noconflict/mode-logiql.js?url'),
  'ace/mode/logtalk': () => import('ace-builds/src-noconflict/mode-logtalk.js?url'),
  'ace/mode/lsl': () => import('ace-builds/src-noconflict/mode-lsl.js?url'),
  'ace/mode/lua': () => import('ace-builds/src-noconflict/mode-lua.js?url'),
  'ace/mode/luapage': () => import('ace-builds/src-noconflict/mode-luapage.js?url'),
  'ace/mode/lucene': () => import('ace-builds/src-noconflict/mode-lucene.js?url'),
  'ace/mode/makefile': () => import('ace-builds/src-noconflict/mode-makefile.js?url'),
  'ace/mode/markdown': () => import('ace-builds/src-noconflict/mode-markdown.js?url'),
  'ace/mode/mask': () => import('ace-builds/src-noconflict/mode-mask.js?url'),
  'ace/mode/matlab': () => import('ace-builds/src-noconflict/mode-matlab.js?url'),
  'ace/mode/maze': () => import('ace-builds/src-noconflict/mode-maze.js?url'),
  'ace/mode/mediawiki': () => import('ace-builds/src-noconflict/mode-mediawiki.js?url'),
  'ace/mode/mel': () => import('ace-builds/src-noconflict/mode-mel.js?url'),
  'ace/mode/mips': () => import('ace-builds/src-noconflict/mode-mips.js?url'),
  'ace/mode/mixal': () => import('ace-builds/src-noconflict/mode-mixal.js?url'),
  'ace/mode/mushcode': () => import('ace-builds/src-noconflict/mode-mushcode.js?url'),
  'ace/mode/mysql': () => import('ace-builds/src-noconflict/mode-mysql.js?url'),
  'ace/mode/nasal': () => import('ace-builds/src-noconflict/mode-nasal.js?url'),
  'ace/mode/nginx': () => import('ace-builds/src-noconflict/mode-nginx.js?url'),
  'ace/mode/nim': () => import('ace-builds/src-noconflict/mode-nim.js?url'),
  'ace/mode/nix': () => import('ace-builds/src-noconflict/mode-nix.js?url'),
  'ace/mode/nsis': () => import('ace-builds/src-noconflict/mode-nsis.js?url'),
  'ace/mode/nunjucks': () => import('ace-builds/src-noconflict/mode-nunjucks.js?url'),
  'ace/mode/objectivec': () => import('ace-builds/src-noconflict/mode-objectivec.js?url'),
  'ace/mode/ocaml': () => import('ace-builds/src-noconflict/mode-ocaml.js?url'),
  'ace/mode/odin': () => import('ace-builds/src-noconflict/mode-odin.js?url'),
  'ace/mode/partiql': () => import('ace-builds/src-noconflict/mode-partiql.js?url'),
  'ace/mode/pascal': () => import('ace-builds/src-noconflict/mode-pascal.js?url'),
  'ace/mode/perl': () => import('ace-builds/src-noconflict/mode-perl.js?url'),
  'ace/mode/pgsql': () => import('ace-builds/src-noconflict/mode-pgsql.js?url'),
  'ace/mode/php': () => import('ace-builds/src-noconflict/mode-php.js?url'),
  'ace/mode/php_laravel_blade': () =>
    import('ace-builds/src-noconflict/mode-php_laravel_blade.js?url'),
  'ace/mode/pig': () => import('ace-builds/src-noconflict/mode-pig.js?url'),
  'ace/mode/plain_text': () => import('ace-builds/src-noconflict/mode-plain_text.js?url'),
  'ace/mode/plsql': () => import('ace-builds/src-noconflict/mode-plsql.js?url'),
  'ace/mode/powershell': () => import('ace-builds/src-noconflict/mode-powershell.js?url'),
  'ace/mode/praat': () => import('ace-builds/src-noconflict/mode-praat.js?url'),
  'ace/mode/prisma': () => import('ace-builds/src-noconflict/mode-prisma.js?url'),
  'ace/mode/prolog': () => import('ace-builds/src-noconflict/mode-prolog.js?url'),
  'ace/mode/properties': () => import('ace-builds/src-noconflict/mode-properties.js?url'),
  'ace/mode/protobuf': () => import('ace-builds/src-noconflict/mode-protobuf.js?url'),
  'ace/mode/prql': () => import('ace-builds/src-noconflict/mode-prql.js?url'),
  'ace/mode/puppet': () => import('ace-builds/src-noconflict/mode-puppet.js?url'),
  'ace/mode/python': () => import('ace-builds/src-noconflict/mode-python.js?url'),
  'ace/mode/qml': () => import('ace-builds/src-noconflict/mode-qml.js?url'),
  'ace/mode/r': () => import('ace-builds/src-noconflict/mode-r.js?url'),
  'ace/mode/raku': () => import('ace-builds/src-noconflict/mode-raku.js?url'),
  'ace/mode/razor': () => import('ace-builds/src-noconflict/mode-razor.js?url'),
  'ace/mode/rdoc': () => import('ace-builds/src-noconflict/mode-rdoc.js?url'),
  'ace/mode/red': () => import('ace-builds/src-noconflict/mode-red.js?url'),
  'ace/mode/redshift': () => import('ace-builds/src-noconflict/mode-redshift.js?url'),
  'ace/mode/rhtml': () => import('ace-builds/src-noconflict/mode-rhtml.js?url'),
  'ace/mode/robot': () => import('ace-builds/src-noconflict/mode-robot.js?url'),
  'ace/mode/rst': () => import('ace-builds/src-noconflict/mode-rst.js?url'),
  'ace/mode/ruby': () => import('ace-builds/src-noconflict/mode-ruby.js?url'),
  'ace/mode/rust': () => import('ace-builds/src-noconflict/mode-rust.js?url'),
  'ace/mode/sac': () => import('ace-builds/src-noconflict/mode-sac.js?url'),
  'ace/mode/sass': () => import('ace-builds/src-noconflict/mode-sass.js?url'),
  'ace/mode/scad': () => import('ace-builds/src-noconflict/mode-scad.js?url'),
  'ace/mode/scala': () => import('ace-builds/src-noconflict/mode-scala.js?url'),
  'ace/mode/scheme': () => import('ace-builds/src-noconflict/mode-scheme.js?url'),
  'ace/mode/scrypt': () => import('ace-builds/src-noconflict/mode-scrypt.js?url'),
  'ace/mode/scss': () => import('ace-builds/src-noconflict/mode-scss.js?url'),
  'ace/mode/sh': () => import('ace-builds/src-noconflict/mode-sh.js?url'),
  'ace/mode/sjs': () => import('ace-builds/src-noconflict/mode-sjs.js?url'),
  'ace/mode/slim': () => import('ace-builds/src-noconflict/mode-slim.js?url'),
  'ace/mode/smarty': () => import('ace-builds/src-noconflict/mode-smarty.js?url'),
  'ace/mode/smithy': () => import('ace-builds/src-noconflict/mode-smithy.js?url'),
  'ace/mode/snippets': () => import('ace-builds/src-noconflict/mode-snippets.js?url'),
  'ace/mode/soy_template': () => import('ace-builds/src-noconflict/mode-soy_template.js?url'),
  'ace/mode/space': () => import('ace-builds/src-noconflict/mode-space.js?url'),
  'ace/mode/sparql': () => import('ace-builds/src-noconflict/mode-sparql.js?url'),
  'ace/mode/sql': () => import('ace-builds/src-noconflict/mode-sql.js?url'),
  'ace/mode/sqlserver': () => import('ace-builds/src-noconflict/mode-sqlserver.js?url'),
  'ace/mode/stylus': () => import('ace-builds/src-noconflict/mode-stylus.js?url'),
  'ace/mode/svg': () => import('ace-builds/src-noconflict/mode-svg.js?url'),
  'ace/mode/swift': () => import('ace-builds/src-noconflict/mode-swift.js?url'),
  'ace/mode/tcl': () => import('ace-builds/src-noconflict/mode-tcl.js?url'),
  'ace/mode/terraform': () => import('ace-builds/src-noconflict/mode-terraform.js?url'),
  'ace/mode/tex': () => import('ace-builds/src-noconflict/mode-tex.js?url'),
  'ace/mode/text': () => import('ace-builds/src-noconflict/mode-text.js?url'),
  'ace/mode/textile': () => import('ace-builds/src-noconflict/mode-textile.js?url'),
  'ace/mode/toml': () => import('ace-builds/src-noconflict/mode-toml.js?url'),
  'ace/mode/tsx': () => import('ace-builds/src-noconflict/mode-tsx.js?url'),
  'ace/mode/turtle': () => import('ace-builds/src-noconflict/mode-turtle.js?url'),
  'ace/mode/twig': () => import('ace-builds/src-noconflict/mode-twig.js?url'),
  'ace/mode/typescript': () => import('ace-builds/src-noconflict/mode-typescript.js?url'),
  'ace/mode/vala': () => import('ace-builds/src-noconflict/mode-vala.js?url'),
  'ace/mode/vbscript': () => import('ace-builds/src-noconflict/mode-vbscript.js?url'),
  'ace/mode/velocity': () => import('ace-builds/src-noconflict/mode-velocity.js?url'),
  'ace/mode/verilog': () => import('ace-builds/src-noconflict/mode-verilog.js?url'),
  'ace/mode/vhdl': () => import('ace-builds/src-noconflict/mode-vhdl.js?url'),
  'ace/mode/visualforce': () => import('ace-builds/src-noconflict/mode-visualforce.js?url'),
  'ace/mode/wollok': () => import('ace-builds/src-noconflict/mode-wollok.js?url'),
  'ace/mode/xml': () => import('ace-builds/src-noconflict/mode-xml.js?url'),
  'ace/mode/xquery': () => import('ace-builds/src-noconflict/mode-xquery.js?url'),
  'ace/mode/yaml': () => import('ace-builds/src-noconflict/mode-yaml.js?url'),
  'ace/mode/zeek': () => import('ace-builds/src-noconflict/mode-zeek.js?url'),
  'ace/theme/ambiance': () => import('ace-builds/src-noconflict/theme-ambiance.js?url'),
  'ace/theme/chaos': () => import('ace-builds/src-noconflict/theme-chaos.js?url'),
  'ace/theme/chrome': () => import('ace-builds/src-noconflict/theme-chrome.js?url'),
  'ace/theme/cloud9_day': () => import('ace-builds/src-noconflict/theme-cloud9_day.js?url'),
  'ace/theme/cloud9_night': () => import('ace-builds/src-noconflict/theme-cloud9_night.js?url'),
  'ace/theme/cloud9_night_low_color': () =>
    import('ace-builds/src-noconflict/theme-cloud9_night_low_color.js?url'),
  'ace/theme/cloud_editor': () => import('ace-builds/src-noconflict/theme-cloud_editor.js?url'),
  'ace/theme/cloud_editor_dark': () =>
    import('ace-builds/src-noconflict/theme-cloud_editor_dark.js?url'),
  'ace/theme/clouds': () => import('ace-builds/src-noconflict/theme-clouds.js?url'),
  'ace/theme/clouds_midnight': () =>
    import('ace-builds/src-noconflict/theme-clouds_midnight.js?url'),
  'ace/theme/cobalt': () => import('ace-builds/src-noconflict/theme-cobalt.js?url'),
  'ace/theme/crimson_editor': () => import('ace-builds/src-noconflict/theme-crimson_editor.js?url'),
  'ace/theme/dawn': () => import('ace-builds/src-noconflict/theme-dawn.js?url'),
  'ace/theme/dracula': () => import('ace-builds/src-noconflict/theme-dracula.js?url'),
  'ace/theme/dreamweaver': () => import('ace-builds/src-noconflict/theme-dreamweaver.js?url'),
  'ace/theme/eclipse': () => import('ace-builds/src-noconflict/theme-eclipse.js?url'),
  'ace/theme/github': () => import('ace-builds/src-noconflict/theme-github.js?url'),
  'ace/theme/github_dark': () => import('ace-builds/src-noconflict/theme-github_dark.js?url'),
  'ace/theme/gob': () => import('ace-builds/src-noconflict/theme-gob.js?url'),
  'ace/theme/gruvbox': () => import('ace-builds/src-noconflict/theme-gruvbox.js?url'),
  'ace/theme/gruvbox_dark_hard': () =>
    import('ace-builds/src-noconflict/theme-gruvbox_dark_hard.js?url'),
  'ace/theme/gruvbox_light_hard': () =>
    import('ace-builds/src-noconflict/theme-gruvbox_light_hard.js?url'),
  'ace/theme/idle_fingers': () => import('ace-builds/src-noconflict/theme-idle_fingers.js?url'),
  'ace/theme/iplastic': () => import('ace-builds/src-noconflict/theme-iplastic.js?url'),
  'ace/theme/katzenmilch': () => import('ace-builds/src-noconflict/theme-katzenmilch.js?url'),
  'ace/theme/kr_theme': () => import('ace-builds/src-noconflict/theme-kr_theme.js?url'),
  'ace/theme/kuroir': () => import('ace-builds/src-noconflict/theme-kuroir.js?url'),
  'ace/theme/merbivore': () => import('ace-builds/src-noconflict/theme-merbivore.js?url'),
  'ace/theme/merbivore_soft': () => import('ace-builds/src-noconflict/theme-merbivore_soft.js?url'),
  'ace/theme/mono_industrial': () =>
    import('ace-builds/src-noconflict/theme-mono_industrial.js?url'),
  'ace/theme/monokai': () => import('ace-builds/src-noconflict/theme-monokai.js?url'),
  'ace/theme/nord_dark': () => import('ace-builds/src-noconflict/theme-nord_dark.js?url'),
  'ace/theme/one_dark': () => import('ace-builds/src-noconflict/theme-one_dark.js?url'),
  'ace/theme/pastel_on_dark': () => import('ace-builds/src-noconflict/theme-pastel_on_dark.js?url'),
  'ace/theme/solarized_dark': () => import('ace-builds/src-noconflict/theme-solarized_dark.js?url'),
  'ace/theme/solarized_light': () =>
    import('ace-builds/src-noconflict/theme-solarized_light.js?url'),
  'ace/theme/sqlserver': () => import('ace-builds/src-noconflict/theme-sqlserver.js?url'),
  'ace/theme/terminal': () => import('ace-builds/src-noconflict/theme-terminal.js?url'),
  'ace/theme/textmate': () => import('ace-builds/src-noconflict/theme-textmate.js?url'),
  'ace/theme/tomorrow': () => import('ace-builds/src-noconflict/theme-tomorrow.js?url'),
  'ace/theme/tomorrow_night': () => import('ace-builds/src-noconflict/theme-tomorrow_night.js?url'),
  'ace/theme/tomorrow_night_blue': () =>
    import('ace-builds/src-noconflict/theme-tomorrow_night_blue.js?url'),
  'ace/theme/tomorrow_night_bright': () =>
    import('ace-builds/src-noconflict/theme-tomorrow_night_bright.js?url'),
  'ace/theme/tomorrow_night_eighties': () =>
    import('ace-builds/src-noconflict/theme-tomorrow_night_eighties.js?url'),
  'ace/theme/twilight': () => import('ace-builds/src-noconflict/theme-twilight.js?url'),
  'ace/theme/vibrant_ink': () => import('ace-builds/src-noconflict/theme-vibrant_ink.js?url'),
  'ace/theme/xcode': () => import('ace-builds/src-noconflict/theme-xcode.js?url'),
  'ace/mode/base_worker': () => import('ace-builds/src-noconflict/worker-base.js?url'),
  'ace/mode/coffee_worker': () => import('ace-builds/src-noconflict/worker-coffee.js?url'),
  'ace/mode/css_worker': () => import('ace-builds/src-noconflict/worker-css.js?url'),
  'ace/mode/html_worker': () => import('ace-builds/src-noconflict/worker-html.js?url'),
  'ace/mode/javascript_worker': () => import('ace-builds/src-noconflict/worker-javascript.js?url'),
  'ace/mode/json_worker': () => import('ace-builds/src-noconflict/worker-json.js?url'),
  'ace/mode/lua_worker': () => import('ace-builds/src-noconflict/worker-lua.js?url'),
  'ace/mode/php_worker': () => import('ace-builds/src-noconflict/worker-php.js?url'),
  'ace/mode/xml_worker': () => import('ace-builds/src-noconflict/worker-xml.js?url'),
  'ace/mode/xquery_worker': () => import('ace-builds/src-noconflict/worker-xquery.js?url'),
  'ace/mode/yaml_worker': () => import('ace-builds/src-noconflict/worker-yaml.js?url'),
  'ace/snippets/abap': () => import('ace-builds/src-noconflict/snippets/abap.js?url'),
  'ace/snippets/abc': () => import('ace-builds/src-noconflict/snippets/abc.js?url'),
  'ace/snippets/actionscript': () =>
    import('ace-builds/src-noconflict/snippets/actionscript.js?url'),
  'ace/snippets/ada': () => import('ace-builds/src-noconflict/snippets/ada.js?url'),
  'ace/snippets/alda': () => import('ace-builds/src-noconflict/snippets/alda.js?url'),
  'ace/snippets/apache_conf': () => import('ace-builds/src-noconflict/snippets/apache_conf.js?url'),
  'ace/snippets/apex': () => import('ace-builds/src-noconflict/snippets/apex.js?url'),
  'ace/snippets/applescript': () => import('ace-builds/src-noconflict/snippets/applescript.js?url'),
  'ace/snippets/aql': () => import('ace-builds/src-noconflict/snippets/aql.js?url'),
  'ace/snippets/asciidoc': () => import('ace-builds/src-noconflict/snippets/asciidoc.js?url'),
  'ace/snippets/asl': () => import('ace-builds/src-noconflict/snippets/asl.js?url'),
  'ace/snippets/assembly_x86': () =>
    import('ace-builds/src-noconflict/snippets/assembly_x86.js?url'),
  'ace/snippets/astro': () => import('ace-builds/src-noconflict/snippets/astro.js?url'),
  'ace/snippets/autohotkey': () => import('ace-builds/src-noconflict/snippets/autohotkey.js?url'),
  'ace/snippets/batchfile': () => import('ace-builds/src-noconflict/snippets/batchfile.js?url'),
  'ace/snippets/bibtex': () => import('ace-builds/src-noconflict/snippets/bibtex.js?url'),
  'ace/snippets/c9search': () => import('ace-builds/src-noconflict/snippets/c9search.js?url'),
  'ace/snippets/c_cpp': () => import('ace-builds/src-noconflict/snippets/c_cpp.js?url'),
  'ace/snippets/cirru': () => import('ace-builds/src-noconflict/snippets/cirru.js?url'),
  'ace/snippets/clojure': () => import('ace-builds/src-noconflict/snippets/clojure.js?url'),
  'ace/snippets/cobol': () => import('ace-builds/src-noconflict/snippets/cobol.js?url'),
  'ace/snippets/coffee': () => import('ace-builds/src-noconflict/snippets/coffee.js?url'),
  'ace/snippets/coldfusion': () => import('ace-builds/src-noconflict/snippets/coldfusion.js?url'),
  'ace/snippets/crystal': () => import('ace-builds/src-noconflict/snippets/crystal.js?url'),
  'ace/snippets/csharp': () => import('ace-builds/src-noconflict/snippets/csharp.js?url'),
  'ace/snippets/csound_document': () =>
    import('ace-builds/src-noconflict/snippets/csound_document.js?url'),
  'ace/snippets/csound_orchestra': () =>
    import('ace-builds/src-noconflict/snippets/csound_orchestra.js?url'),
  'ace/snippets/csound_score': () =>
    import('ace-builds/src-noconflict/snippets/csound_score.js?url'),
  'ace/snippets/csp': () => import('ace-builds/src-noconflict/snippets/csp.js?url'),
  'ace/snippets/css': () => import('ace-builds/src-noconflict/snippets/css.js?url'),
  'ace/snippets/curly': () => import('ace-builds/src-noconflict/snippets/curly.js?url'),
  'ace/snippets/cuttlefish': () => import('ace-builds/src-noconflict/snippets/cuttlefish.js?url'),
  'ace/snippets/d': () => import('ace-builds/src-noconflict/snippets/d.js?url'),
  'ace/snippets/dart': () => import('ace-builds/src-noconflict/snippets/dart.js?url'),
  'ace/snippets/diff': () => import('ace-builds/src-noconflict/snippets/diff.js?url'),
  'ace/snippets/django': () => import('ace-builds/src-noconflict/snippets/django.js?url'),
  'ace/snippets/dockerfile': () => import('ace-builds/src-noconflict/snippets/dockerfile.js?url'),
  'ace/snippets/dot': () => import('ace-builds/src-noconflict/snippets/dot.js?url'),
  'ace/snippets/drools': () => import('ace-builds/src-noconflict/snippets/drools.js?url'),
  'ace/snippets/edifact': () => import('ace-builds/src-noconflict/snippets/edifact.js?url'),
  'ace/snippets/eiffel': () => import('ace-builds/src-noconflict/snippets/eiffel.js?url'),
  'ace/snippets/ejs': () => import('ace-builds/src-noconflict/snippets/ejs.js?url'),
  'ace/snippets/elixir': () => import('ace-builds/src-noconflict/snippets/elixir.js?url'),
  'ace/snippets/elm': () => import('ace-builds/src-noconflict/snippets/elm.js?url'),
  'ace/snippets/erlang': () => import('ace-builds/src-noconflict/snippets/erlang.js?url'),
  'ace/snippets/flix': () => import('ace-builds/src-noconflict/snippets/flix.js?url'),
  'ace/snippets/forth': () => import('ace-builds/src-noconflict/snippets/forth.js?url'),
  'ace/snippets/fortran': () => import('ace-builds/src-noconflict/snippets/fortran.js?url'),
  'ace/snippets/fsharp': () => import('ace-builds/src-noconflict/snippets/fsharp.js?url'),
  'ace/snippets/fsl': () => import('ace-builds/src-noconflict/snippets/fsl.js?url'),
  'ace/snippets/ftl': () => import('ace-builds/src-noconflict/snippets/ftl.js?url'),
  'ace/snippets/gcode': () => import('ace-builds/src-noconflict/snippets/gcode.js?url'),
  'ace/snippets/gherkin': () => import('ace-builds/src-noconflict/snippets/gherkin.js?url'),
  'ace/snippets/gitignore': () => import('ace-builds/src-noconflict/snippets/gitignore.js?url'),
  'ace/snippets/glsl': () => import('ace-builds/src-noconflict/snippets/glsl.js?url'),
  'ace/snippets/gobstones': () => import('ace-builds/src-noconflict/snippets/gobstones.js?url'),
  'ace/snippets/golang': () => import('ace-builds/src-noconflict/snippets/golang.js?url'),
  'ace/snippets/graphqlschema': () =>
    import('ace-builds/src-noconflict/snippets/graphqlschema.js?url'),
  'ace/snippets/groovy': () => import('ace-builds/src-noconflict/snippets/groovy.js?url'),
  'ace/snippets/haml': () => import('ace-builds/src-noconflict/snippets/haml.js?url'),
  'ace/snippets/handlebars': () => import('ace-builds/src-noconflict/snippets/handlebars.js?url'),
  'ace/snippets/haskell': () => import('ace-builds/src-noconflict/snippets/haskell.js?url'),
  'ace/snippets/haskell_cabal': () =>
    import('ace-builds/src-noconflict/snippets/haskell_cabal.js?url'),
  'ace/snippets/haxe': () => import('ace-builds/src-noconflict/snippets/haxe.js?url'),
  'ace/snippets/hjson': () => import('ace-builds/src-noconflict/snippets/hjson.js?url'),
  'ace/snippets/html': () => import('ace-builds/src-noconflict/snippets/html.js?url'),
  'ace/snippets/html_elixir': () => import('ace-builds/src-noconflict/snippets/html_elixir.js?url'),
  'ace/snippets/html_ruby': () => import('ace-builds/src-noconflict/snippets/html_ruby.js?url'),
  'ace/snippets/ini': () => import('ace-builds/src-noconflict/snippets/ini.js?url'),
  'ace/snippets/io': () => import('ace-builds/src-noconflict/snippets/io.js?url'),
  'ace/snippets/ion': () => import('ace-builds/src-noconflict/snippets/ion.js?url'),
  'ace/snippets/jack': () => import('ace-builds/src-noconflict/snippets/jack.js?url'),
  'ace/snippets/jade': () => import('ace-builds/src-noconflict/snippets/jade.js?url'),
  'ace/snippets/java': () => import('ace-builds/src-noconflict/snippets/java.js?url'),
  'ace/snippets/javascript': () => import('ace-builds/src-noconflict/snippets/javascript.js?url'),
  'ace/snippets/jexl': () => import('ace-builds/src-noconflict/snippets/jexl.js?url'),
  'ace/snippets/json': () => import('ace-builds/src-noconflict/snippets/json.js?url'),
  'ace/snippets/json5': () => import('ace-builds/src-noconflict/snippets/json5.js?url'),
  'ace/snippets/jsoniq': () => import('ace-builds/src-noconflict/snippets/jsoniq.js?url'),
  'ace/snippets/jsp': () => import('ace-builds/src-noconflict/snippets/jsp.js?url'),
  'ace/snippets/jssm': () => import('ace-builds/src-noconflict/snippets/jssm.js?url'),
  'ace/snippets/jsx': () => import('ace-builds/src-noconflict/snippets/jsx.js?url'),
  'ace/snippets/julia': () => import('ace-builds/src-noconflict/snippets/julia.js?url'),
  'ace/snippets/kotlin': () => import('ace-builds/src-noconflict/snippets/kotlin.js?url'),
  'ace/snippets/latex': () => import('ace-builds/src-noconflict/snippets/latex.js?url'),
  'ace/snippets/latte': () => import('ace-builds/src-noconflict/snippets/latte.js?url'),
  'ace/snippets/less': () => import('ace-builds/src-noconflict/snippets/less.js?url'),
  'ace/snippets/liquid': () => import('ace-builds/src-noconflict/snippets/liquid.js?url'),
  'ace/snippets/lisp': () => import('ace-builds/src-noconflict/snippets/lisp.js?url'),
  'ace/snippets/livescript': () => import('ace-builds/src-noconflict/snippets/livescript.js?url'),
  'ace/snippets/logiql': () => import('ace-builds/src-noconflict/snippets/logiql.js?url'),
  'ace/snippets/logtalk': () => import('ace-builds/src-noconflict/snippets/logtalk.js?url'),
  'ace/snippets/lsl': () => import('ace-builds/src-noconflict/snippets/lsl.js?url'),
  'ace/snippets/lua': () => import('ace-builds/src-noconflict/snippets/lua.js?url'),
  'ace/snippets/luapage': () => import('ace-builds/src-noconflict/snippets/luapage.js?url'),
  'ace/snippets/lucene': () => import('ace-builds/src-noconflict/snippets/lucene.js?url'),
  'ace/snippets/makefile': () => import('ace-builds/src-noconflict/snippets/makefile.js?url'),
  'ace/snippets/markdown': () => import('ace-builds/src-noconflict/snippets/markdown.js?url'),
  'ace/snippets/mask': () => import('ace-builds/src-noconflict/snippets/mask.js?url'),
  'ace/snippets/matlab': () => import('ace-builds/src-noconflict/snippets/matlab.js?url'),
  'ace/snippets/maze': () => import('ace-builds/src-noconflict/snippets/maze.js?url'),
  'ace/snippets/mediawiki': () => import('ace-builds/src-noconflict/snippets/mediawiki.js?url'),
  'ace/snippets/mel': () => import('ace-builds/src-noconflict/snippets/mel.js?url'),
  'ace/snippets/mips': () => import('ace-builds/src-noconflict/snippets/mips.js?url'),
  'ace/snippets/mixal': () => import('ace-builds/src-noconflict/snippets/mixal.js?url'),
  'ace/snippets/mushcode': () => import('ace-builds/src-noconflict/snippets/mushcode.js?url'),
  'ace/snippets/mysql': () => import('ace-builds/src-noconflict/snippets/mysql.js?url'),
  'ace/snippets/nasal': () => import('ace-builds/src-noconflict/snippets/nasal.js?url'),
  'ace/snippets/nginx': () => import('ace-builds/src-noconflict/snippets/nginx.js?url'),
  'ace/snippets/nim': () => import('ace-builds/src-noconflict/snippets/nim.js?url'),
  'ace/snippets/nix': () => import('ace-builds/src-noconflict/snippets/nix.js?url'),
  'ace/snippets/nsis': () => import('ace-builds/src-noconflict/snippets/nsis.js?url'),
  'ace/snippets/nunjucks': () => import('ace-builds/src-noconflict/snippets/nunjucks.js?url'),
  'ace/snippets/objectivec': () => import('ace-builds/src-noconflict/snippets/objectivec.js?url'),
  'ace/snippets/ocaml': () => import('ace-builds/src-noconflict/snippets/ocaml.js?url'),
  'ace/snippets/odin': () => import('ace-builds/src-noconflict/snippets/odin.js?url'),
  'ace/snippets/partiql': () => import('ace-builds/src-noconflict/snippets/partiql.js?url'),
  'ace/snippets/pascal': () => import('ace-builds/src-noconflict/snippets/pascal.js?url'),
  'ace/snippets/perl': () => import('ace-builds/src-noconflict/snippets/perl.js?url'),
  'ace/snippets/pgsql': () => import('ace-builds/src-noconflict/snippets/pgsql.js?url'),
  'ace/snippets/php': () => import('ace-builds/src-noconflict/snippets/php.js?url'),
  'ace/snippets/php_laravel_blade': () =>
    import('ace-builds/src-noconflict/snippets/php_laravel_blade.js?url'),
  'ace/snippets/pig': () => import('ace-builds/src-noconflict/snippets/pig.js?url'),
  'ace/snippets/plain_text': () => import('ace-builds/src-noconflict/snippets/plain_text.js?url'),
  'ace/snippets/plsql': () => import('ace-builds/src-noconflict/snippets/plsql.js?url'),
  'ace/snippets/powershell': () => import('ace-builds/src-noconflict/snippets/powershell.js?url'),
  'ace/snippets/praat': () => import('ace-builds/src-noconflict/snippets/praat.js?url'),
  'ace/snippets/prisma': () => import('ace-builds/src-noconflict/snippets/prisma.js?url'),
  'ace/snippets/prolog': () => import('ace-builds/src-noconflict/snippets/prolog.js?url'),
  'ace/snippets/properties': () => import('ace-builds/src-noconflict/snippets/properties.js?url'),
  'ace/snippets/protobuf': () => import('ace-builds/src-noconflict/snippets/protobuf.js?url'),
  'ace/snippets/prql': () => import('ace-builds/src-noconflict/snippets/prql.js?url'),
  'ace/snippets/puppet': () => import('ace-builds/src-noconflict/snippets/puppet.js?url'),
  'ace/snippets/python': () => import('ace-builds/src-noconflict/snippets/python.js?url'),
  'ace/snippets/qml': () => import('ace-builds/src-noconflict/snippets/qml.js?url'),
  'ace/snippets/r': () => import('ace-builds/src-noconflict/snippets/r.js?url'),
  'ace/snippets/raku': () => import('ace-builds/src-noconflict/snippets/raku.js?url'),
  'ace/snippets/razor': () => import('ace-builds/src-noconflict/snippets/razor.js?url'),
  'ace/snippets/rdoc': () => import('ace-builds/src-noconflict/snippets/rdoc.js?url'),
  'ace/snippets/red': () => import('ace-builds/src-noconflict/snippets/red.js?url'),
  'ace/snippets/redshift': () => import('ace-builds/src-noconflict/snippets/redshift.js?url'),
  'ace/snippets/rhtml': () => import('ace-builds/src-noconflict/snippets/rhtml.js?url'),
  'ace/snippets/robot': () => import('ace-builds/src-noconflict/snippets/robot.js?url'),
  'ace/snippets/rst': () => import('ace-builds/src-noconflict/snippets/rst.js?url'),
  'ace/snippets/ruby': () => import('ace-builds/src-noconflict/snippets/ruby.js?url'),
  'ace/snippets/rust': () => import('ace-builds/src-noconflict/snippets/rust.js?url'),
  'ace/snippets/sac': () => import('ace-builds/src-noconflict/snippets/sac.js?url'),
  'ace/snippets/sass': () => import('ace-builds/src-noconflict/snippets/sass.js?url'),
  'ace/snippets/scad': () => import('ace-builds/src-noconflict/snippets/scad.js?url'),
  'ace/snippets/scala': () => import('ace-builds/src-noconflict/snippets/scala.js?url'),
  'ace/snippets/scheme': () => import('ace-builds/src-noconflict/snippets/scheme.js?url'),
  'ace/snippets/scrypt': () => import('ace-builds/src-noconflict/snippets/scrypt.js?url'),
  'ace/snippets/scss': () => import('ace-builds/src-noconflict/snippets/scss.js?url'),
  'ace/snippets/sh': () => import('ace-builds/src-noconflict/snippets/sh.js?url'),
  'ace/snippets/sjs': () => import('ace-builds/src-noconflict/snippets/sjs.js?url'),
  'ace/snippets/slim': () => import('ace-builds/src-noconflict/snippets/slim.js?url'),
  'ace/snippets/smarty': () => import('ace-builds/src-noconflict/snippets/smarty.js?url'),
  'ace/snippets/smithy': () => import('ace-builds/src-noconflict/snippets/smithy.js?url'),
  'ace/snippets/snippets': () => import('ace-builds/src-noconflict/snippets/snippets.js?url'),
  'ace/snippets/soy_template': () =>
    import('ace-builds/src-noconflict/snippets/soy_template.js?url'),
  'ace/snippets/space': () => import('ace-builds/src-noconflict/snippets/space.js?url'),
  'ace/snippets/sparql': () => import('ace-builds/src-noconflict/snippets/sparql.js?url'),
  'ace/snippets/sql': () => import('ace-builds/src-noconflict/snippets/sql.js?url'),
  'ace/snippets/sqlserver': () => import('ace-builds/src-noconflict/snippets/sqlserver.js?url'),
  'ace/snippets/stylus': () => import('ace-builds/src-noconflict/snippets/stylus.js?url'),
  'ace/snippets/svg': () => import('ace-builds/src-noconflict/snippets/svg.js?url'),
  'ace/snippets/swift': () => import('ace-builds/src-noconflict/snippets/swift.js?url'),
  'ace/snippets/tcl': () => import('ace-builds/src-noconflict/snippets/tcl.js?url'),
  'ace/snippets/terraform': () => import('ace-builds/src-noconflict/snippets/terraform.js?url'),
  'ace/snippets/tex': () => import('ace-builds/src-noconflict/snippets/tex.js?url'),
  'ace/snippets/text': () => import('ace-builds/src-noconflict/snippets/text.js?url'),
  'ace/snippets/textile': () => import('ace-builds/src-noconflict/snippets/textile.js?url'),
  'ace/snippets/toml': () => import('ace-builds/src-noconflict/snippets/toml.js?url'),
  'ace/snippets/tsx': () => import('ace-builds/src-noconflict/snippets/tsx.js?url'),
  'ace/snippets/turtle': () => import('ace-builds/src-noconflict/snippets/turtle.js?url'),
  'ace/snippets/twig': () => import('ace-builds/src-noconflict/snippets/twig.js?url'),
  'ace/snippets/typescript': () => import('ace-builds/src-noconflict/snippets/typescript.js?url'),
  'ace/snippets/vala': () => import('ace-builds/src-noconflict/snippets/vala.js?url'),
  'ace/snippets/vbscript': () => import('ace-builds/src-noconflict/snippets/vbscript.js?url'),
  'ace/snippets/velocity': () => import('ace-builds/src-noconflict/snippets/velocity.js?url'),
  'ace/snippets/verilog': () => import('ace-builds/src-noconflict/snippets/verilog.js?url'),
  'ace/snippets/vhdl': () => import('ace-builds/src-noconflict/snippets/vhdl.js?url'),
  'ace/snippets/visualforce': () => import('ace-builds/src-noconflict/snippets/visualforce.js?url'),
  'ace/snippets/wollok': () => import('ace-builds/src-noconflict/snippets/wollok.js?url'),
  'ace/snippets/xml': () => import('ace-builds/src-noconflict/snippets/xml.js?url'),
  'ace/snippets/xquery': () => import('ace-builds/src-noconflict/snippets/xquery.js?url'),
  'ace/snippets/yaml': () => import('ace-builds/src-noconflict/snippets/yaml.js?url'),
  'ace/snippets/zeek': () => import('ace-builds/src-noconflict/snippets/zeek.js?url')
}

export default class CodeEditor {
  load(target: HTMLElement) {
    this.loadEditors(target)
  }

  async loadEditors(target: HTMLElement) {
    const codeEditors = target.getElementsByClassName('ems-code-editor')
    for (let i = 0; i < codeEditors.length; i++) {
      const container = codeEditors.item(i)
      if (!container) {
        continue
      }
      let pre: Element | null = container
      if (pre.tagName === 'DIV') {
        pre = container.querySelector('PRE')
      }
      if (!(pre instanceof HTMLElement)) {
        console.warn('PRE tag not found')
        continue
      }

      const inputField = container.querySelector('input')
      const disabled = null === inputField
      const language = container.getAttribute('data-language') || 'ace/mode/twig'
      let theme = container.getAttribute('data-theme') || 'ace/theme/chrome'
      const maxLines = Number.parseInt(container.getAttribute('data-max-lines') || '15')
      const minLines = Number.parseInt(container.getAttribute('data-min-lines') || '1')

      const ace = await import('ace-builds')

      const [languageUrl, themeUrl, keybindingMenuUrl] = await Promise.all([
        this.getModuleUrl(language),
        this.getModuleUrl(theme),
        this.getModuleUrl('ace/ext/keybinding_menu')
      ])

      ace.config.setModuleUrl(language, languageUrl)
      ace.config.setModuleUrl(theme, themeUrl)
      ace.config.setModuleUrl('ace/ext/keybinding_menu', keybindingMenuUrl)

      const editor = ace.edit(pre, {
        mode: language,
        readOnly: disabled,
        maxLines,
        minLines,
        theme
      })

      editor.on('change', function () {
        if (null === inputField) {
          return
        }
        inputField.value = editor.getValue()
        const changeEvent = new ChangeEvent(inputField)
        changeEvent.dispatch()
      })

      editor.commands.addCommands([
        {
          name: 'fullscreen',
          bindKey: { win: 'F11', mac: 'Esc' },
          exec: function (editor) {
            if (pre.classList.contains('panel-fullscreen')) {
              editor.setOption('maxLines', maxLines)
              pre.classList.remove('panel-fullscreen')
              editor.setAutoScrollEditorIntoView(false)
            } else {
              editor.setOption('maxLines', Infinity)
              pre.classList.add('panel-fullscreen')
              editor.setAutoScrollEditorIntoView(true)
            }
            editor.resize()
          }
        },
        {
          name: 'showKeyboardShortcuts',
          bindKey: { win: 'Ctrl-Alt-h', mac: 'Command-Alt-h' },
          exec: function (editor) {
            ace.config.loadModule('ace/ext/keybinding_menu', function (module) {
              module
                .init(editor)(editor as any)
                .showKeyboardShortcuts?.()
            })
          }
        }
      ])
    }
  }

  private async getModuleUrl(moduleName: string): Promise<string> {
    const moduleLoader = modules[moduleName]
    if (!moduleLoader) {
      throw new Error(`Module path not found for ${moduleName}`)
    }

    const module = await moduleLoader()
    return module.default
  }
}
