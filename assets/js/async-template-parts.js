(function () {
  'use strict';

  var settings = window.dciAsyncTemplateParts || {};

  if (!settings.ajaxurl || !window.fetch) {
    return;
  }

  function initInjectedComponents(container) {
    if (!container) {
      return;
    }

    container.querySelectorAll('.carousel').forEach(function (carousel) {
      if (window.bootstrap && window.bootstrap.Carousel) {
        window.bootstrap.Carousel.getOrCreateInstance(carousel);
      }
    });

    container.querySelectorAll('[data-bs-carousel-splide]').forEach(function (carousel) {
      if (window.bootstrap && window.bootstrap.CarouselBI) {
        window.bootstrap.CarouselBI.getOrCreateInstance(carousel);
      }
    });

    if (typeof window.CustomEvent === 'function') {
      document.dispatchEvent(new CustomEvent('dci:async-template-loaded', {
        detail: { container: container }
      }));
    }
  }

  function loadTemplate(placeholder) {
    var templateKey = placeholder.getAttribute('data-template-key');

    if (!templateKey) {
      return Promise.resolve();
    }

    var body = new URLSearchParams();
    body.append('action', 'dci_load_template_part');
    body.append('template_key', templateKey);
    body.append('page_id', placeholder.getAttribute('data-page-id') || '0');

    return fetch(settings.ajaxurl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'Cache-Control': 'no-cache'
      },
      body: body.toString()
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('HTTP ' + response.status);
        }

        return response.json();
      })
      .then(function (payload) {
        if (!payload || !payload.success || !payload.data || typeof payload.data.html !== 'string') {
          throw new Error('Risposta non valida');
        }

        var wrapper = document.createElement('div');
        wrapper.innerHTML = payload.data.html;
        wrapper.className = 'dci-async-template__content';
        placeholder.replaceWith(wrapper);
        initInjectedComponents(wrapper);
      })
      .catch(function () {
        placeholder.setAttribute('aria-busy', 'false');
        placeholder.classList.add('dci-async-template--error');
        placeholder.innerHTML = '<div class="container py-5"><p class="mb-2">Non è stato possibile caricare questa sezione.</p><button class="btn btn-primary btn-sm" type="button">Riprova</button></div>';
        var retry = placeholder.querySelector('button');
        if (retry) {
          retry.addEventListener('click', function () {
            window.location.reload();
          });
        }
      });
  }

  function boot() {
    var placeholders = Array.prototype.slice.call(document.querySelectorAll('.dci-async-template[data-template-key]'));

    placeholders.forEach(function (placeholder) {
      loadTemplate(placeholder);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
}());
