(() => {
    const language = document.documentElement.lang.split('-')[0];

    if (['fr', 'nl'].includes(language)) {
        document.cookie =
            `site_language=${language}; Path=/; Max-Age=31536000; SameSite=Lax; Secure`;
    }
})();