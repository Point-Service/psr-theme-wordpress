(function () {
  const content = document.querySelector('.section-wrapper');
  if (!content) return;

  const steps = Array.from(content.querySelectorAll('[data-steps]'));
  const btnNext = content.querySelector('.btn-next-step');
  const btnBack = content.querySelector('.btn-back-step');
  const alertMessage = document.getElementById('alert-message');

  let currentStep = 1;

  const getProgressNode = () =>
    document.querySelector(`[data-progress="${currentStep}"]`);

  const hideStep = (stepNumber) => {
    const node = content.querySelector(`[data-steps="${stepNumber}"]`);
    if (!node) return;
    node.classList.add('d-none');
    node.classList.remove('active');
  };

  const showStep = (stepNumber) => {
    const node = content.querySelector(`[data-steps="${stepNumber}"]`);
    if (!node) return;
    node.classList.remove('d-none');
    node.classList.add('active');
  };

  const updateProgress = () => {
    document
      .querySelectorAll('[data-progress]')
      .forEach((node) => node.classList.add('d-none'));

    const progress = getProgressNode();
    if (progress) progress.classList.remove('d-none');
  };

  const validateEmail = (email) => {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
  };

  const checkMandatoryFields = () => {
    if (!btnNext) return;

    switch (currentStep) {
      case 1: {
        btnNext.disabled = !document.getElementById('privacy')?.checked;
        break;
      }
      case 2: {
        const place = document.getElementById('luogo-disservizio')?.value;
        const type = document.getElementById('motivo-appuntamento')?.value;
        const title = document.getElementById('title')?.value?.trim();
        const details = document.getElementById('details')?.value?.trim();
        btnNext.disabled = !(place && type && title && details);
        break;
      }
      case 3: {
        // Step 3 nel template storico è informativo: consenti avanzamento.
        btnNext.disabled = false;
        break;
      }
      case 4: {
        const name = document.getElementById('name')?.value?.trim();
        const surname = document.getElementById('surname')?.value?.trim();
        const email = document.getElementById('email')?.value?.trim();
        btnNext.disabled = !(name && surname && email && validateEmail(email));
        break;
      }
      default:
        btnNext.disabled = false;
    }
  };

  const submitReport = async () => {
    const placeSelect = document.getElementById('luogo-disservizio');
    const serviceSelect = document.getElementById('motivo-appuntamento');
    const title = document.getElementById('title')?.value?.trim() || '';
    const details = document.getElementById('details')?.value?.trim() || '';
    const name = document.getElementById('name')?.value?.trim() || '';
    const surname = document.getElementById('surname')?.value?.trim() || '';
    const email = document.getElementById('email')?.value?.trim() || '';

    const selectedPlace = placeSelect?.options?.[placeSelect.selectedIndex]?.text || '';
    const selectedService = serviceSelect?.options?.[serviceSelect.selectedIndex]?.text || '';

    const fullDetails = [
      `Titolo: ${title}`,
      `Luogo: ${selectedPlace}`,
      `Tipologia disservizio: ${selectedService}`,
      `Dettagli: ${details}`,
    ].join('\n');

    const url = Array.isArray(urlConfirm) ? urlConfirm[0] : urlConfirm;
    const body = new URLSearchParams({
      action: 'save_richiesta_assistenza',
      nome: name,
      cognome: surname,
      email,
      categoria_servizio: '',
      servizio: selectedService,
      dettagli: fullDetails,
      privacyChecked: document.getElementById('privacy')?.checked ? 'true' : 'false',
    });

    const response = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Cache-Control': 'no-cache',
      },
      body,
    });

    if (!response.ok) {
      throw new Error(`HTTP error ${response.status}`);
    }

    const result = await response.json();
    if (!result?.success) {
      throw new Error('Salvataggio segnalazione non riuscito');
    }

    document.getElementById('form-steps')?.classList.add('d-none');
    if (alertMessage) alertMessage.classList.remove('d-none');

    const mainContainer = document.querySelector('#main-container');
    if (mainContainer) mainContainer.scrollIntoView({ behavior: 'smooth' });
  };

  const openNext = async () => {
    if (!btnBack || !btnNext) return;

    if (currentStep >= steps.length) {
      try {
        btnNext.disabled = true;
        await submitReport();
      } catch (err) {
        console.error(err);
        btnNext.disabled = false;
      }
      return;
    }

    hideStep(currentStep);
    currentStep += 1;
    showStep(currentStep);

    btnBack.disabled = currentStep === 1;
    btnNext.querySelector('span').innerHTML =
      currentStep === steps.length ? 'Invia' : 'Avanti';

    updateProgress();
    checkMandatoryFields();
  };

  const backPrevious = () => {
    if (!btnBack || !btnNext || currentStep === 1) return;

    hideStep(currentStep);
    currentStep -= 1;
    showStep(currentStep);

    btnBack.disabled = currentStep === 1;
    btnNext.querySelector('span').innerHTML = 'Avanti';

    updateProgress();
    checkMandatoryFields();
  };

  // Bind events
  btnNext?.addEventListener('click', openNext);
  btnBack?.addEventListener('click', backPrevious);

  [
    'privacy',
    'luogo-disservizio',
    'motivo-appuntamento',
    'title',
    'details',
    'name',
    'surname',
    'email',
  ].forEach((id) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener(el.tagName === 'SELECT' || el.type === 'checkbox' ? 'change' : 'input', checkMandatoryFields);
  });

  updateProgress();
  checkMandatoryFields();
})();
