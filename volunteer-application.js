(function () {
  const form = document.querySelector('[data-application-form]');
  if (!form) return;

  form.querySelectorAll('input[type="file"]').forEach(function (input) {
    input.addEventListener('change', function () {
      const file = input.files && input.files[0];
      const name = input.closest('.hub-upload').querySelector('[data-file-name]');
      name.textContent = file ? file.name : 'No document selected';
      input.setCustomValidity(file && file.size > Number(input.dataset.maxBytes) ? 'Each document must be 3 MB or smaller.' : '');
    });
  });

  form.addEventListener('submit', function (event) {
    const tooLarge = Array.from(form.querySelectorAll('input[type="file"]')).find(function (input) {
      return input.files && input.files[0] && input.files[0].size > Number(input.dataset.maxBytes);
    });
    if (tooLarge) {
      event.preventDefault();
      tooLarge.setCustomValidity('Each document must be 3 MB or smaller.');
      tooLarge.reportValidity();
    }
  });
}());
