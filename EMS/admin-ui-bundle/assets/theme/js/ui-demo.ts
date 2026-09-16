import { Toast, Tooltip, Popover } from 'bootstrap';

export function initUiDemo(): void {
  const toastBtn = document.getElementById('toastBtn');
  const toastStack = document.getElementById('toastStack');

  if (toastBtn && toastStack) {
    let toastCount = 0;
    toastBtn.addEventListener('click', () => {
      toastCount += 1;
      const el = document.createElement('div');
      el.className = 'toast align-items-center border-0 text-bg-success';
      el.setAttribute('role', 'alert');
      el.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">Revisie ${toastCount} opgeslagen.</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Sluiten"></button>
        </div>`;
      toastStack.appendChild(el);
      const toast = new Toast(el, { delay: 4000 });
      el.addEventListener('hidden.bs.toast', () => el.remove());
      toast.show();
    });
  }

  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new Tooltip(el));
  document.querySelectorAll('[data-bs-toggle="popover"]').forEach((el) => new Popover(el));
}
