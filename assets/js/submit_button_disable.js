export function disableButtonOnSubmit() {
    const forms = document.getElementsByTagName("form");

    for (const form of forms) {
        form.addEventListener("submit", disableButtons);
    }
}

function disableButtons()
{
    Array
        .from(document.getElementsByTagName("button"))
        .forEach(b => {
            b.classList.add('disabled')
            b.classList.add('u-pointer-events-none')
        });
}
