const member = document.getElementById('member').value
const preferences = document.querySelectorAll( '.preference')

preferences.forEach(
    async preference => {
        preference.addEventListener('change', async (event) => {
            let value = null
            if (event.target.type === 'checkbox') {
                value = event.target.checked
            } else {
                value = event.target.value
            }

            const form = new FormData();
            form.append('member', member);
            form.append('preference', preference.id.replace('preferences_', ''));
            form.append('value', value);

            await fetch("/members/update/preference", { method: 'POST', body: form })
                .then(() => { /* Nothing to do here */ })
        })
    }
)
