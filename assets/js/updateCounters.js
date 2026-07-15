function updateCount() {
    fetch('/count/conversations/unread', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        const conversationCount = document.getElementById('conversationCount');
        if (conversationCount && data && data.html !== undefined) {
            conversationCount.outerHTML = data.html;
            if (typeof window.autocollapse_menu === "function") {
                window.autocollapse_menu(true);
            }
        }
    })
    .catch(error => {
        console.error('Error fetching unread count:', error);
    });
}

const interval = setInterval(function () { updateCount(); }, 600000);

// Initial call
updateCount();
