export const debug = (...args: any[]): void => {
    if ((import.meta as any).env.DEV) {
        console.debug(...args);
    }
};