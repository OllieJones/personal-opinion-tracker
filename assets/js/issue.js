document.addEventListener('DOMContentLoaded', () => {
    /* shrink the tinyMCE editor */
    const poll = setInterval(() => {
        const editor = document.querySelector('iframe#content_ifr')
        if (editor) {
            editor.style.height = '150px'
            clearInterval(poll)
        }
    }, 20)

    /* Make checkboxes embedded in rows of tables work like radio buttons. */
    const checkboxes = document.querySelectorAll("table.issue > tbody > tr > td > input[type='checkbox']")
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', (e) => {
            const target = e.target
            if (target.checked) {
                target
                    .parentElement
                    .parentElement
                    .querySelectorAll("input[type='checkbox']")
                    .forEach(siblingCheckbox => {
                        if (checkbox !== siblingCheckbox) {
                            siblingCheckbox.checked = false
                        }
                    })
            }
        })
    })
})
