import "../../css/inlineEditor/editor.css";
import { initSidebarResize } from './editor/sidebarResize';
import { EditorBridge } from './editor/editorBridge';

initSidebarResize();

const iframe = document.getElementById('preview-iframe') as HTMLIFrameElement;
const editBtn = document.getElementById('edit-btn') as HTMLButtonElement;

const editorBridge = new EditorBridge(iframe);

editBtn.addEventListener('click', () => {
    editorBridge.toggleInlineEdit();
});