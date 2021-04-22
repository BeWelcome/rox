document.getElementById('js-toggle').addEventListener('click', function (e) {
    const rawLabel = this.dataset.labelRaw;
    const htmlLabel = this.dataset.labelHtml;
    const englishTextHtml = document.getElementById('englishTextHTML');
    if (englishTextHtml.classList.contains("d-none")) {
        englishTextHtml.classList.add("d-block");
        englishTextHtml.classList.remove("d-none");
        this.innerText = rawLabel;
    } else {
        englishTextHtml.classList.add("d-none");
        englishTextHtml.classList.remove("d-block");
        this.innerText = htmlLabel;
    }
    const englishTextRaw = document.getElementById('englishTextRaw');
    if (englishTextRaw.classList.contains("d-none")) {
        englishTextRaw.classList.add("d-block");
        englishTextRaw.classList.remove("d-none");
        this.innerText = htmlLabel;
    } else {
        englishTextRaw.classList.add("d-none");
        englishTextRaw.classList.remove("d-block");
        this.innerText = rawLabel;
    }

    e.preventDefault();
});
