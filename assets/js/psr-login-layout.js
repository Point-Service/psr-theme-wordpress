/* PSR Login: raggruppa le utilita' native senza ricreare o inviare i form. */
(function () {
    'use strict';
    function enhance() {
        var root = document.getElementById('login');
        if (!root || root.querySelector('.psr-login-card')) { return; }
        var form = root.querySelector(':scope > form:not(.psr-otp-resend)');
        if (!form) { return; }
        // Rimuove anche dal DOM il checkbox nativo "Ricordami".
        // Il backend ignora comunque i POST che tentano di riattivarlo.
        var remember = form.querySelector('.forgetmenot');
        if (remember) { remember.remove(); }
        var card = document.createElement('section');
        card.className = 'psr-login-card';
        card.setAttribute('aria-label', 'Accesso area riservata');
        var heading = document.createElement('header');
        heading.className = 'psr-login-card-head';
        var title = document.createElement('h2');
        title.textContent = 'Accedi al portale';
        if (form.querySelector('#psr_otp')) { title.textContent = 'Verifica di sicurezza'; }
        if (form.id === 'lostpasswordform') { title.textContent = 'Recupera la password'; }
        if (form.id === 'resetpassform') { title.textContent = 'Nuova password'; }
        var subtitle = document.createElement('p');
        subtitle.textContent = 'Inserisci le tue credenziali per continuare';
        if (form.querySelector('#psr_otp')) { subtitle.textContent = 'Conferma l’accesso con il codice ricevuto via email'; }
        if (form.id === 'lostpasswordform') { subtitle.textContent = 'Segui le istruzioni per recuperare l’accesso'; }
        if (form.id === 'resetpassform') { subtitle.textContent = 'Scegli una nuova password per l’account'; }
        heading.appendChild(title);
        heading.appendChild(subtitle);
        root.insertBefore(card, form);
        card.appendChild(heading);
        card.appendChild(form);

        // Moduli OTP di reinvio rimangono attivi: li ospitiamo senza modificarli.
        var resend = root.querySelector(':scope > form.psr-otp-resend');
        if (resend) { card.appendChild(resend); }

        var utilities = document.createElement('footer');
        utilities.className = 'psr-login-utilities';
        var links = document.createElement('div');
        links.className = 'psr-login-links';
        var nav = root.querySelector(':scope > #nav');
        var back = root.querySelector(':scope > #backtoblog');
        if (nav) { links.appendChild(nav); }
        if (back) { links.appendChild(back); }
        if (links.children.length) { utilities.appendChild(links); }

        var extras = document.createElement('div');
        extras.className = 'psr-login-extras';
        var webmail = root.querySelector(':scope > .psr-login-webmail');
        if (webmail) { extras.appendChild(webmail); }
        var privacy = root.querySelector(':scope > .privacy-policy-page-link');
        if (privacy) { extras.appendChild(privacy); }
        var languages = document.querySelector('body.login > .language-switcher');
        if (languages) { extras.appendChild(languages); }
        if (extras.children.length) { utilities.appendChild(extras); }
        if (utilities.children.length) { card.appendChild(utilities); }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', enhance, { once: true });
    } else {
        enhance();
    }
})();
