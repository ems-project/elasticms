// inspired by ace-builds/webpack-resolver.js
import ace from 'ace-builds/src-noconflict/ace'

import extBeautify from 'ace-builds/src-noconflict/ext-beautify.js?url'
import extCode_lens from 'ace-builds/src-noconflict/ext-code_lens.js?url'
import extCommand_bar from 'ace-builds/src-noconflict/ext-command_bar.js?url'
import extElastic_tabstops_lite from 'ace-builds/src-noconflict/ext-elastic_tabstops_lite.js?url'
import extEmmet from 'ace-builds/src-noconflict/ext-emmet.js?url'
import extError_marker from 'ace-builds/src-noconflict/ext-error_marker.js?url'
import extHardwrap from 'ace-builds/src-noconflict/ext-hardwrap.js?url'
import extInline_autocomplete from 'ace-builds/src-noconflict/ext-inline_autocomplete.js?url'
import extKeyboard_menu from 'ace-builds/src-noconflict/ext-keybinding_menu.js?url'
import extLanguage_tools from 'ace-builds/src-noconflict/ext-language_tools.js?url'
import extLinking from 'ace-builds/src-noconflict/ext-linking.js?url'
import extModelist from 'ace-builds/src-noconflict/ext-modelist.js?url'
import extOptions from 'ace-builds/src-noconflict/ext-options.js?url'
import extPrompt from 'ace-builds/src-noconflict/ext-prompt.js?url'
import extRtl from 'ace-builds/src-noconflict/ext-rtl.js?url'
import extSearchbox from 'ace-builds/src-noconflict/ext-searchbox.js?url'
import extSettings_menu from 'ace-builds/src-noconflict/ext-settings_menu.js?url'
import extSimple_tokenizer from 'ace-builds/src-noconflict/ext-simple_tokenizer.js?url'
import extSpellcheck from 'ace-builds/src-noconflict/ext-spellcheck.js?url'
import extSplit from 'ace-builds/src-noconflict/ext-split.js?url'
import extStatic_highlight from 'ace-builds/src-noconflict/ext-static_highlight.js?url'
import extStatusbar from 'ace-builds/src-noconflict/ext-statusbar.js?url'
import extTextarea from 'ace-builds/src-noconflict/ext-textarea.js?url'
import extThemelist from 'ace-builds/src-noconflict/ext-themelist.js?url'
import extWhitespace from 'ace-builds/src-noconflict/ext-whitespace.js?url'
import keyboardEmacs from 'ace-builds/src-noconflict/keybinding-emacs.js?url'
import keyboardSublime from 'ace-builds/src-noconflict/keybinding-sublime.js?url'
import keyboardVim from 'ace-builds/src-noconflict/keybinding-vim.js?url'
import keyboardVscode from 'ace-builds/src-noconflict/keybinding-vscode.js?url'
import modeAbap from 'ace-builds/src-noconflict/mode-abap.js?url'
import modeAbc from 'ace-builds/src-noconflict/mode-abc.js?url'
import modeActionscript from 'ace-builds/src-noconflict/mode-actionscript.js?url'
import modeAda from 'ace-builds/src-noconflict/mode-ada.js?url'
import modeAlda from 'ace-builds/src-noconflict/mode-alda.js?url'
import modeApache_conf from 'ace-builds/src-noconflict/mode-apache_conf.js?url'
import modeApex from 'ace-builds/src-noconflict/mode-apex.js?url'
import modeApplescript from 'ace-builds/src-noconflict/mode-applescript.js?url'
import modeAql from 'ace-builds/src-noconflict/mode-aql.js?url'
import modeAsciidoc from 'ace-builds/src-noconflict/mode-asciidoc.js?url'
import modeAsl from 'ace-builds/src-noconflict/mode-asl.js?url'
import modeAssembly_x86 from 'ace-builds/src-noconflict/mode-assembly_x86.js?url'
import modeAstro from 'ace-builds/src-noconflict/mode-astro.js?url'
import modeAutohotkey from 'ace-builds/src-noconflict/mode-autohotkey.js?url'
import modeBatchfile from 'ace-builds/src-noconflict/mode-batchfile.js?url'
import modeBibtex from 'ace-builds/src-noconflict/mode-bibtex.js?url'
import modeC9search from 'ace-builds/src-noconflict/mode-c9search.js?url'
import modeC_cpp from 'ace-builds/src-noconflict/mode-c_cpp.js?url'
import modeCirru from 'ace-builds/src-noconflict/mode-cirru.js?url'
import modeClojure from 'ace-builds/src-noconflict/mode-clojure.js?url'
import modeCobol from 'ace-builds/src-noconflict/mode-cobol.js?url'
import modeCoffee from 'ace-builds/src-noconflict/mode-coffee.js?url'
import modeColdfusion from 'ace-builds/src-noconflict/mode-coldfusion.js?url'
import modeCrystal from 'ace-builds/src-noconflict/mode-crystal.js?url'
import modeCsharp from 'ace-builds/src-noconflict/mode-csharp.js?url'
import modeCsound_document from 'ace-builds/src-noconflict/mode-csound_document.js?url'
import modeCsound_orchestra from 'ace-builds/src-noconflict/mode-csound_orchestra.js?url'
import modeCsound_score from 'ace-builds/src-noconflict/mode-csound_score.js?url'
import modeCsp from 'ace-builds/src-noconflict/mode-csp.js?url'
import modeCss from 'ace-builds/src-noconflict/mode-css.js?url'
import modeCurly from 'ace-builds/src-noconflict/mode-curly.js?url'
import modeCuttlefish from 'ace-builds/src-noconflict/mode-cuttlefish.js?url'
import modeD from 'ace-builds/src-noconflict/mode-d.js?url'
import modeDart from 'ace-builds/src-noconflict/mode-dart.js?url'
import modeDiff from 'ace-builds/src-noconflict/mode-diff.js?url'
import modeDjango from 'ace-builds/src-noconflict/mode-django.js?url'
import modeDockerfile from 'ace-builds/src-noconflict/mode-dockerfile.js?url'
import modeDot from 'ace-builds/src-noconflict/mode-dot.js?url'
import modeDrools from 'ace-builds/src-noconflict/mode-drools.js?url'
import modeEdifact from 'ace-builds/src-noconflict/mode-edifact.js?url'
import modeEiffel from 'ace-builds/src-noconflict/mode-eiffel.js?url'
import modeEjs from 'ace-builds/src-noconflict/mode-ejs.js?url'
import modeElixir from 'ace-builds/src-noconflict/mode-elixir.js?url'
import modeElm from 'ace-builds/src-noconflict/mode-elm.js?url'
import modeErlang from 'ace-builds/src-noconflict/mode-erlang.js?url'
import modeFlix from 'ace-builds/src-noconflict/mode-flix.js?url'
import modeForth from 'ace-builds/src-noconflict/mode-forth.js?url'
import modeFortran from 'ace-builds/src-noconflict/mode-fortran.js?url'
import modeFsharp from 'ace-builds/src-noconflict/mode-fsharp.js?url'
import modeFsl from 'ace-builds/src-noconflict/mode-fsl.js?url'
import modeFtl from 'ace-builds/src-noconflict/mode-ftl.js?url'
import modeGcode from 'ace-builds/src-noconflict/mode-gcode.js?url'
import modeGherkin from 'ace-builds/src-noconflict/mode-gherkin.js?url'
import modeGitignore from 'ace-builds/src-noconflict/mode-gitignore.js?url'
import modeGlsl from 'ace-builds/src-noconflict/mode-glsl.js?url'
import modeGobstones from 'ace-builds/src-noconflict/mode-gobstones.js?url'
import modeGolang from 'ace-builds/src-noconflict/mode-golang.js?url'
import modeGraphqlschema from 'ace-builds/src-noconflict/mode-graphqlschema.js?url'
import modeGroovy from 'ace-builds/src-noconflict/mode-groovy.js?url'
import modeHaml from 'ace-builds/src-noconflict/mode-haml.js?url'
import modeHandlebars from 'ace-builds/src-noconflict/mode-handlebars.js?url'
import modeHaskell from 'ace-builds/src-noconflict/mode-haskell.js?url'
import modeHaskell_cabal from 'ace-builds/src-noconflict/mode-haskell_cabal.js?url'
import modeHaxe from 'ace-builds/src-noconflict/mode-haxe.js?url'
import modeHjson from 'ace-builds/src-noconflict/mode-hjson.js?url'
import modeHtml from 'ace-builds/src-noconflict/mode-html.js?url'
import modeHtml_elixir from 'ace-builds/src-noconflict/mode-html_elixir.js?url'
import modeHtml_ruby from 'ace-builds/src-noconflict/mode-html_ruby.js?url'
import modeIni from 'ace-builds/src-noconflict/mode-ini.js?url'
import modeIo from 'ace-builds/src-noconflict/mode-io.js?url'
import modeIon from 'ace-builds/src-noconflict/mode-ion.js?url'
import modeJack from 'ace-builds/src-noconflict/mode-jack.js?url'
import modeJade from 'ace-builds/src-noconflict/mode-jade.js?url'
import modeJava from 'ace-builds/src-noconflict/mode-java.js?url'
import modeJavascript from 'ace-builds/src-noconflict/mode-javascript.js?url'
import modeJexl from 'ace-builds/src-noconflict/mode-jexl.js?url'
import modeJson from 'ace-builds/src-noconflict/mode-json.js?url'
import modeJson5 from 'ace-builds/src-noconflict/mode-json5.js?url'
import modeJsoniq from 'ace-builds/src-noconflict/mode-jsoniq.js?url'
import modeJsp from 'ace-builds/src-noconflict/mode-jsp.js?url'
import modeJssm from 'ace-builds/src-noconflict/mode-jssm.js?url'
import modeJsx from 'ace-builds/src-noconflict/mode-jsx.js?url'
import modeJulia from 'ace-builds/src-noconflict/mode-julia.js?url'
import modeKotlin from 'ace-builds/src-noconflict/mode-kotlin.js?url'
import modeLatex from 'ace-builds/src-noconflict/mode-latex.js?url'
import modeLatte from 'ace-builds/src-noconflict/mode-latte.js?url'
import modeLess from 'ace-builds/src-noconflict/mode-less.js?url'
import modeLiquid from 'ace-builds/src-noconflict/mode-liquid.js?url'
import modeLisp from 'ace-builds/src-noconflict/mode-lisp.js?url'
import modeLivescript from 'ace-builds/src-noconflict/mode-livescript.js?url'
import modeLogiql from 'ace-builds/src-noconflict/mode-logiql.js?url'
import modeLogtalk from 'ace-builds/src-noconflict/mode-logtalk.js?url'
import modeLsl from 'ace-builds/src-noconflict/mode-lsl.js?url'
import modeLua from 'ace-builds/src-noconflict/mode-lua.js?url'
import modeLuapage from 'ace-builds/src-noconflict/mode-luapage.js?url'
import modeLucene from 'ace-builds/src-noconflict/mode-lucene.js?url'
import modeMakefile from 'ace-builds/src-noconflict/mode-makefile.js?url'
import modeMarkdown from 'ace-builds/src-noconflict/mode-markdown.js?url'
import modeMask from 'ace-builds/src-noconflict/mode-mask.js?url'
import modeMatlab from 'ace-builds/src-noconflict/mode-matlab.js?url'
import modeMaze from 'ace-builds/src-noconflict/mode-maze.js?url'
import modeMediawiki from 'ace-builds/src-noconflict/mode-mediawiki.js?url'
import modeMel from 'ace-builds/src-noconflict/mode-mel.js?url'
import modeMips from 'ace-builds/src-noconflict/mode-mips.js?url'
import modeMixal from 'ace-builds/src-noconflict/mode-mixal.js?url'
import modeMushcode from 'ace-builds/src-noconflict/mode-mushcode.js?url'
import modeMysql from 'ace-builds/src-noconflict/mode-mysql.js?url'
import modeNasal from 'ace-builds/src-noconflict/mode-nasal.js?url'
import modeNginx from 'ace-builds/src-noconflict/mode-nginx.js?url'
import modeNim from 'ace-builds/src-noconflict/mode-nim.js?url'
import modeNix from 'ace-builds/src-noconflict/mode-nix.js?url'
import modeNsis from 'ace-builds/src-noconflict/mode-nsis.js?url'
import modeNunjucks from 'ace-builds/src-noconflict/mode-nunjucks.js?url'
import modeObjectivec from 'ace-builds/src-noconflict/mode-objectivec.js?url'
import modeOcaml from 'ace-builds/src-noconflict/mode-ocaml.js?url'
import modeOdin from 'ace-builds/src-noconflict/mode-odin.js?url'
import modePartiql from 'ace-builds/src-noconflict/mode-partiql.js?url'
import modePascal from 'ace-builds/src-noconflict/mode-pascal.js?url'
import modePerl from 'ace-builds/src-noconflict/mode-perl.js?url'
import modePgsql from 'ace-builds/src-noconflict/mode-pgsql.js?url'
import modePhp from 'ace-builds/src-noconflict/mode-php.js?url'
import modePhp_laravel_blade from 'ace-builds/src-noconflict/mode-php_laravel_blade.js?url'
import modePig from 'ace-builds/src-noconflict/mode-pig.js?url'
import modePlain_text from 'ace-builds/src-noconflict/mode-plain_text.js?url'
import modePlsql from 'ace-builds/src-noconflict/mode-plsql.js?url'
import modePowershell from 'ace-builds/src-noconflict/mode-powershell.js?url'
import modePraat from 'ace-builds/src-noconflict/mode-praat.js?url'
import modePrisma from 'ace-builds/src-noconflict/mode-prisma.js?url'
import modeProlog from 'ace-builds/src-noconflict/mode-prolog.js?url'
import modeProperties from 'ace-builds/src-noconflict/mode-properties.js?url'
import modeProtobuf from 'ace-builds/src-noconflict/mode-protobuf.js?url'
import modePrql from 'ace-builds/src-noconflict/mode-prql.js?url'
import modePuppet from 'ace-builds/src-noconflict/mode-puppet.js?url'
import modePython from 'ace-builds/src-noconflict/mode-python.js?url'
import modeQml from 'ace-builds/src-noconflict/mode-qml.js?url'
import modeR from 'ace-builds/src-noconflict/mode-r.js?url'
import modeRaku from 'ace-builds/src-noconflict/mode-raku.js?url'
import modeRazor from 'ace-builds/src-noconflict/mode-razor.js?url'
import modeRdoc from 'ace-builds/src-noconflict/mode-rdoc.js?url'
import modeRed from 'ace-builds/src-noconflict/mode-red.js?url'
import modeRedshift from 'ace-builds/src-noconflict/mode-redshift.js?url'
import modeRhtml from 'ace-builds/src-noconflict/mode-rhtml.js?url'
import modeRobot from 'ace-builds/src-noconflict/mode-robot.js?url'
import modeRst from 'ace-builds/src-noconflict/mode-rst.js?url'
import modeRuby from 'ace-builds/src-noconflict/mode-ruby.js?url'
import modeRust from 'ace-builds/src-noconflict/mode-rust.js?url'
import modeSac from 'ace-builds/src-noconflict/mode-sac.js?url'
import modeSass from 'ace-builds/src-noconflict/mode-sass.js?url'
import modeScad from 'ace-builds/src-noconflict/mode-scad.js?url'
import modeScala from 'ace-builds/src-noconflict/mode-scala.js?url'
import modeScheme from 'ace-builds/src-noconflict/mode-scheme.js?url'
import modeScrypt from 'ace-builds/src-noconflict/mode-scrypt.js?url'
import modeScss from 'ace-builds/src-noconflict/mode-scss.js?url'
import modeSh from 'ace-builds/src-noconflict/mode-sh.js?url'
import modeSjs from 'ace-builds/src-noconflict/mode-sjs.js?url'
import modeSlim from 'ace-builds/src-noconflict/mode-slim.js?url'
import modeSmarty from 'ace-builds/src-noconflict/mode-smarty.js?url'
import modeSmithy from 'ace-builds/src-noconflict/mode-smithy.js?url'
import modeSnippets from 'ace-builds/src-noconflict/mode-snippets.js?url'
import modeSoy_template from 'ace-builds/src-noconflict/mode-soy_template.js?url'
import modeSpace from 'ace-builds/src-noconflict/mode-space.js?url'
import modeSparql from 'ace-builds/src-noconflict/mode-sparql.js?url'
import modeSql from 'ace-builds/src-noconflict/mode-sql.js?url'
import modeSqlserver from 'ace-builds/src-noconflict/mode-sqlserver.js?url'
import modeStylus from 'ace-builds/src-noconflict/mode-stylus.js?url'
import modeSvg from 'ace-builds/src-noconflict/mode-svg.js?url'
import modeSwift from 'ace-builds/src-noconflict/mode-swift.js?url'
import modeTcl from 'ace-builds/src-noconflict/mode-tcl.js?url'
import modeTerraform from 'ace-builds/src-noconflict/mode-terraform.js?url'
import modeTex from 'ace-builds/src-noconflict/mode-tex.js?url'
import modeText from 'ace-builds/src-noconflict/mode-text.js?url'
import modeTextile from 'ace-builds/src-noconflict/mode-textile.js?url'
import modeToml from 'ace-builds/src-noconflict/mode-toml.js?url'
import modeTsx from 'ace-builds/src-noconflict/mode-tsx.js?url'
import modeTurtle from 'ace-builds/src-noconflict/mode-turtle.js?url'
import modeTwig from 'ace-builds/src-noconflict/mode-twig.js?url'
import modeTypescript from 'ace-builds/src-noconflict/mode-typescript.js?url'
import modeVala from 'ace-builds/src-noconflict/mode-vala.js?url'
import modeVbscript from 'ace-builds/src-noconflict/mode-vbscript.js?url'
import modeVelocity from 'ace-builds/src-noconflict/mode-velocity.js?url'
import modeVerilog from 'ace-builds/src-noconflict/mode-verilog.js?url'
import modeVhdl from 'ace-builds/src-noconflict/mode-vhdl.js?url'
import modeVisualforce from 'ace-builds/src-noconflict/mode-visualforce.js?url'
import modeWollok from 'ace-builds/src-noconflict/mode-wollok.js?url'
import modeXml from 'ace-builds/src-noconflict/mode-xml.js?url'
import modeXquery from 'ace-builds/src-noconflict/mode-xquery.js?url'
import modeYaml from 'ace-builds/src-noconflict/mode-yaml.js?url'
import modeZeek from 'ace-builds/src-noconflict/mode-zeek.js?url'

import themeAmbiance from 'ace-builds/src-noconflict/theme-ambiance.js?url'
import themeChaos from 'ace-builds/src-noconflict/theme-chaos.js?url'
import themeChrome from 'ace-builds/src-noconflict/theme-chrome.js?url'
import themeCloud9_day from 'ace-builds/src-noconflict/theme-cloud9_day.js?url'
import themeCloud9_night from 'ace-builds/src-noconflict/theme-cloud9_night.js?url'
import themeCloud9_night_low_color from 'ace-builds/src-noconflict/theme-cloud9_night_low_color.js?url'
import themeCloud_editor from 'ace-builds/src-noconflict/theme-cloud_editor.js?url'
import themeCloud_editor_dark from 'ace-builds/src-noconflict/theme-cloud_editor_dark.js?url'
import themeClouds from 'ace-builds/src-noconflict/theme-clouds.js?url'
import themeClouds_midnight from 'ace-builds/src-noconflict/theme-clouds_midnight.js?url'
import themeCobalt from 'ace-builds/src-noconflict/theme-cobalt.js?url'
import themeCrimson_editor from 'ace-builds/src-noconflict/theme-crimson_editor.js?url'
import themeDawn from 'ace-builds/src-noconflict/theme-dawn.js?url'
import themeDracula from 'ace-builds/src-noconflict/theme-dracula.js?url'
import themeDreamweaver from 'ace-builds/src-noconflict/theme-dreamweaver.js?url'
import themeEclipse from 'ace-builds/src-noconflict/theme-eclipse.js?url'
import themeGithub from 'ace-builds/src-noconflict/theme-github.js?url'
import themeGithub_dark from 'ace-builds/src-noconflict/theme-github_dark.js?url'
import themeGob from 'ace-builds/src-noconflict/theme-gob.js?url'
import themeGruvbox from 'ace-builds/src-noconflict/theme-gruvbox.js?url'
import themeGruvbox_dark_hard from 'ace-builds/src-noconflict/theme-gruvbox_dark_hard.js?url'
import themeGruvbox_light_hard from 'ace-builds/src-noconflict/theme-gruvbox_light_hard.js?url'
import themeIdle_fingers from 'ace-builds/src-noconflict/theme-idle_fingers.js?url'
import themeIplastic from 'ace-builds/src-noconflict/theme-iplastic.js?url'
import themeKatzenmilch from 'ace-builds/src-noconflict/theme-katzenmilch.js?url'
import themeKr_theme from 'ace-builds/src-noconflict/theme-kr_theme.js?url'
import themeKuroir from 'ace-builds/src-noconflict/theme-kuroir.js?url'
import themeMerbivore from 'ace-builds/src-noconflict/theme-merbivore.js?url'
import themeMerbivore_soft from 'ace-builds/src-noconflict/theme-merbivore_soft.js?url'
import themeMono_industrial from 'ace-builds/src-noconflict/theme-mono_industrial.js?url'
import themeMonokai from 'ace-builds/src-noconflict/theme-monokai.js?url'
import themeNord_dark from 'ace-builds/src-noconflict/theme-nord_dark.js?url'
import themeOne_dark from 'ace-builds/src-noconflict/theme-one_dark.js?url'
import themePastel_on_dark from 'ace-builds/src-noconflict/theme-pastel_on_dark.js?url'
import themeSolarized_dark from 'ace-builds/src-noconflict/theme-solarized_dark.js?url'
import themeSolarized_light from 'ace-builds/src-noconflict/theme-solarized_light.js?url'
import themeSqlserver from 'ace-builds/src-noconflict/theme-sqlserver.js?url'
import themeTerminal from 'ace-builds/src-noconflict/theme-terminal.js?url'
import themeTextmate from 'ace-builds/src-noconflict/theme-textmate.js?url'
import themeTomorrow from 'ace-builds/src-noconflict/theme-tomorrow.js?url'
import themeTomorrow_night from 'ace-builds/src-noconflict/theme-tomorrow_night.js?url'
import themeTomorrow_night_blue from 'ace-builds/src-noconflict/theme-tomorrow_night_blue.js?url'
import themeTomorrow_night_bright from 'ace-builds/src-noconflict/theme-tomorrow_night_bright.js?url'
import themeTomorrow_night_eighties from 'ace-builds/src-noconflict/theme-tomorrow_night_eighties.js?url'
import themeTwilight from 'ace-builds/src-noconflict/theme-twilight.js?url'
import themeVibrant_ink from 'ace-builds/src-noconflict/theme-vibrant_ink.js?url'
import themeXcode from 'ace-builds/src-noconflict/theme-xcode.js?url'
import modeBase_worker from 'ace-builds/src-noconflict/worker-base.js?url'
import modeCoffee_worker from 'ace-builds/src-noconflict/worker-coffee.js?url'
import modeCss_worker from 'ace-builds/src-noconflict/worker-css.js?url'
import modeHtml_worker from 'ace-builds/src-noconflict/worker-html.js?url'
import modeJavascript_worker from 'ace-builds/src-noconflict/worker-javascript.js?url'
import modeJson_worker from 'ace-builds/src-noconflict/worker-json.js?url'
import modeLua_worker from 'ace-builds/src-noconflict/worker-lua.js?url'
import modePhp_worker from 'ace-builds/src-noconflict/worker-php.js?url'
import modeXml_worker from 'ace-builds/src-noconflict/worker-xml.js?url'
import modeXquery_worker from 'ace-builds/src-noconflict/worker-xquery.js?url'
import modeYaml_worker from 'ace-builds/src-noconflict/worker-yaml.js?url'
import snippetsAbap from 'ace-builds/src-noconflict/snippets/abap.js?url'
import snippetsAbc from 'ace-builds/src-noconflict/snippets/abc.js?url'
import snippetsActionscript from 'ace-builds/src-noconflict/snippets/actionscript.js?url'
import snippetsAda from 'ace-builds/src-noconflict/snippets/ada.js?url'
import snippetsAlda from 'ace-builds/src-noconflict/snippets/alda.js?url'
import snippetsApache_conf from 'ace-builds/src-noconflict/snippets/apache_conf.js?url'
import snippetsApex from 'ace-builds/src-noconflict/snippets/apex.js?url'
import snippetsApplescript from 'ace-builds/src-noconflict/snippets/applescript.js?url'
import snippetsAql from 'ace-builds/src-noconflict/snippets/aql.js?url'
import snippetsAsciidoc from 'ace-builds/src-noconflict/snippets/asciidoc.js?url'
import snippetsAsl from 'ace-builds/src-noconflict/snippets/asl.js?url'
import snippetsAssembly_x86 from 'ace-builds/src-noconflict/snippets/assembly_x86.js?url'
import snippetsAstro from 'ace-builds/src-noconflict/snippets/astro.js?url'
import snippetsAutohotkey from 'ace-builds/src-noconflict/snippets/autohotkey.js?url'
import snippetsBatchfile from 'ace-builds/src-noconflict/snippets/batchfile.js?url'
import snippetsBibtex from 'ace-builds/src-noconflict/snippets/bibtex.js?url'
import snippetsC9search from 'ace-builds/src-noconflict/snippets/c9search.js?url'
import snippetsC_cpp from 'ace-builds/src-noconflict/snippets/c_cpp.js?url'
import snippetsCirru from 'ace-builds/src-noconflict/snippets/cirru.js?url'
import snippetsClojure from 'ace-builds/src-noconflict/snippets/clojure.js?url'
import snippetsCobol from 'ace-builds/src-noconflict/snippets/cobol.js?url'
import snippetsCoffee from 'ace-builds/src-noconflict/snippets/coffee.js?url'
import snippetsColdfusion from 'ace-builds/src-noconflict/snippets/coldfusion.js?url'
import snippetsCrystal from 'ace-builds/src-noconflict/snippets/crystal.js?url'
import snippetsCsharp from 'ace-builds/src-noconflict/snippets/csharp.js?url'
import snippetsCsound_document from 'ace-builds/src-noconflict/snippets/csound_document.js?url'
import snippetsCsound_orchestra from 'ace-builds/src-noconflict/snippets/csound_orchestra.js?url'
import snippetsCsound_score from 'ace-builds/src-noconflict/snippets/csound_score.js?url'
import snippetsCsp from 'ace-builds/src-noconflict/snippets/csp.js?url'
import snippetsCss from 'ace-builds/src-noconflict/snippets/css.js?url'
import snippetsCurly from 'ace-builds/src-noconflict/snippets/curly.js?url'
import snippetsCuttlefish from 'ace-builds/src-noconflict/snippets/cuttlefish.js?url'
import snippetsD from 'ace-builds/src-noconflict/snippets/d.js?url'
import snippetsDart from 'ace-builds/src-noconflict/snippets/dart.js?url'
import snippetsDiff from 'ace-builds/src-noconflict/snippets/diff.js?url'
import snippetsDjango from 'ace-builds/src-noconflict/snippets/django.js?url'
import snippetsDockerfile from 'ace-builds/src-noconflict/snippets/dockerfile.js?url'
import snippetsDot from 'ace-builds/src-noconflict/snippets/dot.js?url'
import snippetsDrools from 'ace-builds/src-noconflict/snippets/drools.js?url'
import snippetsEdifact from 'ace-builds/src-noconflict/snippets/edifact.js?url'
import snippetsEiffel from 'ace-builds/src-noconflict/snippets/eiffel.js?url'
import snippetsEjs from 'ace-builds/src-noconflict/snippets/ejs.js?url'
import snippetsElixir from 'ace-builds/src-noconflict/snippets/elixir.js?url'
import snippetsElm from 'ace-builds/src-noconflict/snippets/elm.js?url'
import snippetsErlang from 'ace-builds/src-noconflict/snippets/erlang.js?url'
import snippetsFlix from 'ace-builds/src-noconflict/snippets/flix.js?url'
import snippetsForth from 'ace-builds/src-noconflict/snippets/forth.js?url'
import snippetsFortran from 'ace-builds/src-noconflict/snippets/fortran.js?url'
import snippetsFsharp from 'ace-builds/src-noconflict/snippets/fsharp.js?url'
import snippetsFsl from 'ace-builds/src-noconflict/snippets/fsl.js?url'
import snippetsFtl from 'ace-builds/src-noconflict/snippets/ftl.js?url'
import snippetsGcode from 'ace-builds/src-noconflict/snippets/gcode.js?url'
import snippetsGherkin from 'ace-builds/src-noconflict/snippets/gherkin.js?url'
import snippetsGitignore from 'ace-builds/src-noconflict/snippets/gitignore.js?url'
import snippetsGlsl from 'ace-builds/src-noconflict/snippets/glsl.js?url'
import snippetsGobstones from 'ace-builds/src-noconflict/snippets/gobstones.js?url'
import snippetsGolang from 'ace-builds/src-noconflict/snippets/golang.js?url'
import snippetsGraphqlschema from 'ace-builds/src-noconflict/snippets/graphqlschema.js?url'
import snippetsGroovy from 'ace-builds/src-noconflict/snippets/groovy.js?url'
import snippetsHaml from 'ace-builds/src-noconflict/snippets/haml.js?url'
import snippetsHandlebars from 'ace-builds/src-noconflict/snippets/handlebars.js?url'
import snippetsHaskell from 'ace-builds/src-noconflict/snippets/haskell.js?url'
import snippetsHaskell_cabal from 'ace-builds/src-noconflict/snippets/haskell_cabal.js?url'
import snippetsHaxe from 'ace-builds/src-noconflict/snippets/haxe.js?url'
import snippetsHjson from 'ace-builds/src-noconflict/snippets/hjson.js?url'
import snippetsHtml from 'ace-builds/src-noconflict/snippets/html.js?url'
import snippetsHtml_elixir from 'ace-builds/src-noconflict/snippets/html_elixir.js?url'
import snippetsHtml_ruby from 'ace-builds/src-noconflict/snippets/html_ruby.js?url'
import snippetsIni from 'ace-builds/src-noconflict/snippets/ini.js?url'
import snippetsIo from 'ace-builds/src-noconflict/snippets/io.js?url'
import snippetsIon from 'ace-builds/src-noconflict/snippets/ion.js?url'
import snippetsJack from 'ace-builds/src-noconflict/snippets/jack.js?url'
import snippetsJade from 'ace-builds/src-noconflict/snippets/jade.js?url'
import snippetsJava from 'ace-builds/src-noconflict/snippets/java.js?url'
import snippetsJavascript from 'ace-builds/src-noconflict/snippets/javascript.js?url'
import snippetsJexl from 'ace-builds/src-noconflict/snippets/jexl.js?url'
import snippetsJson from 'ace-builds/src-noconflict/snippets/json.js?url'
import snippetsJson5 from 'ace-builds/src-noconflict/snippets/json5.js?url'
import snippetsJsoniq from 'ace-builds/src-noconflict/snippets/jsoniq.js?url'
import snippetsJsp from 'ace-builds/src-noconflict/snippets/jsp.js?url'
import snippetsJssm from 'ace-builds/src-noconflict/snippets/jssm.js?url'
import snippetsJsx from 'ace-builds/src-noconflict/snippets/jsx.js?url'
import snippetsJulia from 'ace-builds/src-noconflict/snippets/julia.js?url'
import snippetsKotlin from 'ace-builds/src-noconflict/snippets/kotlin.js?url'
import snippetsLatex from 'ace-builds/src-noconflict/snippets/latex.js?url'
import snippetsLatte from 'ace-builds/src-noconflict/snippets/latte.js?url'
import snippetsLess from 'ace-builds/src-noconflict/snippets/less.js?url'
import snippetsLiquid from 'ace-builds/src-noconflict/snippets/liquid.js?url'
import snippetsLisp from 'ace-builds/src-noconflict/snippets/lisp.js?url'
import snippetsLivescript from 'ace-builds/src-noconflict/snippets/livescript.js?url'
import snippetsLogiql from 'ace-builds/src-noconflict/snippets/logiql.js?url'
import snippetsLogtalk from 'ace-builds/src-noconflict/snippets/logtalk.js?url'
import snippetsLsl from 'ace-builds/src-noconflict/snippets/lsl.js?url'
import snippetsLua from 'ace-builds/src-noconflict/snippets/lua.js?url'
import snippetsLuapage from 'ace-builds/src-noconflict/snippets/luapage.js?url'
import snippetsLucene from 'ace-builds/src-noconflict/snippets/lucene.js?url'
import snippetsMakefile from 'ace-builds/src-noconflict/snippets/makefile.js?url'
import snippetsMarkdown from 'ace-builds/src-noconflict/snippets/markdown.js?url'
import snippetsMask from 'ace-builds/src-noconflict/snippets/mask.js?url'
import snippetsMatlab from 'ace-builds/src-noconflict/snippets/matlab.js?url'
import snippetsMaze from 'ace-builds/src-noconflict/snippets/maze.js?url'
import snippetsMediawiki from 'ace-builds/src-noconflict/snippets/mediawiki.js?url'
import snippetsMel from 'ace-builds/src-noconflict/snippets/mel.js?url'
import snippetsMips from 'ace-builds/src-noconflict/snippets/mips.js?url'
import snippetsMixal from 'ace-builds/src-noconflict/snippets/mixal.js?url'
import snippetsMushcode from 'ace-builds/src-noconflict/snippets/mushcode.js?url'
import snippetsMysql from 'ace-builds/src-noconflict/snippets/mysql.js?url'
import snippetsNasal from 'ace-builds/src-noconflict/snippets/nasal.js?url'
import snippetsNginx from 'ace-builds/src-noconflict/snippets/nginx.js?url'
import snippetsNim from 'ace-builds/src-noconflict/snippets/nim.js?url'
import snippetsNix from 'ace-builds/src-noconflict/snippets/nix.js?url'
import snippetsNsis from 'ace-builds/src-noconflict/snippets/nsis.js?url'
import snippetsNunjucks from 'ace-builds/src-noconflict/snippets/nunjucks.js?url'
import snippetsObjectivec from 'ace-builds/src-noconflict/snippets/objectivec.js?url'
import snippetsOcaml from 'ace-builds/src-noconflict/snippets/ocaml.js?url'
import snippetsOdin from 'ace-builds/src-noconflict/snippets/odin.js?url'
import snippetsPartiql from 'ace-builds/src-noconflict/snippets/partiql.js?url'
import snippetsPascal from 'ace-builds/src-noconflict/snippets/pascal.js?url'
import snippetsPerl from 'ace-builds/src-noconflict/snippets/perl.js?url'
import snippetsPgsql from 'ace-builds/src-noconflict/snippets/pgsql.js?url'
import snippetsPhp from 'ace-builds/src-noconflict/snippets/php.js?url'
import snippetsPhp_laravel_blade from 'ace-builds/src-noconflict/snippets/php_laravel_blade.js?url'
import snippetsPig from 'ace-builds/src-noconflict/snippets/pig.js?url'
import snippetsPlain_text from 'ace-builds/src-noconflict/snippets/plain_text.js?url'
import snippetsPlsql from 'ace-builds/src-noconflict/snippets/plsql.js?url'
import snippetsPowershell from 'ace-builds/src-noconflict/snippets/powershell.js?url'
import snippetsPraat from 'ace-builds/src-noconflict/snippets/praat.js?url'
import snippetsPrisma from 'ace-builds/src-noconflict/snippets/prisma.js?url'
import snippetsProlog from 'ace-builds/src-noconflict/snippets/prolog.js?url'
import snippetsProperties from 'ace-builds/src-noconflict/snippets/properties.js?url'
import snippetsProtobuf from 'ace-builds/src-noconflict/snippets/protobuf.js?url'
import snippetsPrql from 'ace-builds/src-noconflict/snippets/prql.js?url'
import snippetsPuppet from 'ace-builds/src-noconflict/snippets/puppet.js?url'
import snippetsPython from 'ace-builds/src-noconflict/snippets/python.js?url'
import snippetsQml from 'ace-builds/src-noconflict/snippets/qml.js?url'
import snippetsR from 'ace-builds/src-noconflict/snippets/r.js?url'
import snippetsRaku from 'ace-builds/src-noconflict/snippets/raku.js?url'
import snippetsRazor from 'ace-builds/src-noconflict/snippets/razor.js?url'
import snippetsRdoc from 'ace-builds/src-noconflict/snippets/rdoc.js?url'
import snippetsRed from 'ace-builds/src-noconflict/snippets/red.js?url'
import snippetsRedshift from 'ace-builds/src-noconflict/snippets/redshift.js?url'
import snippetsRhtml from 'ace-builds/src-noconflict/snippets/rhtml.js?url'
import snippetsRobot from 'ace-builds/src-noconflict/snippets/robot.js?url'
import snippetsRst from 'ace-builds/src-noconflict/snippets/rst.js?url'
import snippetsRuby from 'ace-builds/src-noconflict/snippets/ruby.js?url'
import snippetsRust from 'ace-builds/src-noconflict/snippets/rust.js?url'
import snippetsSac from 'ace-builds/src-noconflict/snippets/sac.js?url'
import snippetsSass from 'ace-builds/src-noconflict/snippets/sass.js?url'
import snippetsScad from 'ace-builds/src-noconflict/snippets/scad.js?url'
import snippetsScala from 'ace-builds/src-noconflict/snippets/scala.js?url'
import snippetsScheme from 'ace-builds/src-noconflict/snippets/scheme.js?url'
import snippetsScrypt from 'ace-builds/src-noconflict/snippets/scrypt.js?url'
import snippetsScss from 'ace-builds/src-noconflict/snippets/scss.js?url'
import snippetsSh from 'ace-builds/src-noconflict/snippets/sh.js?url'
import snippetsSjs from 'ace-builds/src-noconflict/snippets/sjs.js?url'
import snippetsSlim from 'ace-builds/src-noconflict/snippets/slim.js?url'
import snippetsSmarty from 'ace-builds/src-noconflict/snippets/smarty.js?url'
import snippetsSmithy from 'ace-builds/src-noconflict/snippets/smithy.js?url'
import snippetsSnippets from 'ace-builds/src-noconflict/snippets/snippets.js?url'
import snippetsSoy_template from 'ace-builds/src-noconflict/snippets/soy_template.js?url'
import snippetsSpace from 'ace-builds/src-noconflict/snippets/space.js?url'
import snippetsSparql from 'ace-builds/src-noconflict/snippets/sparql.js?url'
import snippetsSql from 'ace-builds/src-noconflict/snippets/sql.js?url'
import snippetsSqlserver from 'ace-builds/src-noconflict/snippets/sqlserver.js?url'
import snippetsStylus from 'ace-builds/src-noconflict/snippets/stylus.js?url'
import snippetsSvg from 'ace-builds/src-noconflict/snippets/svg.js?url'
import snippetsSwift from 'ace-builds/src-noconflict/snippets/swift.js?url'
import snippetsTcl from 'ace-builds/src-noconflict/snippets/tcl.js?url'
import snippetsTerraform from 'ace-builds/src-noconflict/snippets/terraform.js?url'
import snippetsTex from 'ace-builds/src-noconflict/snippets/tex.js?url'
import snippetsText from 'ace-builds/src-noconflict/snippets/text.js?url'
import snippetsTextile from 'ace-builds/src-noconflict/snippets/textile.js?url'
import snippetsToml from 'ace-builds/src-noconflict/snippets/toml.js?url'
import snippetsTsx from 'ace-builds/src-noconflict/snippets/tsx.js?url'
import snippetsTurtle from 'ace-builds/src-noconflict/snippets/turtle.js?url'
import snippetsTwig from 'ace-builds/src-noconflict/snippets/twig.js?url'
import snippetsTypescript from 'ace-builds/src-noconflict/snippets/typescript.js?url'
import snippetsVala from 'ace-builds/src-noconflict/snippets/vala.js?url'
import snippetsVbscript from 'ace-builds/src-noconflict/snippets/vbscript.js?url'
import snippetsVelocity from 'ace-builds/src-noconflict/snippets/velocity.js?url'
import snippetsVerilog from 'ace-builds/src-noconflict/snippets/verilog.js?url'
import snippetsVhdl from 'ace-builds/src-noconflict/snippets/vhdl.js?url'
import snippetsVisualforce from 'ace-builds/src-noconflict/snippets/visualforce.js?url'
import snippetsWollok from 'ace-builds/src-noconflict/snippets/wollok.js?url'
import snippetsXml from 'ace-builds/src-noconflict/snippets/xml.js?url'
import snippetsXquery from 'ace-builds/src-noconflict/snippets/xquery.js?url'
import snippetsYaml from 'ace-builds/src-noconflict/snippets/yaml.js?url'
import snippetsZeek from 'ace-builds/src-noconflict/snippets/zeek.js?url'


ace.config.setModuleUrl('ace/ext/beautify', extBeautify)
ace.config.setModuleUrl('ace/ext/code_lens', extCode_lens)
ace.config.setModuleUrl('ace/ext/command_bar', extCommand_bar)
ace.config.setModuleUrl('ace/ext/elastic_tabstops_lite', extElastic_tabstops_lite)
ace.config.setModuleUrl('ace/ext/emmet', extEmmet)
ace.config.setModuleUrl('ace/ext/error_marker', extError_marker)
ace.config.setModuleUrl('ace/ext/hardwrap', extHardwrap)
ace.config.setModuleUrl('ace/ext/inline_autocomplete', extInline_autocomplete)
ace.config.setModuleUrl('ace/ext/keyboard_menu', extKeyboard_menu)
ace.config.setModuleUrl('ace/ext/language_tools', extLanguage_tools)
ace.config.setModuleUrl('ace/ext/linking', extLinking)
ace.config.setModuleUrl('ace/ext/modelist', extModelist)
ace.config.setModuleUrl('ace/ext/options', extOptions)
ace.config.setModuleUrl('ace/ext/prompt', extPrompt)
ace.config.setModuleUrl('ace/ext/rtl', extRtl)
ace.config.setModuleUrl('ace/ext/searchbox', extSearchbox)
ace.config.setModuleUrl('ace/ext/settings_menu', extSettings_menu)
ace.config.setModuleUrl('ace/ext/simple_tokenizer', extSimple_tokenizer)
ace.config.setModuleUrl('ace/ext/spellcheck', extSpellcheck)
ace.config.setModuleUrl('ace/ext/split', extSplit)
ace.config.setModuleUrl('ace/ext/static_highlight', extStatic_highlight)
ace.config.setModuleUrl('ace/ext/statusbar', extStatusbar)
ace.config.setModuleUrl('ace/ext/textarea', extTextarea)
ace.config.setModuleUrl('ace/ext/themelist', extThemelist)
ace.config.setModuleUrl('ace/ext/whitespace', extWhitespace)
ace.config.setModuleUrl('ace/keyboard/emacs', keyboardEmacs)
ace.config.setModuleUrl('ace/keyboard/sublime', keyboardSublime)
ace.config.setModuleUrl('ace/keyboard/vim', keyboardVim)
ace.config.setModuleUrl('ace/keyboard/vscode', keyboardVscode)
ace.config.setModuleUrl('ace/mode/abap', modeAbap)
ace.config.setModuleUrl('ace/mode/abc', modeAbc)
ace.config.setModuleUrl('ace/mode/actionscript', modeActionscript)
ace.config.setModuleUrl('ace/mode/ada', modeAda)
ace.config.setModuleUrl('ace/mode/alda', modeAlda)
ace.config.setModuleUrl('ace/mode/apache_conf', modeApache_conf)
ace.config.setModuleUrl('ace/mode/apex', modeApex)
ace.config.setModuleUrl('ace/mode/applescript', modeApplescript)
ace.config.setModuleUrl('ace/mode/aql', modeAql)
ace.config.setModuleUrl('ace/mode/asciidoc', modeAsciidoc)
ace.config.setModuleUrl('ace/mode/asl', modeAsl)
ace.config.setModuleUrl('ace/mode/assembly_x86', modeAssembly_x86)
ace.config.setModuleUrl('ace/mode/astro', modeAstro)
ace.config.setModuleUrl('ace/mode/autohotkey', modeAutohotkey)
ace.config.setModuleUrl('ace/mode/batchfile', modeBatchfile)
ace.config.setModuleUrl('ace/mode/bibtex', modeBibtex)
ace.config.setModuleUrl('ace/mode/c9search', modeC9search)
ace.config.setModuleUrl('ace/mode/c_cpp', modeC_cpp)
ace.config.setModuleUrl('ace/mode/cirru', modeCirru)
ace.config.setModuleUrl('ace/mode/clojure', modeClojure)
ace.config.setModuleUrl('ace/mode/cobol', modeCobol)
ace.config.setModuleUrl('ace/mode/coffee', modeCoffee)
ace.config.setModuleUrl('ace/mode/coldfusion', modeColdfusion)
ace.config.setModuleUrl('ace/mode/crystal', modeCrystal)
ace.config.setModuleUrl('ace/mode/csharp', modeCsharp)
ace.config.setModuleUrl('ace/mode/csound_document', modeCsound_document)
ace.config.setModuleUrl('ace/mode/csound_orchestra', modeCsound_orchestra)
ace.config.setModuleUrl('ace/mode/csound_score', modeCsound_score)
ace.config.setModuleUrl('ace/mode/csp', modeCsp)
ace.config.setModuleUrl('ace/mode/css', modeCss)
ace.config.setModuleUrl('ace/mode/curly', modeCurly)
ace.config.setModuleUrl('ace/mode/cuttlefish', modeCuttlefish)
ace.config.setModuleUrl('ace/mode/d', modeD)
ace.config.setModuleUrl('ace/mode/dart', modeDart)
ace.config.setModuleUrl('ace/mode/diff', modeDiff)
ace.config.setModuleUrl('ace/mode/django', modeDjango)
ace.config.setModuleUrl('ace/mode/dockerfile', modeDockerfile)
ace.config.setModuleUrl('ace/mode/dot', modeDot)
ace.config.setModuleUrl('ace/mode/drools', modeDrools)
ace.config.setModuleUrl('ace/mode/edifact', modeEdifact)
ace.config.setModuleUrl('ace/mode/eiffel', modeEiffel)
ace.config.setModuleUrl('ace/mode/ejs', modeEjs)
ace.config.setModuleUrl('ace/mode/elixir', modeElixir)
ace.config.setModuleUrl('ace/mode/elm', modeElm)
ace.config.setModuleUrl('ace/mode/erlang', modeErlang)
ace.config.setModuleUrl('ace/mode/flix', modeFlix)
ace.config.setModuleUrl('ace/mode/forth', modeForth)
ace.config.setModuleUrl('ace/mode/fortran', modeFortran)
ace.config.setModuleUrl('ace/mode/fsharp', modeFsharp)
ace.config.setModuleUrl('ace/mode/fsl', modeFsl)
ace.config.setModuleUrl('ace/mode/ftl', modeFtl)
ace.config.setModuleUrl('ace/mode/gcode', modeGcode)
ace.config.setModuleUrl('ace/mode/gherkin', modeGherkin)
ace.config.setModuleUrl('ace/mode/gitignore', modeGitignore)
ace.config.setModuleUrl('ace/mode/glsl', modeGlsl)
ace.config.setModuleUrl('ace/mode/gobstones', modeGobstones)
ace.config.setModuleUrl('ace/mode/golang', modeGolang)
ace.config.setModuleUrl('ace/mode/graphqlschema', modeGraphqlschema)
ace.config.setModuleUrl('ace/mode/groovy', modeGroovy)
ace.config.setModuleUrl('ace/mode/haml', modeHaml)
ace.config.setModuleUrl('ace/mode/handlebars', modeHandlebars)
ace.config.setModuleUrl('ace/mode/haskell', modeHaskell)
ace.config.setModuleUrl('ace/mode/haskell_cabal', modeHaskell_cabal)
ace.config.setModuleUrl('ace/mode/haxe', modeHaxe)
ace.config.setModuleUrl('ace/mode/hjson', modeHjson)
ace.config.setModuleUrl('ace/mode/html', modeHtml)
ace.config.setModuleUrl('ace/mode/html_elixir', modeHtml_elixir)
ace.config.setModuleUrl('ace/mode/html_ruby', modeHtml_ruby)
ace.config.setModuleUrl('ace/mode/ini', modeIni)
ace.config.setModuleUrl('ace/mode/io', modeIo)
ace.config.setModuleUrl('ace/mode/ion', modeIon)
ace.config.setModuleUrl('ace/mode/jack', modeJack)
ace.config.setModuleUrl('ace/mode/jade', modeJade)
ace.config.setModuleUrl('ace/mode/java', modeJava)
ace.config.setModuleUrl('ace/mode/javascript', modeJavascript)
ace.config.setModuleUrl('ace/mode/jexl', modeJexl)
ace.config.setModuleUrl('ace/mode/json', modeJson)
ace.config.setModuleUrl('ace/mode/json5', modeJson5)
ace.config.setModuleUrl('ace/mode/jsoniq', modeJsoniq)
ace.config.setModuleUrl('ace/mode/jsp', modeJsp)
ace.config.setModuleUrl('ace/mode/jssm', modeJssm)
ace.config.setModuleUrl('ace/mode/jsx', modeJsx)
ace.config.setModuleUrl('ace/mode/julia', modeJulia)
ace.config.setModuleUrl('ace/mode/kotlin', modeKotlin)
ace.config.setModuleUrl('ace/mode/latex', modeLatex)
ace.config.setModuleUrl('ace/mode/latte', modeLatte)
ace.config.setModuleUrl('ace/mode/less', modeLess)
ace.config.setModuleUrl('ace/mode/liquid', modeLiquid)
ace.config.setModuleUrl('ace/mode/lisp', modeLisp)
ace.config.setModuleUrl('ace/mode/livescript', modeLivescript)
ace.config.setModuleUrl('ace/mode/logiql', modeLogiql)
ace.config.setModuleUrl('ace/mode/logtalk', modeLogtalk)
ace.config.setModuleUrl('ace/mode/lsl', modeLsl)
ace.config.setModuleUrl('ace/mode/lua', modeLua)
ace.config.setModuleUrl('ace/mode/luapage', modeLuapage)
ace.config.setModuleUrl('ace/mode/lucene', modeLucene)
ace.config.setModuleUrl('ace/mode/makefile', modeMakefile)
ace.config.setModuleUrl('ace/mode/markdown', modeMarkdown)
ace.config.setModuleUrl('ace/mode/mask', modeMask)
ace.config.setModuleUrl('ace/mode/matlab', modeMatlab)
ace.config.setModuleUrl('ace/mode/maze', modeMaze)
ace.config.setModuleUrl('ace/mode/mediawiki', modeMediawiki)
ace.config.setModuleUrl('ace/mode/mel', modeMel)
ace.config.setModuleUrl('ace/mode/mips', modeMips)
ace.config.setModuleUrl('ace/mode/mixal', modeMixal)
ace.config.setModuleUrl('ace/mode/mushcode', modeMushcode)
ace.config.setModuleUrl('ace/mode/mysql', modeMysql)
ace.config.setModuleUrl('ace/mode/nasal', modeNasal)
ace.config.setModuleUrl('ace/mode/nginx', modeNginx)
ace.config.setModuleUrl('ace/mode/nim', modeNim)
ace.config.setModuleUrl('ace/mode/nix', modeNix)
ace.config.setModuleUrl('ace/mode/nsis', modeNsis)
ace.config.setModuleUrl('ace/mode/nunjucks', modeNunjucks)
ace.config.setModuleUrl('ace/mode/objectivec', modeObjectivec)
ace.config.setModuleUrl('ace/mode/ocaml', modeOcaml)
ace.config.setModuleUrl('ace/mode/odin', modeOdin)
ace.config.setModuleUrl('ace/mode/partiql', modePartiql)
ace.config.setModuleUrl('ace/mode/pascal', modePascal)
ace.config.setModuleUrl('ace/mode/perl', modePerl)
ace.config.setModuleUrl('ace/mode/pgsql', modePgsql)
ace.config.setModuleUrl('ace/mode/php', modePhp)
ace.config.setModuleUrl('ace/mode/php_laravel_blade', modePhp_laravel_blade)
ace.config.setModuleUrl('ace/mode/pig', modePig)
ace.config.setModuleUrl('ace/mode/plain_text', modePlain_text)
ace.config.setModuleUrl('ace/mode/plsql', modePlsql)
ace.config.setModuleUrl('ace/mode/powershell', modePowershell)
ace.config.setModuleUrl('ace/mode/praat', modePraat)
ace.config.setModuleUrl('ace/mode/prisma', modePrisma)
ace.config.setModuleUrl('ace/mode/prolog', modeProlog)
ace.config.setModuleUrl('ace/mode/properties', modeProperties)
ace.config.setModuleUrl('ace/mode/protobuf', modeProtobuf)
ace.config.setModuleUrl('ace/mode/prql', modePrql)
ace.config.setModuleUrl('ace/mode/puppet', modePuppet)
ace.config.setModuleUrl('ace/mode/python', modePython)
ace.config.setModuleUrl('ace/mode/qml', modeQml)
ace.config.setModuleUrl('ace/mode/r', modeR)
ace.config.setModuleUrl('ace/mode/raku', modeRaku)
ace.config.setModuleUrl('ace/mode/razor', modeRazor)
ace.config.setModuleUrl('ace/mode/rdoc', modeRdoc)
ace.config.setModuleUrl('ace/mode/red', modeRed)
ace.config.setModuleUrl('ace/mode/redshift', modeRedshift)
ace.config.setModuleUrl('ace/mode/rhtml', modeRhtml)
ace.config.setModuleUrl('ace/mode/robot', modeRobot)
ace.config.setModuleUrl('ace/mode/rst', modeRst)
ace.config.setModuleUrl('ace/mode/ruby', modeRuby)
ace.config.setModuleUrl('ace/mode/rust', modeRust)
ace.config.setModuleUrl('ace/mode/sac', modeSac)
ace.config.setModuleUrl('ace/mode/sass', modeSass)
ace.config.setModuleUrl('ace/mode/scad', modeScad)
ace.config.setModuleUrl('ace/mode/scala', modeScala)
ace.config.setModuleUrl('ace/mode/scheme', modeScheme)
ace.config.setModuleUrl('ace/mode/scrypt', modeScrypt)
ace.config.setModuleUrl('ace/mode/scss', modeScss)
ace.config.setModuleUrl('ace/mode/sh', modeSh)
ace.config.setModuleUrl('ace/mode/sjs', modeSjs)
ace.config.setModuleUrl('ace/mode/slim', modeSlim)
ace.config.setModuleUrl('ace/mode/smarty', modeSmarty)
ace.config.setModuleUrl('ace/mode/smithy', modeSmithy)
ace.config.setModuleUrl('ace/mode/snippets', modeSnippets)
ace.config.setModuleUrl('ace/mode/soy_template', modeSoy_template)
ace.config.setModuleUrl('ace/mode/space', modeSpace)
ace.config.setModuleUrl('ace/mode/sparql', modeSparql)
ace.config.setModuleUrl('ace/mode/sql', modeSql)
ace.config.setModuleUrl('ace/mode/sqlserver', modeSqlserver)
ace.config.setModuleUrl('ace/mode/stylus', modeStylus)
ace.config.setModuleUrl('ace/mode/svg', modeSvg)
ace.config.setModuleUrl('ace/mode/swift', modeSwift)
ace.config.setModuleUrl('ace/mode/tcl', modeTcl)
ace.config.setModuleUrl('ace/mode/terraform', modeTerraform)
ace.config.setModuleUrl('ace/mode/tex', modeTex)
ace.config.setModuleUrl('ace/mode/text', modeText)
ace.config.setModuleUrl('ace/mode/textile', modeTextile)
ace.config.setModuleUrl('ace/mode/toml', modeToml)
ace.config.setModuleUrl('ace/mode/tsx', modeTsx)
ace.config.setModuleUrl('ace/mode/turtle', modeTurtle)
ace.config.setModuleUrl('ace/mode/twig', modeTwig)
ace.config.setModuleUrl('ace/mode/typescript', modeTypescript)
ace.config.setModuleUrl('ace/mode/vala', modeVala)
ace.config.setModuleUrl('ace/mode/vbscript', modeVbscript)
ace.config.setModuleUrl('ace/mode/velocity', modeVelocity)
ace.config.setModuleUrl('ace/mode/verilog', modeVerilog)
ace.config.setModuleUrl('ace/mode/vhdl', modeVhdl)
ace.config.setModuleUrl('ace/mode/visualforce', modeVisualforce)
ace.config.setModuleUrl('ace/mode/wollok', modeWollok)
ace.config.setModuleUrl('ace/mode/xml', modeXml)
ace.config.setModuleUrl('ace/mode/xquery', modeXquery)
ace.config.setModuleUrl('ace/mode/yaml', modeYaml)
ace.config.setModuleUrl('ace/mode/zeek', modeZeek)

ace.config.setModuleUrl('ace/theme/ambiance', themeAmbiance)
ace.config.setModuleUrl('ace/theme/chaos', themeChaos)
ace.config.setModuleUrl('ace/theme/chrome', themeChrome)
ace.config.setModuleUrl('ace/theme/cloud9_day', themeCloud9_day)
ace.config.setModuleUrl('ace/theme/cloud9_night', themeCloud9_night)
ace.config.setModuleUrl('ace/theme/cloud9_night_low_color', themeCloud9_night_low_color)
ace.config.setModuleUrl('ace/theme/cloud_editor', themeCloud_editor)
ace.config.setModuleUrl('ace/theme/cloud_editor_dark', themeCloud_editor_dark)
ace.config.setModuleUrl('ace/theme/clouds', themeClouds)
ace.config.setModuleUrl('ace/theme/clouds_midnight', themeClouds_midnight)
ace.config.setModuleUrl('ace/theme/cobalt', themeCobalt)
ace.config.setModuleUrl('ace/theme/crimson_editor', themeCrimson_editor)
ace.config.setModuleUrl('ace/theme/dawn', themeDawn)
ace.config.setModuleUrl('ace/theme/dracula', themeDracula)
ace.config.setModuleUrl('ace/theme/dreamweaver', themeDreamweaver)
ace.config.setModuleUrl('ace/theme/eclipse', themeEclipse)
ace.config.setModuleUrl('ace/theme/github', themeGithub)
ace.config.setModuleUrl('ace/theme/github_dark', themeGithub_dark)
ace.config.setModuleUrl('ace/theme/gob', themeGob)
ace.config.setModuleUrl('ace/theme/gruvbox', themeGruvbox)
ace.config.setModuleUrl('ace/theme/gruvbox_dark_hard', themeGruvbox_dark_hard)
ace.config.setModuleUrl('ace/theme/gruvbox_light_hard', themeGruvbox_light_hard)
ace.config.setModuleUrl('ace/theme/idle_fingers', themeIdle_fingers)
ace.config.setModuleUrl('ace/theme/iplastic', themeIplastic)
ace.config.setModuleUrl('ace/theme/katzenmilch', themeKatzenmilch)
ace.config.setModuleUrl('ace/theme/kr_theme', themeKr_theme)
ace.config.setModuleUrl('ace/theme/kuroir', themeKuroir)
ace.config.setModuleUrl('ace/theme/merbivore', themeMerbivore)
ace.config.setModuleUrl('ace/theme/merbivore_soft', themeMerbivore_soft)
ace.config.setModuleUrl('ace/theme/mono_industrial', themeMono_industrial)
ace.config.setModuleUrl('ace/theme/monokai', themeMonokai)
ace.config.setModuleUrl('ace/theme/nord_dark', themeNord_dark)
ace.config.setModuleUrl('ace/theme/one_dark', themeOne_dark)
ace.config.setModuleUrl('ace/theme/pastel_on_dark', themePastel_on_dark)
ace.config.setModuleUrl('ace/theme/solarized_dark', themeSolarized_dark)
ace.config.setModuleUrl('ace/theme/solarized_light', themeSolarized_light)
ace.config.setModuleUrl('ace/theme/sqlserver', themeSqlserver)
ace.config.setModuleUrl('ace/theme/terminal', themeTerminal)
ace.config.setModuleUrl('ace/theme/textmate', themeTextmate)
ace.config.setModuleUrl('ace/theme/tomorrow', themeTomorrow)
ace.config.setModuleUrl('ace/theme/tomorrow_night', themeTomorrow_night)
ace.config.setModuleUrl('ace/theme/tomorrow_night_blue', themeTomorrow_night_blue)
ace.config.setModuleUrl('ace/theme/tomorrow_night_bright', themeTomorrow_night_bright)
ace.config.setModuleUrl('ace/theme/tomorrow_night_eighties', themeTomorrow_night_eighties)
ace.config.setModuleUrl('ace/theme/twilight', themeTwilight)
ace.config.setModuleUrl('ace/theme/vibrant_ink', themeVibrant_ink)
ace.config.setModuleUrl('ace/theme/xcode', themeXcode)
ace.config.setModuleUrl('ace/mode/base_worker', modeBase_worker)
ace.config.setModuleUrl('ace/mode/coffee_worker', modeCoffee_worker)
ace.config.setModuleUrl('ace/mode/css_worker', modeCss_worker)
ace.config.setModuleUrl('ace/mode/html_worker', modeHtml_worker)
ace.config.setModuleUrl('ace/mode/javascript_worker', modeJavascript_worker)
ace.config.setModuleUrl('ace/mode/json_worker', modeJson_worker)
ace.config.setModuleUrl('ace/mode/lua_worker', modeLua_worker)
ace.config.setModuleUrl('ace/mode/php_worker', modePhp_worker)
ace.config.setModuleUrl('ace/mode/xml_worker', modeXml_worker)
ace.config.setModuleUrl('ace/mode/xquery_worker', modeXquery_worker)
ace.config.setModuleUrl('ace/mode/yaml_worker', modeYaml_worker)
ace.config.setModuleUrl('ace/snippets/abap', snippetsAbap)
ace.config.setModuleUrl('ace/snippets/abc', snippetsAbc)
ace.config.setModuleUrl('ace/snippets/actionscript', snippetsActionscript)
ace.config.setModuleUrl('ace/snippets/ada', snippetsAda)
ace.config.setModuleUrl('ace/snippets/alda', snippetsAlda)
ace.config.setModuleUrl('ace/snippets/apache_conf', snippetsApache_conf)
ace.config.setModuleUrl('ace/snippets/apex', snippetsApex)
ace.config.setModuleUrl('ace/snippets/applescript', snippetsApplescript)
ace.config.setModuleUrl('ace/snippets/aql', snippetsAql)
ace.config.setModuleUrl('ace/snippets/asciidoc', snippetsAsciidoc)
ace.config.setModuleUrl('ace/snippets/asl', snippetsAsl)
ace.config.setModuleUrl('ace/snippets/assembly_x86', snippetsAssembly_x86)
ace.config.setModuleUrl('ace/snippets/astro', snippetsAstro)
ace.config.setModuleUrl('ace/snippets/autohotkey', snippetsAutohotkey)
ace.config.setModuleUrl('ace/snippets/batchfile', snippetsBatchfile)
ace.config.setModuleUrl('ace/snippets/bibtex', snippetsBibtex)
ace.config.setModuleUrl('ace/snippets/c9search', snippetsC9search)
ace.config.setModuleUrl('ace/snippets/c_cpp', snippetsC_cpp)
ace.config.setModuleUrl('ace/snippets/cirru', snippetsCirru)
ace.config.setModuleUrl('ace/snippets/clojure', snippetsClojure)
ace.config.setModuleUrl('ace/snippets/cobol', snippetsCobol)
ace.config.setModuleUrl('ace/snippets/coffee', snippetsCoffee)
ace.config.setModuleUrl('ace/snippets/coldfusion', snippetsColdfusion)
ace.config.setModuleUrl('ace/snippets/crystal', snippetsCrystal)
ace.config.setModuleUrl('ace/snippets/csharp', snippetsCsharp)
ace.config.setModuleUrl('ace/snippets/csound_document', snippetsCsound_document)
ace.config.setModuleUrl('ace/snippets/csound_orchestra', snippetsCsound_orchestra)
ace.config.setModuleUrl('ace/snippets/csound_score', snippetsCsound_score)
ace.config.setModuleUrl('ace/snippets/csp', snippetsCsp)
ace.config.setModuleUrl('ace/snippets/css', snippetsCss)
ace.config.setModuleUrl('ace/snippets/curly', snippetsCurly)
ace.config.setModuleUrl('ace/snippets/cuttlefish', snippetsCuttlefish)
ace.config.setModuleUrl('ace/snippets/d', snippetsD)
ace.config.setModuleUrl('ace/snippets/dart', snippetsDart)
ace.config.setModuleUrl('ace/snippets/diff', snippetsDiff)
ace.config.setModuleUrl('ace/snippets/django', snippetsDjango)
ace.config.setModuleUrl('ace/snippets/dockerfile', snippetsDockerfile)
ace.config.setModuleUrl('ace/snippets/dot', snippetsDot)
ace.config.setModuleUrl('ace/snippets/drools', snippetsDrools)
ace.config.setModuleUrl('ace/snippets/edifact', snippetsEdifact)
ace.config.setModuleUrl('ace/snippets/eiffel', snippetsEiffel)
ace.config.setModuleUrl('ace/snippets/ejs', snippetsEjs)
ace.config.setModuleUrl('ace/snippets/elixir', snippetsElixir)
ace.config.setModuleUrl('ace/snippets/elm', snippetsElm)
ace.config.setModuleUrl('ace/snippets/erlang', snippetsErlang)
ace.config.setModuleUrl('ace/snippets/flix', snippetsFlix)
ace.config.setModuleUrl('ace/snippets/forth', snippetsForth)
ace.config.setModuleUrl('ace/snippets/fortran', snippetsFortran)
ace.config.setModuleUrl('ace/snippets/fsharp', snippetsFsharp)
ace.config.setModuleUrl('ace/snippets/fsl', snippetsFsl)
ace.config.setModuleUrl('ace/snippets/ftl', snippetsFtl)
ace.config.setModuleUrl('ace/snippets/gcode', snippetsGcode)
ace.config.setModuleUrl('ace/snippets/gherkin', snippetsGherkin)
ace.config.setModuleUrl('ace/snippets/gitignore', snippetsGitignore)
ace.config.setModuleUrl('ace/snippets/glsl', snippetsGlsl)
ace.config.setModuleUrl('ace/snippets/gobstones', snippetsGobstones)
ace.config.setModuleUrl('ace/snippets/golang', snippetsGolang)
ace.config.setModuleUrl('ace/snippets/graphqlschema', snippetsGraphqlschema)
ace.config.setModuleUrl('ace/snippets/groovy', snippetsGroovy)
ace.config.setModuleUrl('ace/snippets/haml', snippetsHaml)
ace.config.setModuleUrl('ace/snippets/handlebars', snippetsHandlebars)
ace.config.setModuleUrl('ace/snippets/haskell', snippetsHaskell)
ace.config.setModuleUrl('ace/snippets/haskell_cabal', snippetsHaskell_cabal)
ace.config.setModuleUrl('ace/snippets/haxe', snippetsHaxe)
ace.config.setModuleUrl('ace/snippets/hjson', snippetsHjson)
ace.config.setModuleUrl('ace/snippets/html', snippetsHtml)
ace.config.setModuleUrl('ace/snippets/html_elixir', snippetsHtml_elixir)
ace.config.setModuleUrl('ace/snippets/html_ruby', snippetsHtml_ruby)
ace.config.setModuleUrl('ace/snippets/ini', snippetsIni)
ace.config.setModuleUrl('ace/snippets/io', snippetsIo)
ace.config.setModuleUrl('ace/snippets/ion', snippetsIon)
ace.config.setModuleUrl('ace/snippets/jack', snippetsJack)
ace.config.setModuleUrl('ace/snippets/jade', snippetsJade)
ace.config.setModuleUrl('ace/snippets/java', snippetsJava)
ace.config.setModuleUrl('ace/snippets/javascript', snippetsJavascript)
ace.config.setModuleUrl('ace/snippets/jexl', snippetsJexl)
ace.config.setModuleUrl('ace/snippets/json', snippetsJson)
ace.config.setModuleUrl('ace/snippets/json5', snippetsJson5)
ace.config.setModuleUrl('ace/snippets/jsoniq', snippetsJsoniq)
ace.config.setModuleUrl('ace/snippets/jsp', snippetsJsp)
ace.config.setModuleUrl('ace/snippets/jssm', snippetsJssm)
ace.config.setModuleUrl('ace/snippets/jsx', snippetsJsx)
ace.config.setModuleUrl('ace/snippets/julia', snippetsJulia)
ace.config.setModuleUrl('ace/snippets/kotlin', snippetsKotlin)
ace.config.setModuleUrl('ace/snippets/latex', snippetsLatex)
ace.config.setModuleUrl('ace/snippets/latte', snippetsLatte)
ace.config.setModuleUrl('ace/snippets/less', snippetsLess)
ace.config.setModuleUrl('ace/snippets/liquid', snippetsLiquid)
ace.config.setModuleUrl('ace/snippets/lisp', snippetsLisp)
ace.config.setModuleUrl('ace/snippets/livescript', snippetsLivescript)
ace.config.setModuleUrl('ace/snippets/logiql', snippetsLogiql)
ace.config.setModuleUrl('ace/snippets/logtalk', snippetsLogtalk)
ace.config.setModuleUrl('ace/snippets/lsl', snippetsLsl)
ace.config.setModuleUrl('ace/snippets/lua', snippetsLua)
ace.config.setModuleUrl('ace/snippets/luapage', snippetsLuapage)
ace.config.setModuleUrl('ace/snippets/lucene', snippetsLucene)
ace.config.setModuleUrl('ace/snippets/makefile', snippetsMakefile)
ace.config.setModuleUrl('ace/snippets/markdown', snippetsMarkdown)
ace.config.setModuleUrl('ace/snippets/mask', snippetsMask)
ace.config.setModuleUrl('ace/snippets/matlab', snippetsMatlab)
ace.config.setModuleUrl('ace/snippets/maze', snippetsMaze)
ace.config.setModuleUrl('ace/snippets/mediawiki', snippetsMediawiki)
ace.config.setModuleUrl('ace/snippets/mel', snippetsMel)
ace.config.setModuleUrl('ace/snippets/mips', snippetsMips)
ace.config.setModuleUrl('ace/snippets/mixal', snippetsMixal)
ace.config.setModuleUrl('ace/snippets/mushcode', snippetsMushcode)
ace.config.setModuleUrl('ace/snippets/mysql', snippetsMysql)
ace.config.setModuleUrl('ace/snippets/nasal', snippetsNasal)
ace.config.setModuleUrl('ace/snippets/nginx', snippetsNginx)
ace.config.setModuleUrl('ace/snippets/nim', snippetsNim)
ace.config.setModuleUrl('ace/snippets/nix', snippetsNix)
ace.config.setModuleUrl('ace/snippets/nsis', snippetsNsis)
ace.config.setModuleUrl('ace/snippets/nunjucks', snippetsNunjucks)
ace.config.setModuleUrl('ace/snippets/objectivec', snippetsObjectivec)
ace.config.setModuleUrl('ace/snippets/ocaml', snippetsOcaml)
ace.config.setModuleUrl('ace/snippets/odin', snippetsOdin)
ace.config.setModuleUrl('ace/snippets/partiql', snippetsPartiql)
ace.config.setModuleUrl('ace/snippets/pascal', snippetsPascal)
ace.config.setModuleUrl('ace/snippets/perl', snippetsPerl)
ace.config.setModuleUrl('ace/snippets/pgsql', snippetsPgsql)
ace.config.setModuleUrl('ace/snippets/php', snippetsPhp)
ace.config.setModuleUrl('ace/snippets/php_laravel_blade', snippetsPhp_laravel_blade)
ace.config.setModuleUrl('ace/snippets/pig', snippetsPig)
ace.config.setModuleUrl('ace/snippets/plain_text', snippetsPlain_text)
ace.config.setModuleUrl('ace/snippets/plsql', snippetsPlsql)
ace.config.setModuleUrl('ace/snippets/powershell', snippetsPowershell)
ace.config.setModuleUrl('ace/snippets/praat', snippetsPraat)
ace.config.setModuleUrl('ace/snippets/prisma', snippetsPrisma)
ace.config.setModuleUrl('ace/snippets/prolog', snippetsProlog)
ace.config.setModuleUrl('ace/snippets/properties', snippetsProperties)
ace.config.setModuleUrl('ace/snippets/protobuf', snippetsProtobuf)
ace.config.setModuleUrl('ace/snippets/prql', snippetsPrql)
ace.config.setModuleUrl('ace/snippets/puppet', snippetsPuppet)
ace.config.setModuleUrl('ace/snippets/python', snippetsPython)
ace.config.setModuleUrl('ace/snippets/qml', snippetsQml)
ace.config.setModuleUrl('ace/snippets/r', snippetsR)
ace.config.setModuleUrl('ace/snippets/raku', snippetsRaku)
ace.config.setModuleUrl('ace/snippets/razor', snippetsRazor)
ace.config.setModuleUrl('ace/snippets/rdoc', snippetsRdoc)
ace.config.setModuleUrl('ace/snippets/red', snippetsRed)
ace.config.setModuleUrl('ace/snippets/redshift', snippetsRedshift)
ace.config.setModuleUrl('ace/snippets/rhtml', snippetsRhtml)
ace.config.setModuleUrl('ace/snippets/robot', snippetsRobot)
ace.config.setModuleUrl('ace/snippets/rst', snippetsRst)
ace.config.setModuleUrl('ace/snippets/ruby', snippetsRuby)
ace.config.setModuleUrl('ace/snippets/rust', snippetsRust)
ace.config.setModuleUrl('ace/snippets/sac', snippetsSac)
ace.config.setModuleUrl('ace/snippets/sass', snippetsSass)
ace.config.setModuleUrl('ace/snippets/scad', snippetsScad)
ace.config.setModuleUrl('ace/snippets/scala', snippetsScala)
ace.config.setModuleUrl('ace/snippets/scheme', snippetsScheme)
ace.config.setModuleUrl('ace/snippets/scrypt', snippetsScrypt)
ace.config.setModuleUrl('ace/snippets/scss', snippetsScss)
ace.config.setModuleUrl('ace/snippets/sh', snippetsSh)
ace.config.setModuleUrl('ace/snippets/sjs', snippetsSjs)
ace.config.setModuleUrl('ace/snippets/slim', snippetsSlim)
ace.config.setModuleUrl('ace/snippets/smarty', snippetsSmarty)
ace.config.setModuleUrl('ace/snippets/smithy', snippetsSmithy)
ace.config.setModuleUrl('ace/snippets/snippets', snippetsSnippets)
ace.config.setModuleUrl('ace/snippets/soy_template', snippetsSoy_template)
ace.config.setModuleUrl('ace/snippets/space', snippetsSpace)
ace.config.setModuleUrl('ace/snippets/sparql', snippetsSparql)
ace.config.setModuleUrl('ace/snippets/sql', snippetsSql)
ace.config.setModuleUrl('ace/snippets/sqlserver', snippetsSqlserver)
ace.config.setModuleUrl('ace/snippets/stylus', snippetsStylus)
ace.config.setModuleUrl('ace/snippets/svg', snippetsSvg)
ace.config.setModuleUrl('ace/snippets/swift', snippetsSwift)
ace.config.setModuleUrl('ace/snippets/tcl', snippetsTcl)
ace.config.setModuleUrl('ace/snippets/terraform', snippetsTerraform)
ace.config.setModuleUrl('ace/snippets/tex', snippetsTex)
ace.config.setModuleUrl('ace/snippets/text', snippetsText)
ace.config.setModuleUrl('ace/snippets/textile', snippetsTextile)
ace.config.setModuleUrl('ace/snippets/toml', snippetsToml)
ace.config.setModuleUrl('ace/snippets/tsx', snippetsTsx)
ace.config.setModuleUrl('ace/snippets/turtle', snippetsTurtle)
ace.config.setModuleUrl('ace/snippets/twig', snippetsTwig)
ace.config.setModuleUrl('ace/snippets/typescript', snippetsTypescript)
ace.config.setModuleUrl('ace/snippets/vala', snippetsVala)
ace.config.setModuleUrl('ace/snippets/vbscript', snippetsVbscript)
ace.config.setModuleUrl('ace/snippets/velocity', snippetsVelocity)
ace.config.setModuleUrl('ace/snippets/verilog', snippetsVerilog)
ace.config.setModuleUrl('ace/snippets/vhdl', snippetsVhdl)
ace.config.setModuleUrl('ace/snippets/visualforce', snippetsVisualforce)
ace.config.setModuleUrl('ace/snippets/wollok', snippetsWollok)
ace.config.setModuleUrl('ace/snippets/xml', snippetsXml)
ace.config.setModuleUrl('ace/snippets/xquery', snippetsXquery)
ace.config.setModuleUrl('ace/snippets/yaml', snippetsYaml)
ace.config.setModuleUrl('ace/snippets/zeek', snippetsZeek)