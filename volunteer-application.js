(function () {
  const form = document.querySelector('[data-application-form]');
  if (!form) return;

  form.querySelectorAll('select').forEach(function (select, selectIndex) {
    const label = select.closest('label');
    if (!label) return;

    const picker = document.createElement('div');
    picker.className = 'hub-select';
    const trigger = document.createElement('button');
    const labelId = 'hub-select-label-' + selectIndex;
    const valueId = 'hub-select-value-' + selectIndex;
    const menuId = 'hub-select-options-' + selectIndex;
    label.id = labelId;

    trigger.type = 'button';
    trigger.className = 'hub-select__trigger';
    trigger.setAttribute('role', 'combobox');
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.setAttribute('aria-controls', menuId);
    trigger.setAttribute('aria-labelledby', labelId + ' ' + valueId);

    const value = document.createElement('span');
    value.className = 'hub-select__value';
    value.id = valueId;
    const selectedOption = select.options[select.selectedIndex];
    value.textContent = selectedOption ? selectedOption.textContent.trim() : '';
    const arrow = document.createElement('span');
    arrow.className = 'material-symbols-outlined';
    arrow.setAttribute('aria-hidden', 'true');
    arrow.textContent = 'expand_more';
    trigger.append(value, arrow);

    const menu = document.createElement('div');
    menu.className = 'hub-select__menu';
    menu.id = menuId;
    menu.setAttribute('role', 'listbox');
    menu.setAttribute('aria-labelledby', labelId);
    menu.hidden = true;

    let optionIndex = 0;
    function addOption(option, parent) {
      if (!option.value) return;
      const item = document.createElement('button');
      item.type = 'button';
      item.className = 'hub-select__option';
      item.id = menuId + '-option-' + optionIndex++;
      item.setAttribute('role', 'option');
      item.setAttribute('aria-selected', String(option.value === select.value));
      item.dataset.value = option.value;
      item.textContent = option.textContent.trim();
      parent.appendChild(item);
    }

    Array.from(select.children).forEach(function (child) {
      if (child.tagName === 'OPTGROUP') {
        const group = document.createElement('div');
        group.setAttribute('role', 'group');
        group.setAttribute('aria-label', child.label);
        const heading = document.createElement('div');
        heading.className = 'hub-select__group-label';
        heading.setAttribute('role', 'presentation');
        heading.textContent = child.label;
        group.appendChild(heading);
        Array.from(child.children).forEach(function (option) { addOption(option, group); });
        menu.appendChild(group);
      } else if (child.tagName === 'OPTION') {
        addOption(child, menu);
      }
    });

    picker.append(trigger, menu);
    select.after(picker);
    select.classList.add('hub-select__native');
    select.tabIndex = -1;

    function closeMenu() {
      menu.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
    }

    function openMenu(focusLast) {
      menu.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
      const items = menu.querySelectorAll('[role="option"]');
      const selected = menu.querySelector('[aria-selected="true"]');
      const target = selected || (focusLast ? items[items.length - 1] : items[0]);
      if (target) target.focus();
    }

    function choose(item) {
      select.value = item.dataset.value;
      value.textContent = select.options[select.selectedIndex].textContent.trim();
      menu.querySelectorAll('[role="option"]').forEach(function (option) {
        option.setAttribute('aria-selected', String(option.dataset.value === select.value));
      });
      select.dispatchEvent(new Event('change', { bubbles: true }));
      closeMenu();
      trigger.focus();
    }

    menu.addEventListener('click', function (event) {
      const item = event.target.closest('[role="option"]');
      if (item) choose(item);
    });
    trigger.addEventListener('click', function () {
      if (menu.hidden) openMenu(false);
      else closeMenu();
    });
    trigger.addEventListener('keydown', function (event) {
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        openMenu(event.key === 'ArrowUp');
      }
    });
    menu.addEventListener('keydown', function (event) {
      const items = Array.from(menu.querySelectorAll('[role="option"]'));
      const currentIndex = items.indexOf(document.activeElement);
      if (event.key === 'Escape') {
        event.preventDefault();
        closeMenu();
        trigger.focus();
      } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        const direction = event.key === 'ArrowDown' ? 1 : -1;
        const next = (currentIndex + direction + items.length) % items.length;
        if (items[next]) items[next].focus();
      } else if (event.key === 'Home' || event.key === 'End') {
        event.preventDefault();
        const target = event.key === 'Home' ? items[0] : items[items.length - 1];
        if (target) target.focus();
      }
    });
    document.addEventListener('click', function (event) {
      if (!picker.contains(event.target)) closeMenu();
    });
    select.addEventListener('invalid', function (event) {
      event.preventDefault();
      openMenu(false);
    });
  });

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
