/**
 * Texts are fetched from a global window.globals.texts javascript variable. To see how they get put there see globals.js
 */
export const getText = (key) => {
    if (window.globals?.texts?.[key]) {
        return window.globals?.texts?.[key]
    }
    console.error(`Translation key "${key}" not found`)
    return key;
}
