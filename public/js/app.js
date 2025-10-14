export const AppConfig = {
    PRODUCTION: false,
    BASE_URL: "http://mammamstore.local",
    PROJECT_NAME: "/mam-mam-store"
};
export const FULL_URL = AppConfig.PRODUCTION ? '' : AppConfig.BASE_URL;
