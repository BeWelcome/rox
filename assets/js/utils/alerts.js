import Toastify from 'toastify-js';

const ERROR_BCG = 'linear-gradient(90deg, rgba(170,40,28,1) 0%, rgba(224,106,15,1) 100%)';
const SUCCESS_BCG = 'linear-gradient(90deg, rgba(52,170,28,1) 0%, rgba(29,213,35,1) 100%)';

export const alertError = (msg) => {
      showAlert(msg, ERROR_BCG)
}

export const alertSuccess = (msg) => {
    showAlert(msg, SUCCESS_BCG)
}

const showAlert = (msg, background) => {
    Toastify({
        text: msg,
        close: true,
        position: "center",
        backgroundColor: background
      }).showToast();
}
