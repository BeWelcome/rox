import MicroModal from 'micromodal';

const editLanguages = document.querySelectorAll("[data-edit-language]");

editLanguages.forEach(editLanguage => {
    editLanguage.addEventListener("click", e => {
        editLanguages.forEach(editLanguage => {
            editLanguage.classList.add('btn-outline-primary')
            editLanguage.classList.remove('btn-primary')
            editLanguage.classList.remove('p-edit-header__lang-tab--active')
            editLanguage.setAttribute('aria-selected', 'false')
        })

        const languages = document.querySelectorAll('[id^=profile-language-]')
        languages.forEach(language => {
            language.classList.add('u:hidden!')
        })

        const language = e.currentTarget.dataset.editLanguage
        const activeLanguage = document.getElementById("profile-language-" + language)
        const editLanguageButton = e.currentTarget

        activeLanguage.classList.remove('u:hidden!')
        editLanguageButton.classList.add("btn-primary")
        editLanguageButton.classList.remove("btn-outline-primary")
        editLanguageButton.classList.add("p-edit-header__lang-tab--active")
        editLanguageButton.setAttribute('aria-selected', 'true')
    })
})

const deleteLanguages = document.querySelectorAll("[data-delete-language]")

deleteLanguages.forEach( deleteLanguage => {
    deleteLanguage.addEventListener('click', (e) => {
        const modalId = 'delete-' + e.target.dataset.deleteLanguage;
        MicroModal.show(modalId);
    })
})
