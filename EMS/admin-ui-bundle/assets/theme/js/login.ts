export function initLogin(): void {
  const loginPage = document.getElementById('loginPage');
  const loginUser = document.getElementById('loginUser') as HTMLInputElement | null;
  const loginPass = document.getElementById('loginPass') as HTMLInputElement | null;
  const loginSubmit = document.getElementById('loginSubmit');
  const ssoBtn = document.getElementById('ssoBtn');
  const togglePass = document.getElementById('togglePass');

  if (!loginPage || !loginSubmit || !ssoBtn || !togglePass || !loginPass) {
    return;
  }

  function showLogin(): void {
    loginPage!.classList.add('active');
    loginUser?.focus();
  }

  function hideLogin(): void {
    loginPage!.classList.remove('active');
  }

  loginSubmit.addEventListener('click', hideLogin);
  ssoBtn.addEventListener('click', hideLogin);

  document.querySelectorAll<HTMLAnchorElement>('[data-action="logout"]').forEach((el) => {
    el.addEventListener('click', (event) => {
      event.preventDefault();
      showLogin();
    });
  });

  loginPage.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' || event.key === 'Escape') {
      hideLogin();
    }
  });

  togglePass.addEventListener('click', () => {
    const hidden = loginPass.type === 'password';
    loginPass.type = hidden ? 'text' : 'password';
    togglePass.innerHTML = hidden
      ? '<i class="fa-solid fa-eye-slash"></i>'
      : '<i class="fa-solid fa-eye"></i>';
  });
}
