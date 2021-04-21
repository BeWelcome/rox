document.getElementById('js-toggle').addEventListener('click', function (e) {
    const englishTextHtml = document.getElementById('englishTextHTML');
    if (englishTextHtml.classList.contains("d-none")) {
        englishTextHtml.classList.add("d-block");
        englishTextHtml.classList.remove("d-none");
    } else {
        englishTextHtml.classList.add("d-none");
        englishTextHtml.classList.remove("d-block");
    }
    const englishTextRaw = document.getElementById('englishTextRaw');
    if (englishTextRaw.classList.contains("d-none")) {
        englishTextRaw.classList.add("d-block");
        englishTextRaw.classList.remove("d-none");
    } else {
        englishTextRaw.classList.add("d-none");
        englishTextRaw.classList.remove("d-block");
    }
    e.preventDefault();
});
