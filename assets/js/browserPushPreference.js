const BROWSER_PUSH_SUBSCRIBE_TIMEOUT_MS = 5000

export function initBrowserPushTriggers() {
    const config = document.querySelector('[data-browser-push-trigger-config]')
    if (!config) {
        return
    }

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.browserPushPromptHandled === '1') {
                delete form.dataset.browserPushPromptHandled
                return
            }
            if (!shouldTryBrowserPush(config)) {
                return
            }
            if ('function' !== typeof form.requestSubmit) {
                return
            }

            event.preventDefault()
            event.stopImmediatePropagation()

            const submitter = event.submitter instanceof HTMLElement ? event.submitter : null
            withTimeout(maybeSubscribeCurrentBrowser(config), BROWSER_PUSH_SUBSCRIBE_TIMEOUT_MS).finally(() => {
                form.dataset.browserPushPromptHandled = '1'
                if (submitter && submitter.form === form) {
                    form.requestSubmit(submitter)
                } else {
                    form.requestSubmit()
                }
            })
        })
    })
}

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
    if (value !== 'Always') {
        await unsubscribeCurrentBrowser(element)
        if (value === 'OpenOnly') {
            await (permissionPromise || requestBrowserNotificationPermission(element))
        }
        return
    }

    await maybeSubscribeCurrentBrowser(element, permissionPromise)
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

        const registration = await withTimeout(navigator.serviceWorker.ready, BROWSER_PUSH_SUBSCRIBE_TIMEOUT_MS)
        if (!registration) {
            return null
        }

        return await createOrUpdateBrowserPushSubscription(element, registration)
    } catch (error) {
        return null
    }
}

async function unsubscribeCurrentBrowser(element) {
    if (!isBrowserPushAvailable(element)) {
        return
    }

    try {
        const registration = await withTimeout(navigator.serviceWorker.ready, BROWSER_PUSH_SUBSCRIBE_TIMEOUT_MS)
        if (!registration) {
            return
        }
        const subscription = await registration.pushManager.getSubscription()
        if (subscription) {
            await subscription.unsubscribe()
        }
    } catch (error) {
        // The server-side preference change already removed subscriptions.
    }
}

function shouldTryBrowserPush(element) {
    return isBrowserPushAvailable(element)
        && element.dataset.browserPushPreferenceValue === 'Always'
        && Notification.permission !== 'denied'
}

function isBrowserPushAvailable(element) {
    return isBrowserNotificationAvailable(element)
        && 'serviceWorker' in navigator
        && 'PushManager' in window
}

function isBrowserNotificationAvailable(element) {
    return element
        && element.dataset.browserPushConfigured === '1'
        && 'Notification' in window
        && window.isSecureContext
}

function getBrowserPushPreferenceValue(element) {
    if (element.type === 'checkbox') {
        return element.checked ? 'No' : 'Always'
    }

    return element.value || element.dataset.browserPushPreferenceValue || 'No'
}

async function createOrUpdateBrowserPushSubscription(element, registration) {
    const applicationServerKey = urlBase64ToUint8Array(element.dataset.publicKey)
    let subscription = await registration.pushManager.getSubscription()
    if (subscription && !applicationServerKeyMatches(subscription, applicationServerKey)) {
        await subscription.unsubscribe()
        subscription = null
    }
    if (!subscription) {
        subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey,
        })
    }
    await postBrowserPushSubscription(element, subscription)

    return subscription
}

async function postBrowserPushSubscription(element, subscription) {
    await postJson(element.dataset.subscribeUrl, {
        ...subscription.toJSON(),
        contentEncoding: 'aes128gcm',
    }, element.dataset.csrfToken, [409])
}

async function postJson(url, payload, csrfToken, acceptedStatuses = []) {
    const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken,
        },
        body: JSON.stringify(payload),
    })

    if (!response.ok && !acceptedStatuses.includes(response.status)) {
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

function withTimeout(promise, timeoutMs) {
    return Promise.race([
        promise,
        new Promise((resolve) => {
            window.setTimeout(() => resolve(null), timeoutMs)
        }),
    ])
}
