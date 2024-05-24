document.addEventListener('DOMContentLoaded', () => {

  const nonce_element = document.getElementById('postnonce')
  const nonce = nonce_element ? nonce_element.value : ''

  const getstate = async (target) => {
    const glyph = target.children[0]
    return glyph.classList.contains('checked');
  }
  const togglestate = async (target) => {
    const checked = await getstate(target)
    await setstate(!checked, target);
    return !checked;
  }
  const setstate = async (checked, target) => {
    const current = await getstate(target);
    if (current !== checked) {
      const glyph = target.children[0]
      if (checked) {
        target.classList.add('checked')
        glyph.classList.add('checked')
        glyph.innerText = target.dataset.checked
      } else {
        target.classList.remove('checked')
        glyph.classList.remove('checked')
        glyph.innerText = ''
      }
      await vote_server(checked, target);
    }
    return current;
  }
  const vote_server = async (state, target) => {
    const endpoint = target.dataset.url
    const checked = state ? 'checked' : 'unchecked'
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

  const checkdivs = document.querySelectorAll("div.personal_opinion_widget div.votes > div.check")
  for (const checkdiv of checkdivs) {

    /**
     * When checking one box, clear its sibling.
     */
    checkdiv.addEventListener('click', async (e) => {
      const target = e.currentTarget;
      const checked = await togglestate(target)
      if (checked) {
        const otherdivs = target.parentElement.parentElement.querySelectorAll("div.votes > div.check")
        for (const otherdiv of otherdivs) {
          if (checkdiv !== otherdiv) {
            await setstate(false, otherdiv)
          }
        }
      }
    })
  }

})
