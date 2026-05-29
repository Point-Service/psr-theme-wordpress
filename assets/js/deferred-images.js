(function () {
  'use strict';

  function loadDeferredImages() {
    document.querySelectorAll('img[data-dci-src]').forEach(function (image) {
      var src = image.getAttribute('data-dci-src');
      if (!src) {
        return;
      }

      image.setAttribute('src', src);
      image.removeAttribute('data-dci-src');
      image.classList.add('dci-deferred-img-loaded');
    });
  }

  if (document.readyState === 'complete') {
    loadDeferredImages();
  } else {
    window.addEventListener('load', loadDeferredImages, { once: true });
  }
}());
