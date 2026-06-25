import {
    handleBrowserPushPreferenceChange,
    requestBrowserPushPermission,
} from '../browserPushPreference';

const member = document.getElementById('member').value
const preferences = document.querySelectorAll( '.preference')

preferences.forEach(
    async preference => {
        preference.addEventListener('change', async (event) => {
            let value = null
            let browserPushPermission = null
            if (event.target.type === 'checkbox') {
                value = event.target.checked
                if (isBrowserPushPreference(preference) && !event.target.checked) {
                    browserPushPermission = requestBrowserPushPermission(preference)
                }
            } else {
                value = event.target.value
            }

            const form = new FormData();
            form.append('member', member);
            form.append('preference', preference.id.replace('preferences_', ''));
            form.append('value', value);

            const response = await fetch("/members/update/preference", { method: 'POST', body: form });

            if (response.ok && isBrowserPushPreference(preference)) {
                await handleBrowserPushPreferenceChange(preference, browserPushPermission)
            }

            console.log(form)
        })
    }
)

function isBrowserPushPreference(preference) {
    return 'browserPushPreference' in preference.dataset
}
