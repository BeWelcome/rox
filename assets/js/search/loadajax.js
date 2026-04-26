document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.ajaxload').forEach(element => {
        element.addEventListener('click', Search.loadContent);
    });
});

const Search = {
    loadContent: function (e) {
        e.preventDefault();
        document.getElementById('overlay').classList.add("loading");
        let url = this.getAttribute('href');
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(data => {
            let searchResults = document.getElementById('searchresults');
            if (searchResults) {
                searchResults.outerHTML = data;
            }
            document.getElementById('overlay').classList.remove("loading");
            document.querySelectorAll(".ajaxload").forEach(element => {
                element.addEventListener('click', Search.loadContent);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('overlay').classList.remove("loading");
        });
    }
};
