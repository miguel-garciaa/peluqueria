type NavigatorWithStandalone = Navigator & { standalone?: boolean };

export function isStandaloneDisplayMode() {
  return window.matchMedia("(display-mode: standalone)").matches
    || (navigator as NavigatorWithStandalone).standalone === true;
}

export function isPhoneViewport() {
  return window.matchMedia("(max-width: 47.999rem)").matches;
}

export function isMobileAppMode() {
  return isStandaloneDisplayMode() && isPhoneViewport();
}

export function applyDisplayModeClass() {
  document.documentElement.classList.toggle("is-mobile-app", isMobileAppMode());
}
