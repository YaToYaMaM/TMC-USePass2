declare module 'ziggy-js' {
    interface RouteParams {
        [key: string]: string | number | boolean;
    }

    export function route(
        name: string,
        params?: RouteParams | RouteParams[],
        absolute?: boolean
    ): string;
}
