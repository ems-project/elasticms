import Document from '@tiptap/extension-document'
import Paragraph from '@tiptap/extension-paragraph'
import Text from '@tiptap/extension-text'
import { Gapcursor } from '@tiptap/extension-gapcursor'
import { HardBreak } from '@tiptap/extension-hard-break'

export const DEFAULT_EXTENSIONS = [Document, Paragraph, Text, Gapcursor, HardBreak]
