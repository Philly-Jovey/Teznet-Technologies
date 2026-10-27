(function () {
  const phoneNumber = '256781856006';
  const floatingButton = document.createElement('a');

  floatingButton.className = 'whatsapp-float';
  floatingButton.href = 'https://wa.me/' + phoneNumber;
  floatingButton.target = '_blank';
  floatingButton.rel = 'noopener noreferrer';
  floatingButton.setAttribute('aria-label', 'Chat with Teznet Technologies on WhatsApp');
  floatingButton.setAttribute('title', 'Chat with us on WhatsApp');
  floatingButton.innerHTML = '<svg viewBox="0 0 32 32" aria-hidden="true" focusable="false"><path d="M16.04 3C8.88 3 3.06 8.82 3.06 15.98c0 2.29.6 4.52 1.75 6.49L3 29l6.7-1.76a12.9 12.9 0 0 0 6.34 1.65h.01C23.2 28.89 29 23.07 29 15.91 29 8.8 23.16 3 16.04 3Zm.01 23.7h-.01a10.7 10.7 0 0 1-5.45-1.49l-.39-.23-3.98 1.05 1.06-3.88-.25-.4a10.68 10.68 0 0 1-1.64-5.77c0-5.88 4.79-10.67 10.67-10.67 2.85 0 5.53 1.11 7.55 3.13a10.6 10.6 0 0 1 3.12 7.55c0 5.88-4.79 10.67-10.68 10.67Zm5.86-8c-.32-.16-1.89-.93-2.18-1.04-.29-.11-.5-.16-.71.16-.21.32-.82 1.04-1 1.25-.18.21-.37.24-.69.08-.32-.16-1.35-.5-2.57-1.6-.95-.85-1.6-1.9-1.78-2.22-.19-.32-.02-.5.14-.66.14-.14.32-.37.48-.55.16-.19.21-.32.32-.53.1-.21.05-.4-.03-.56-.08-.16-.71-1.72-.98-2.36-.26-.62-.52-.53-.71-.54l-.61-.01c-.21 0-.55.08-.84.4-.29.32-1.1 1.08-1.1 2.64 0 1.56 1.13 3.06 1.29 3.27.16.21 2.23 3.4 5.4 4.77.76.33 1.35.52 1.81.66.76.24 1.45.21 2 .13.61-.09 1.89-.77 2.15-1.51.27-.75.27-1.38.19-1.51-.08-.13-.29-.21-.61-.37Z"/></svg><span>WhatsApp</span>';
  document.body.appendChild(floatingButton);

  function findWhatsAppControls() {
    return Array.from(document.querySelectorAll('a[href*="wa.me"], a[href*="whatsapp"], button, [role="button"]'))
      .filter(function (element) {
        if (element === floatingButton) return false;
        const label = (element.innerText || element.getAttribute('aria-label') || '').toLowerCase();
        const href = (element.getAttribute('href') || '').toLowerCase();
        return href.includes('wa.me') || href.includes('whatsapp') || label.includes('whatsapp');
      });
  }

  function overlaps(first, second) {
    return first.left < second.right && first.right > second.left &&
      first.top < second.bottom && first.bottom > second.top;
  }

  function updateVisibility() {
    const buttonRect = floatingButton.getBoundingClientRect();
    const hasOverlap = findWhatsAppControls().some(function (control) {
      const rect = control.getBoundingClientRect();
      return rect.width > 0 && rect.height > 0 && overlaps(buttonRect, rect);
    });
    floatingButton.hidden = hasOverlap;
  }

  let updatePending = false;
  function scheduleUpdate() {
    if (updatePending) return;
    updatePending = true;
    window.requestAnimationFrame(function () {
      updatePending = false;
      updateVisibility();
    });
  }

  window.addEventListener('scroll', scheduleUpdate, { passive: true });
  window.addEventListener('resize', scheduleUpdate);
  window.addEventListener('load', scheduleUpdate);
  updateVisibility();
}());
