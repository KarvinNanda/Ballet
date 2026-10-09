// Shows flash toasts. Sidebar and dropdowns use Bootstrap data attributes, no custom JS.
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.toast').forEach((element) => {
    bootstrap.Toast.getOrCreateInstance(element).show();
  });

  document.querySelectorAll('.app-main table').forEach(prepareCardTable);
});

// Forms that delete or reset data ask first (data-confirm="message").
document.addEventListener('submit', (event) => {
  const message = event.target.dataset && event.target.dataset.confirm;
  if (message && !window.confirm(message)) event.preventDefault();
});

/**
 * On phones, theme.css shows each row of a .table-cards table as a card.
 * Here each cell gets its column header as data-label (colspan aware),
 * cells that only hold buttons become the card's action row,
 * and placeholder cells ("None", "-") are hidden.
 */
function prepareCardTable(table) {
  const headerRow = table.tHead && table.tHead.rows[0];
  if (!headerRow) return;

  const labels = [];
  for (const th of headerRow.cells) {
    for (let i = 0; i < th.colSpan; i++) labels.push(th.textContent.trim());
  }

  for (const body of table.tBodies) {
    for (const row of body.rows) {
      let column = 0;
      for (const cell of row.cells) {
        const isActionCell = isButtonOnly(cell);
        cell.dataset.label = row.cells.length === 1 || isActionCell ? '' : labels[column] || '';
        cell.classList.toggle('cell-actions', isActionCell);
        // theme.css sizes action cells by button count, so every button in the row gets the same width.
        if (isActionCell) cell.style.setProperty('--buttons', String(visibleButtons(cell).length));
        cell.classList.toggle('cell-empty', !isActionCell && isPlaceholder(cell));
        column += cell.colSpan;
      }
    }
  }

  table.classList.add('table-cards');
}

const PLACEHOLDERS = ['', '-', 'None'];

/** "None", "-" or blank, and no form control or link inside (attendance checkboxes have no text). */
function isPlaceholder(cell) {
  return PLACEHOLDERS.includes(cell.textContent.trim()) && cell.querySelector('input, select, textarea, a, img') === null;
}

/** Buttons shown in the cell itself; items of a dropdown menu are not counted. */
function visibleButtons(cell) {
  return Array.from(cell.querySelectorAll('button, .btn')).filter((button) => !button.closest('.dropdown-menu'));
}

/** True when the cell has buttons and no text of its own. Blade indentation whitespace and dropdown menus are ignored. */
function isButtonOnly(cell) {
  const buttons = visibleButtons(cell);
  if (buttons.length === 0) return false;

  const withoutSpaces = (text) => text.replace(/\s+/g, '');
  const buttonText = buttons.map((button) => withoutSpaces(button.textContent)).join('');
  const menuText = Array.from(cell.querySelectorAll('.dropdown-menu')).map((menu) => withoutSpaces(menu.textContent)).join('');

  return withoutSpaces(cell.textContent).length === buttonText.length + menuText.length;
}
