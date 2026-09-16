type ThemeMode = 'light' | 'dark';

const DEFAULT_PROJECT_NAME = 'Klantportaal';

function readStorage(key: string): string | null {
  try {
    return localStorage.getItem(key);
  } catch {
    return null;
  }
}

function writeStorage(key: string, value: string): void {
  try {
    localStorage.setItem(key, value);
  } catch {
    /* storage unavailable */
  }
}

export function initTheme(): void {
  const root = document.documentElement;

  const modeButtons = document.querySelectorAll<HTMLButtonElement>('.mode-btn');
  const skinButtons = document.querySelectorAll<HTMLButtonElement>('.skin-swatch');

  function updateModeButtons(mode: ThemeMode | null): void {
    const effective: ThemeMode =
      mode ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    modeButtons.forEach((btn) => {
      btn.classList.toggle('active', btn.dataset.mode === effective);
    });
  }

  function setMode(mode: ThemeMode): void {
    root.setAttribute('data-bs-theme', mode);
    writeStorage('adminlte-theme', mode);
    updateModeButtons(mode);
  }

  function updateSkinButtons(skin: string | null): void {
    skinButtons.forEach((btn) => {
      btn.classList.toggle('active', btn.dataset.skin === skin);
    });
  }

  function setSkin(skin: string): void {
    root.setAttribute('data-skin', skin);
    writeStorage('adminlte-skin', skin);
    updateSkinButtons(skin);
  }

  const storedTheme = readStorage('adminlte-theme');
  const effectiveTheme: ThemeMode =
    storedTheme === 'light' || storedTheme === 'dark'
      ? storedTheme
      : window.matchMedia('(prefers-color-scheme: dark)').matches
        ? 'dark'
        : 'light';
  root.setAttribute('data-bs-theme', effectiveTheme);
  updateModeButtons(effectiveTheme);

  const storedSkin = readStorage('adminlte-skin');
  if (storedSkin) {
    root.setAttribute('data-skin', storedSkin);
  }
  updateSkinButtons(root.getAttribute('data-skin'));

  modeButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const mode = btn.dataset.mode as ThemeMode | undefined;
      if (mode) {
        setMode(mode);
      }
    });
  });

  skinButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const skin = btn.dataset.skin;
      if (skin) {
        setSkin(skin);
      }
    });
  });

  const projectHeader = document.getElementById('projectNameHeader');
  const headerBrand = document.getElementById('headerBrand');
  const sidebarBrandDesktop = document.getElementById('sidebarBrandDesktop');
  const projectInput = document.getElementById('projectNameInput') as HTMLInputElement | null;

  function applyProjectName(name: string): void {
    const clean = name.trim();
    if (projectHeader) {
      projectHeader.textContent = clean ? `\u00b7 ${clean}` : '';
    }
    if (headerBrand) {
      headerBrand.textContent = clean || 'elasticMS';
    }
    if (sidebarBrandDesktop) {
      sidebarBrandDesktop.textContent = clean || 'elasticMS';
    }
  }

  if (projectInput) {
    const storedProject = readStorage('adminlte-project') ?? DEFAULT_PROJECT_NAME;
    projectInput.value = storedProject;
    applyProjectName(storedProject);

    projectInput.addEventListener('input', () => {
      applyProjectName(projectInput.value);
      writeStorage('adminlte-project', projectInput.value);
    });
  }
}
