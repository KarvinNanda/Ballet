'use strict';

/**
 * Add / Update transaction. The server is the source of truth: this script only fills Price from the chosen class,
 * previews the total with the same rule as App\Support\Discount::total(), and confirms the "apply to all" checkbox.
 */
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form[data-transaction-form]');
  if (!form) return;

  const price = form.querySelector('[data-price-input]');
  const classSelect = form.querySelector('select[data-class-select]');
  if (classSelect && price) {
    classSelect.addEventListener('change', () => {
      const option = classSelect.selectedOptions[0];
      if (option && option.dataset.price !== undefined) {
        price.value = option.dataset.price;
        price.dispatchEvent(new Event('input'));
      }
    });
  }

  const discount = form.querySelector('[data-discount-input]');
  const output = form.querySelector('output[data-total-output]');
  if (price && discount && output) {
    const render = () => { output.value = formatTotal(discountedTotal(price.value, discount.value)); };
    price.addEventListener('input', render);
    discount.addEventListener('input', render);
  }

  const applyAll = form.querySelector('input[data-apply-all]');
  if (applyAll) {
    form.addEventListener('submit', (event) => {
      if (applyAll.checked && !window.confirm(applyAll.dataset.confirmMessage)) {
        event.preventDefault();
        event.stopImmediatePropagation();
      }
    });
  }
});

const DISCOUNT_PATTERN = /^(\d{1,10}|(100|\d{1,2})%)$/;

/** Mirrors App\Support\Discount::total(); null means the discount is not valid for this price. */
function discountedTotal(priceText, discountText) {
  const price = Number.parseInt(priceText, 10);
  if (!Number.isFinite(price) || price < 0) return null;

  const discount = String(discountText).trim();
  if (discount === '' || discount === '0') return price;
  if (!DISCOUNT_PATTERN.test(discount)) return null;
  if (discount.endsWith('%')) return price - Math.round((price * Number.parseInt(discount, 10)) / 100);

  const amount = Number.parseInt(discount, 10);
  return amount <= price ? price - amount : null;
}

function formatTotal(total) {
  return total === null ? 'Invalid discount' : 'Rp' + total.toLocaleString('en-US');
}
