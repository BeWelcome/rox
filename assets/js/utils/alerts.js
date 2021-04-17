const ALERT_TARGET_ID = 'js-alert'
const ALERT_SUCCESS = 'alert-success';
const ALERT_FAIL = 'alert-danger';

export const alertError = (msg) => {
    const updated = updateNotice(msg, ALERT_FAIL);

    if (!updated) {
        console.error(msg);
    }
}

export const alertSuccess = (msg) => {
    updateNotice(msg, ALERT_SUCCESS);
}

const updateNotice = (msg, newClass) => {
    const alertTarget = document.getElementById(ALERT_TARGET_ID);
    if (alertTarget) {
        alertTarget.innerHTML = msg;
        // remove previous classes
        alertTarget.classList.remove(ALERT_SUCCESS);
        alertTarget.classList.remove(ALERT_FAIL);

        // set new
        alertTarget.classList.add(newClass);

        // show (unless it is already visible, then this is no operation)
        alertTarget.classList.remove('d-none');

        return true;
    }
    return false;
}
