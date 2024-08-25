const passwordField = document.querySelector('.js-password-input');
const showHidePasswordButton = document.querySelector('.js-password-show-hide');

const showHidePassword = (event) => {
    if (passwordField.type === 'text') {
        passwordField.type = 'password';
        showHidePasswordButton.innerHTML="<svg class=\"\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\">\n" +
            "                              <path class=\"block\" d=\"M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z\"></path>\n" +
            "                              <circle class=\"block\" cx=\"12\" cy=\"12\" r=\"3\"></circle>\n" +
            "                            </svg>";
    } else {
        passwordField.type = 'text';
        showHidePasswordButton.innerHTML="<svg class=\"\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\">\n" +
            "                              <path class=\"\" d=\"M9.88 9.88a3 3 0 1 0 4.24 4.24\"></path>\n" +
            "                              <path class=\"\" d=\"M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68\"></path>\n" +
            "                              <path class=\"\" d=\"M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61\"></path>\n" +
            "                              <line class=\"\" x1=\"2\" x2=\"22\" y1=\"2\" y2=\"22\"></line>\n" +
            "                            </svg>";
    }
}

showHidePasswordButton.addEventListener("click", showHidePassword);
