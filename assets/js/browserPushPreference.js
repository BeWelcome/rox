const BROWSER_PUSH_TIMEOUT_MS = 5000
const BROWSER_PUSH_RECONCILED_KEY_PREFIX = 'bewelcomeBrowserPushReconciled:'

export async function requestBrowserPushPermission(element) {
    if (!isBrowserPushAvailable(element)) {
        return false
    }

    return await requestBrowserNotificationPermission(element)
}

export async function requestBrowserNotificationPermission(element) {
    if (!isBrowserNotificationAvailable(element)) {
        return false
    }
    if (Notification.permission === 'denied') {
        return false
    }
    if (Notification.permission === 'granted') {
        return true
    }

    return await requestNotificationPermission() === 'granted'
}

export async function handleBrowserPushPreferenceChange(element, permissionPromise = null) {
    const value = getBrowserPushPreferenceValue(element)
    element.dataset.browserPushPreferenceValue = value
    removeSessionValue(BROWSER_PUSH_RECONCILED_KEY_PREFIX + (element.dataset.memberId || ''))
    if (value !== 'Always') {
        await removeCurrentBrowserSubscription(element, true)
        const permissionGranted = value !== 'OpenOnly'
            || await (permissionPromise || requestBrowserNotificationPermission(element))
        dispatchPreferenceChange(value)

        return permissionGranted
    }

    const enabled = await enableBrowserPushForCurrentBrowser(element, permissionPromise)
    dispatchPreferenceChange(value)

    return enabled
}

export async function enableBrowserPushForCurrentBrowser(element, permissionPromise = null) {
    const enabled = null !== await maybeSubscribeCurrentBrowser(element, permissionPromise)
    if (enabled) {
        markBrowserPushReconciled(element)
    }

    return enabled
}

export async function getBrowserPushDeviceState(element) {
    const value = getBrowserPushPreferenceValue(element)
    if (value === 'No') {
        return 'off'
    }
    if (!isBrowserNotificationAvailable(element)) {
        return 'unsupported'
    }
    if (Notification.permission === 'denied') {
        return 'denied'
    }
    if (value === 'OpenOnly') {
        return Notification.permission === 'granted' ? 'open_only' : 'inactive'
    }
    if (!supportsBrowserPush()) {
        return 'unsupported'
    }
    if (Notification.permission !== 'granted') {
        return 'inactive'
    }

    try {
        const registration = await getServiceWorkerRegistration()
        if (!registration) {
            return 'error'
        }
        const subscription = await registration.pushManager.getSubscription()
        if (!subscription) {
            return 'inactive'
        }
        const applicationServerKey = urlBase64ToUint8Array(element.dataset.publicKey)

        return applicationServerKeyMatches(subscription, applicationServerKey) ? 'active' : 'inactive'
    } catch (error) {
        return 'error'
    }
}

export function initBrowserPushSession() {
    const element = document.querySelector('[data-browser-push-session]')
    if (!element) {
        return
    }

    element.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button > 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return
        }

        event.preventDefault()
        const memberId = element.dataset.memberId || ''
        removeSessionValue(BROWSER_PUSH_RECONCILED_KEY_PREFIX + memberId)
        window.dispatchEvent(new CustomEvent('browser-push-session-ending', {
            detail: { memberId },
        }))
        withTimeout(removeCurrentBrowserSubscription(element, true), BROWSER_PUSH_TIMEOUT_MS).finally(() => {
            window.location.assign(element.href)
        })
    })

    reconcileCurrentBrowserSubscription(element)
}

async function reconcileCurrentBrowserSubscription(element) {
    const memberId = element.dataset.memberId || ''
    const reconciliationState = getBrowserPushReconciliationState(element)
    if (!memberId) {
        return
    }

    try {
        const registration = await getServiceWorkerRegistration()
        if (!registration) {
            return
        }

        const subscription = await registration.pushManager.getSubscription()
        const shouldHaveSubscription =
            element.dataset.browserPushPreferenceValue === 'Always'
            && isBrowserPushAvailable(element)
            && Notification.permission === 'granted'
        if (
            getSessionValue(BROWSER_PUSH_RECONCILED_KEY_PREFIX + memberId) === reconciliationState
            && browserPushSubscriptionMatchesExpectedState(element, subscription, shouldHaveSubscription)
        ) {
            return
        }

        if (shouldHaveSubscription) {
            await createOrUpdateBrowserPushSubscription(element, registration)
        } else if (subscription) {
            await removeSubscription(element, subscription)
        }
        setSessionValue(BROWSER_PUSH_RECONCILED_KEY_PREFIX + memberId, reconciliationState)
    } catch (error) {
        // The explicit preference control remains available for retry.
    }
}

function browserPushSubscriptionMatchesExpectedState(element, subscription, shouldHaveSubscription) {
    if (!shouldHaveSubscription) {
        return !subscription
    }
    if (!subscription) {
        return false
    }

    return applicationServerKeyMatches(subscription, urlBase64ToUint8Array(element.dataset.publicKey))
}

function markBrowserPushReconciled(element) {
    const memberId = element.dataset.memberId || ''
    if (memberId) {
        setSessionValue(
            BROWSER_PUSH_RECONCILED_KEY_PREFIX + memberId,
            getBrowserPushReconciliationState(element),
        )
    }
}

function getBrowserPushReconciliationState(element) {
    return `${element.dataset.browserPushPreferenceValue || 'No'}:${element.dataset.publicKey || ''}`
}

async function maybeSubscribeCurrentBrowser(element, permissionPromise = null) {
    if (!shouldTryBrowserPush(element)) {
        return null
    }

    try {
        const hasPermission = permissionPromise ? await permissionPromise : await requestBrowserPushPermission(element)
        if (!hasPermission) {
            return null
        }

        const registration = await getServiceWorkerRegistration()
        if (!registration) {
            return null
        }

        return await createOrUpdateBrowserPushSubscription(element, registration)
    } catch (error) {
        return null
    }
}

async function removeCurrentBrowserSubscription(element, removeFromServer = false) {
    if (!supportsBrowserPush()) {
        return
    }

    try {
        const registration = await getServiceWorkerRegistration()
        if (!registration) {
            return
        }
        const subscription = await registration.pushManager.getSubscription()
        if (!subscription) {
            return
        }

        if (removeFromServer) {
            await removeSubscription(element, subscription)
        } else {
            await subscription.unsubscribe()
        }
    } catch (error) {
        // Server-side preference cleanup still prevents future sends for this account.
    }
}

async function removeSubscription(element, subscription) {
    const results = await Promise.allSettled([
        deleteBrowserPushSubscription(element, subscription),
        unsubscribeBrowserPushSubscription(subscription),
    ])
    if (results.every(result => result.status === 'rejected')) {
        throw results[0].reason
    }
}

async function unsubscribeBrowserPushSubscription(subscription) {
    if (!await subscription.unsubscribe()) {
        throw new Error('Browser push subscription could not be removed')
    }
}

function shouldTryBrowserPush(element) {
    return isBrowserPushAvailable(element)
        && element.dataset.browserPushPreferenceValue === 'Always'
        && Notification.permission !== 'denied'
}

function isBrowserPushAvailable(element) {
    return isBrowserNotificationAvailable(element) && supportsBrowserPush()
}

function supportsBrowserPush() {
    return 'serviceWorker' in navigator && 'PushManager' in window
}

function isBrowserNotificationAvailable(element) {
    return element
        && element.dataset.browserPushConfigured === '1'
        && 'Notification' in window
        && window.isSecureContext
}

function getBrowserPushPreferenceValue(element) {
    if (element.type === 'checkbox') {
        return element.checked ? 'Always' : 'No'
    }

    return element.value || element.dataset.browserPushPreferenceValue || 'No'
}

async function createOrUpdateBrowserPushSubscription(element, registration) {
    const applicationServerKey = urlBase64ToUint8Array(element.dataset.publicKey)
    let subscription = await registration.pushManager.getSubscription()
    if (subscription && !applicationServerKeyMatches(subscription, applicationServerKey)) {
        await removeSubscription(element, subscription)
        subscription = null
    }
    if (!subscription) {
        subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey,
        })
    }
    await requestJson(element.dataset.subscribeUrl, 'POST', {
        ...subscription.toJSON(),
        contentEncoding: 'aes128gcm',
    }, element.dataset.csrfToken)

    return subscription
}

async function deleteBrowserPushSubscription(element, subscription) {
    if (!element.dataset.unsubscribeUrl) {
        throw new Error('Browser push unsubscribe URL is missing')
    }

    await requestJson(
        element.dataset.unsubscribeUrl,
        'DELETE',
        subscription.toJSON(),
        element.dataset.csrfToken,
        true,
    )
}

async function requestJson(url, method, payload, csrfToken, keepalive = false) {
    const response = await fetch(url, {
        method,
        credentials: 'same-origin',
        keepalive,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken,
        },
        body: JSON.stringify(payload),
    })

    if (!response.ok) {
        throw new Error(`Browser push request failed with ${response.status}`)
    }

    const contentType = response.headers.get('Content-Type') || ''
    if (!contentType.includes('application/json')) {
        return null
    }

    return response.json()
}

function requestNotificationPermission() {
    return new Promise((resolve) => {
        const result = Notification.requestPermission(resolve)
        if (result && 'function' === typeof result.then) {
            result.then(resolve)
        }
    })
}

function getServiceWorkerRegistration() {
    return withTimeout(navigator.serviceWorker.ready, BROWSER_PUSH_TIMEOUT_MS)
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4)
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/')
    const rawData = window.atob(base64)
    const outputArray = new Uint8Array(rawData.length)

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i)
    }

    return outputArray
}

function applicationServerKeyMatches(subscription, applicationServerKey) {
    const currentKey = subscription.options && subscription.options.applicationServerKey
    if (!currentKey) {
        return false
    }
    const currentKeyBytes = new Uint8Array(currentKey)
    if (currentKeyBytes.byteLength !== applicationServerKey.byteLength) {
        return false
    }

    for (let i = 0; i < currentKeyBytes.byteLength; i++) {
        if (currentKeyBytes[i] !== applicationServerKey[i]) {
            return false
        }
    }

    return true
}

function dispatchPreferenceChange(value) {
    window.dispatchEvent(new CustomEvent('browser-push-preference-changed', {
        detail: { value },
    }))
}

function getSessionValue(key) {
    try {
        return window.sessionStorage.getItem(key)
    } catch (error) {
        return null
    }
}

function setSessionValue(key, value) {
    try {
        window.sessionStorage.setItem(key, value)
    } catch (error) {
        // Reconciliation will run again on the next page when storage is unavailable.
    }
}

function removeSessionValue(key) {
    try {
        window.sessionStorage.removeItem(key)
    } catch (error) {
        // Reconciliation will run after the next full browser session.
    }
}

function withTimeout(promise, timeoutMs) {
    return Promise.race([
        promise,
        new Promise((resolve) => {
            window.setTimeout(() => resolve(null), timeoutMs)
        }),
    ])
}
