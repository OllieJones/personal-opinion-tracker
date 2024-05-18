document.addEventListener('DOMContentLoaded', () => {
    /* Make checkboxes embedded in rows of tables work like radio buttons. */
    const checkboxes = document.querySelectorAll("div.personal_opinion_widget div.votes input[type='checkbox']")
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
