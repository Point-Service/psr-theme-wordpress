(function () {
  'use strict';

  var body = document.body;
  var nav = document.getElementById('header-nav-wrapper');

  if (!body || !nav) {
    return;
  }

  var placeholder = document.createElement('div');
  var navOffsetTop = 0;
  var navHeight = 0;
  var ticking = false;

  placeholder.className = 'dci-sticky-header-nav-placeholder';
  nav.parentNode.insertBefore(placeholder, nav.nextSibling);

  function getAdminBarOffset() {
    var adminBar = document.getElementById('wpadminbar');

    if (!adminBar || window.getComputedStyle(adminBar).position !== 'fixed') {
      return 0;
    }

    return adminBar.offsetHeight || 0;
  }

  function setStickyState(shouldStick) {
    body.classList.toggle('dci-sticky-header-nav-active', shouldStick);
    placeholder.style.height = shouldStick ? navHeight + 'px' : '0px';
    document.documentElement.style.setProperty('--dci-sticky-header-nav-height', shouldStick ? navHeight + 'px' : '0px');
    document.documentElement.style.setProperty('--dci-sticky-header-nav-top', getAdminBarOffset() + 'px');
  }

  function measure() {
    var wasSticky = body.classList.contains('dci-sticky-header-nav-active');

    if (wasSticky) {
      setStickyState(false);
    }

    var rect = nav.getBoundingClientRect();
    navOffsetTop = rect.top + window.pageYOffset;
    navHeight = nav.offsetHeight || rect.height || 0;

    update();
  }

  function update() {
    var shouldStick = window.pageYOffset > navOffsetTop - getAdminBarOffset();
    setStickyState(shouldStick);
    ticking = false;
  }

  function requestUpdate() {
    if (!ticking) {
      window.requestAnimationFrame(update);
      ticking = true;
    }
  }

  window.addEventListener('scroll', requestUpdate, { passive: true });
  window.addEventListener('resize', measure);
  window.addEventListener('orientationchange', measure);
  window.addEventListener('load', measure);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', measure);
  } else {
    measure();
  }
}());
