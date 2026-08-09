const form = document.querySelector('#rsvp-form');

if (form) {
  const attendingFields = form.querySelector('[data-attending]');
  const status = form.querySelector('#form-status');

  form.addEventListener('change', (event) => {
    if (event.target.name === 'ucast') {
      attendingFields.hidden = event.target.value === 'nepridem';
    }
    if (event.target.name === 'alkohol[]' && event.target.value === 'nepijem' && event.target.checked) {
      form.querySelectorAll('input[name="alkohol[]"]:not([value="nepijem"])').forEach((input) => { input.checked = false; });
    }
    if (event.target.name === 'alkohol[]' && event.target.value !== 'nepijem' && event.target.checked) {
      form.querySelector('input[name="alkohol[]"][value="nepijem"]').checked = false;
    }
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    status.textContent = 'Zapisujem…';
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;

    try {
      const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } });
      const result = await response.json();
      status.textContent = result.message;
      status.className = `mt-3 ${response.ok ? 'text-success' : 'text-danger'}`;
    } catch {
      status.textContent = 'Spojenie zlyhalo. Skús to, prosím, ešte raz.';
      status.className = 'mt-3 text-danger';
    } finally {
      button.disabled = false;
    }
  });
}
