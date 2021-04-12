export const parseGlobals = (dataTagId) => {
    var element = document.getElementById(dataTagId);

    window.globals = JSON.parse(element.dataset.globals);
}
