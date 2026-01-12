import MicroModal from 'micromodal';

const editLanguages = document.querySelectorAll("[data-edit-language]");

editLanguages.forEach(editLanguage => {
    editLanguage.addEventListener("click", e => {
        editLanguages.forEach(editLanguage => {
            editLanguage.classList.add('btn-outline-primary')
            editLanguage.classList.remove('btn-primary')
        })

        const languages = document.querySelectorAll('[id^=profile-language-]')
        languages.forEach(language => {
            language.classList.add('u:hidden!')
        })

        const language = e.target.dataset.editLanguage
        const activeLanguage = document.getElementById("profile-language-" + language)
        const editLanguageButton = document.querySelector("[data-edit-language=" + language + "]")

        activeLanguage.classList.remove('u:hidden!')
        editLanguageButton.classList.add("btn-primary")
        editLanguageButton.classList.remove("btn-outline-primary")
    })
})

const deleteLanguages = document.querySelectorAll("[data-delete-language]")

deleteLanguages.forEach( deleteLanguage => {
    deleteLanguage.addEventListener('click', (e) => {
        const modalId = 'delete-' + e.target.dataset.deleteLanguage;
        MicroModal.show(modalId);
    })
})
