export default function t(key) {
    return window.translations?.[key] || key;
}