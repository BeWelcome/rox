/**
 * Texts are fetched from a global window.globals.texts javascript variable. To see how they get put there see globals.js
 */
export const getText = (key) => {
    return window.globals?.texts?.[key] || key;
}
