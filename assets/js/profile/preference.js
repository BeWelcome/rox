import {
    enableBrowserPushForCurrentBrowser,
    getBrowserPushDeviceState,
    handleBrowserPushPreferenceChange,
    requestBrowserNotificationPermission,
    requestBrowserPushPermission,
} from '../browserPushPreference';

const memberElement = document.getElementById('member')
const member = memberElement.value
const preferences = document.querySelectorAll('.preference')
const browserPushPreference = document.querySelector('[data-browser-push-preference]')
const browserPushControls = document.querySelector('[data-browser-push-device-controls]')

preferences.forEach(preference => {
    preference.addEventListener('change', event => updatePreference(preference, event))
})

if (browserPushPreference && browserPushControls) {
    const enableButton = browserPushControls.querySelector('[data-browser-push-enable]')
    enableButton.addEventListener('click', async () => {
        enableButton.disabled = true
        if (getPreferenceValue(browserPushPreference) === 'OpenOnly') {
            await requestBrowserNotificationPermission(browserPushPreference)
            await renderBrowserPushState()
        } else {
            await renderBrowserPushAttempt(await enableBrowserPushForCurrentBrowser(browserPushPreference))
        }
    })
    renderBrowserPushState()
}

async function updatePreference(preference, event) {
    const previousBrowserPushValue = preference.dataset.browserPushPreferenceValue || 'No'
    let value = event.target.type === 'checkbox' ? event.target.checked : event.target.value
    let browserPushPermission = null
    if (isBrowserPushPreference(preference) && getPreferenceValue(preference) === 'Always') {
        browserPushPermission = requestBrowserPushPermission(preference)
    } else if (isBrowserPushPreference(preference) && getPreferenceValue(preference) === 'OpenOnly') {
        browserPushPermission = requestBrowserNotificationPermission(preference)
    }

    const form = new FormData()
    form.append('member', member)
    form.append('preference', preference.id.replace('preferences_', ''))
    form.append('value', value)

    try {
        const response = await fetch(memberElement.dataset.updateUrl, {
            method: 'POST',
            headers: {'X-CSRF-Token': memberElement.dataset.csrfToken},
            body: form,
        })
        if (!response.ok) {
            throw new Error(`Preference update failed with ${response.status}`)
        }

        if (isBrowserPushPreference(preference)) {
            await renderBrowserPushAttempt(
                await handleBrowserPushPreferenceChange(preference, browserPushPermission)
            )
        }
    } catch (error) {
        if (isBrowserPushPreference(preference)) {
            setPreferenceValue(preference, previousBrowserPushValue)
            await renderBrowserPushState('error')
        }
    }
}

async function renderBrowserPushAttempt(enabled) {
    if (enabled) {
        await renderBrowserPushState()

        return
    }

    const currentState = await getBrowserPushDeviceState(browserPushPreference)
    await renderBrowserPushState(currentState === 'active' ? 'error' : currentState)
}

async function renderBrowserPushState(state = null) {
    if (!browserPushControls) {
        return
    }

    const enableButton = browserPushControls.querySelector('[data-browser-push-enable]')
    const status = browserPushControls.querySelector('[data-browser-push-status]')
    const currentState = state || await getBrowserPushDeviceState(browserPushPreference)
    const messageKey = 'status' + currentState.split('_').map(part => {
        return part.charAt(0).toUpperCase() + part.slice(1)
    }).join('')
    status.textContent = browserPushControls.dataset[messageKey] || browserPushControls.dataset.statusError

    const preferenceValue = getPreferenceValue(browserPushPreference)
    const canEnable = ['Always', 'OpenOnly'].includes(preferenceValue)
        && !['active', 'open_only', 'denied', 'unsupported'].includes(currentState)
    enableButton.hidden = preferenceValue === 'No'
    enableButton.disabled = !canEnable
}

function getPreferenceValue(preference) {
    if (preference.type === 'checkbox') {
        return preference.checked ? 'Always' : 'No'
    }

    return preference.value
}

function setPreferenceValue(preference, value) {
    preference.dataset.browserPushPreferenceValue = value
    if (preference.type === 'checkbox') {
        preference.checked = value === 'Always'
    } else {
        preference.value = value
    }
}

function isBrowserPushPreference(preference) {
    return 'browserPushPreference' in preference.dataset
}
