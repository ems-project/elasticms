export const debug = (...args: unknown[]): void => {
    if ((import.meta as ImportMeta).env?.DEV) {
        console.debug(...args);
    }
};