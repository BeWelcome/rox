import '../scss/faq.scss';

document.addEventListener('DOMContentLoaded', function () {
    const faqs = document.getElementById('faqs');
    if (faqs) {
        const dds = faqs.querySelectorAll('dd');
        dds.forEach(dd => dd.style.display = 'none');

        const dts = faqs.querySelectorAll('dt');
        dts.forEach(dt => {
            dt.addEventListener('click', function (e) {
                e.preventDefault();
                const nextDd = this.nextElementSibling;
                if (nextDd && nextDd.tagName === 'DD') {
                    if (nextDd.style.display === 'none') {
                        nextDd.style.display = 'block';
                    } else {
                        nextDd.style.display = 'none';
                    }
                }
                
                const icon = this.querySelector('[data-fa-i2svg]');
                if (icon) {
                    icon.classList.toggle('fa-plus-circle');
                    icon.classList.toggle('fa-minus-circle');
                }
            });
        });
    }
    
    openHash();
});

window.addEventListener('hashchange', openHash);

function openHash() {
    let hash = location.hash;
    if (hash) {
        const element = document.querySelector(hash);
        if (element) {
            element.click();
            element.scrollIntoView();
        }
    }
}
