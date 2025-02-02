import {emsReceiver} from './modules/emsReceiver';

document.addEventListener('DOMContentLoaded', onLoad);

export function onLoad() {
    let metaId = document.head.querySelector('meta[property="id"]');
    let metaDomains = document.head.querySelector('meta[property="domains"]');

    new emsReceiver({
        'id': metaId.dataset.id,
        'domains': JSON.parse(metaDomains.dataset.list)
    });
}
