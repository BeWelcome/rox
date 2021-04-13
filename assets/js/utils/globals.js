export const parseGlobals = (dataTagId) => {
    var element = document.getElementById(dataTagId);

    const globalsString = element?.dataset?.globals;
    if (globalsString) {
        window.globals = JSON.parse(globalsString);
    }
}
