(function () {
  'use strict';

  var settings = window.dciAsyncTemplateParts || {};

  function fallbackToSync() {
    var paramName = settings.disableParam || 'dci_disable_async';
    var currentUrl;
    var fallbackUrl;

    try {
      currentUrl = new URL(window.location.href);
      if (currentUrl.searchParams.get(paramName) === '1') {
        return false;
      }
      currentUrl.searchParams.set(paramName, '1');
      fallbackUrl = currentUrl.toString();
    } catch (error) {
      if (window.location.search.indexOf(paramName + '=1') !== -1) {
        return false;
      }
      fallbackUrl = window.location.href + (window.location.search ? '&' : '?') + encodeURIComponent(paramName) + '=1';
    }

    window.location.replace(fallbackUrl);
    return true;
  }

  function getAjaxUrl() {
    var ajaxUrl;

    try {
      ajaxUrl = new URL(settings.ajaxurl, window.location.href);
    } catch (error) {
      return settings.ajaxurl;
    }

    if (window.location.protocol === 'https:' && ajaxUrl.protocol === 'http:' && ajaxUrl.host === window.location.host) {
      ajaxUrl.protocol = 'https:';
    }

    return ajaxUrl.toString();
  }

  if (!settings.ajaxurl || !window.fetch) {
    fallbackToSync();
    return;
  }

  function executeInjectedScripts(container) {
    container.querySelectorAll('script').forEach(function (script) {
      var clone = document.createElement('script');

      Array.prototype.slice.call(script.attributes).forEach(function (attribute) {
        clone.setAttribute(attribute.name, attribute.value);
      });

      clone.text = script.text;
      script.parentNode.replaceChild(clone, script);
    });
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
    body.append('query_string', window.location.search.replace(/^\?/, ''));
    body.append('term_id', placeholder.getAttribute('data-term-id') || '0');
    body.append('taxonomy', placeholder.getAttribute('data-taxonomy') || '');

    return fetch(getAjaxUrl(), {
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
        executeInjectedScripts(wrapper);
        initInjectedComponents(wrapper);
      })
      .catch(function () {
        if (fallbackToSync()) {
          return;
        }

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
    var maxConcurrent = parseInt(settings.maxConcurrent, 10) || 3;
    var active = 0;
    var index = 0;

    function runNext() {
      if (index >= placeholders.length || active >= maxConcurrent) {
        return;
      }

      active += 1;
      loadTemplate(placeholders[index]).then(function () {
        active -= 1;
        runNext();
      }, function () {
        active -= 1;
        runNext();
      });
      index += 1;
      runNext();
    }

    runNext();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
}());
