export default function t(key, replace = null) {
    let translation = window.translations?.[key] || key;

    if (replace !== null) {
        Object.keys(replace).forEach(pattern => {
            translation = translation.replace(pattern, replace[pattern]);
        })
    }

    return translation;
}
