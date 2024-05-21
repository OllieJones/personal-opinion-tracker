document.addEventListener('DOMContentLoaded', () => {

  const nonce_element = document.getElementById('postnonce')
  const nonce = nonce_element ? nonce_element.value : ''

  const vote_server = async (e) => {
    const target = e.target
    const endpoint = target.dataset.endpoint
    const checked = target.checked ? 'checked' : 'unchecked'
    const body = JSON.stringify({
      action: target.dataset.action,
      name: target.dataset.name,
      user: target.dataset.user,
      checked
    })
    target.disabled = true
    const options = {
      method: 'POST',
      body: body,
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-WP-Nonce': nonce
      },
      mode: 'same-origin',
      credentials: 'include',
      cache: 'no-cache',
      referrerPolicy: 'same-origin'
    };
    const req = new Request(endpoint, options)
    const res = await fetch(req)
    target.disabled = false
    if (res.status === 200) {
      await res.json();
    }

  }
  /* Make checkboxes embedded in rows of tables work like radio buttons. */
  const checkboxes = document.querySelectorAll("div.personal_opinion_widget div.votes input[type='checkbox']")
  checkboxes.forEach(checkbox => {
    /**
     * Push the change to the server API
     */
    checkbox.addEventListener('change', vote_server)


    /**
     * When checking one box, clear its sibling.
     */
    checkbox.addEventListener('change', async (e) => {
      const target = e.target
      if (target.checked) {
        const checkboxes = target.parentElement.parentElement.querySelectorAll("input[type='checkbox']")
        for (const siblingCheckbox of checkboxes) {
          if (checkbox !== siblingCheckbox) {
            siblingCheckbox.checked = false
            await vote_server({target: siblingCheckbox})
          }
        }
      }
    })

  })
})
