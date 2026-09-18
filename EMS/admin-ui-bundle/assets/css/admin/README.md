# css/admin

This is the real production admin stylesheet (Bootstrap 5 + overrides),
imported by `assets/css/app.scss` → `assets/src/app.js`. It's what every
live admin page actually loads.

```
variables/_app.scss   Sass variables (compile-time): Bootstrap overrides,
                       sidebar/navbar tokens, $theme-colors map,
                       $theme-colors-supported (see below)
mixins/                small reusable Sass mixins
components/            grouped by UI area - see conventions below
utilities/             small utility classes (cursors, sizing)
vendor/                overrides for 3rd-party CSS (flatpickr, simplebar)
```

`components/_dark-mode.scss` and `components/_theme-color.scss` are
imported last in `app.scss`, so they can override any other component.

## Conventions

- Group related pieces of the app shell into one file instead of one file
  per widget - e.g. `components/_layout.scss` holds the wrapper, navbar,
  hamburger, avatar and sidebar together, split into `// ===== Name =====`
  sections. Small unrelated one-off Bootstrap tweaks also share a file
  (`_bootstrap-overrides.scss`) rather than getting a file each.
- No explanatory comments in these SCSS files - only short section headers
  where a file covers more than one area. Put the reasoning in this
  README instead.

## Theme color (sidebar/topbar branding)

The `theme_color` app config (a Twig global, e.g. `blue`) is rendered as
`data-theme="{{ theme_color }}"` on `<html>` in `base/html5.html.twig` -
for every user, always. `components/_theme-color.scss` reacts to that
attribute and colors **only** the sidebar/topbar chrome (navbar,
sidebar-brand, badges, the temporary-sidebar-toggle) - buttons, cards and
everything else always stay on the normal Bootstrap `$primary`, untouched.

Only 5 colors are actually supported: `$theme-colors-supported` in
`variables/_app.scss` (`blue`, `purple`, `green`, `red`, `yellow` - `blue`
is the config default). That list is the single source of truth; the dev
panel's swatches (`elements/dev-panel.html.twig`,
`components/_dev-panel.scss`) read the same variable.

`ROLE_SUPER_ADMIN` users can override the color client-side via the dev
panel (localStorage `ems.dev.themeColor`), for previewing without touching
the config. Regular users only ever see the server-rendered value.

## Dark mode

`data-bs-theme="light"|"dark"` is set **only** on `<html>` - by the
FOUC-prevention script in `base/html5.html.twig` (before first paint) and
by `src/core/components/theme.ts` (the dev panel's Light/Dark buttons,
`ROLE_SUPER_ADMIN` only). It is never set anywhere else in the DOM.

Bootstrap's own color-mode support only auto-remaps a small core set of
variables (body color/bg, borders, forms, navbar, close button - see
`node_modules/bootstrap/scss/_root.scss`). Everything else - `.card`,
`.dropdown-menu`, `.pagination`, `.form-control`, `.stat` - hardcodes its
own local variable/color once from the light-mode Sass value and never
touches it again. Those all get an explicit dark value, centralized in
`components/_dark-mode.scss` rather than scattered across component files.

Every rule in there targets the component **directly**
(`html[data-bs-theme='dark'] .card { ... }`), not just the ancestor
(`html[data-bs-theme='dark'] { ... }`): a CSS custom property set locally
on an element always wins over one only inherited from a parent,
regardless of source order. Setting it on the ancestor alone silently does
nothing if the component redeclares that same variable on itself (which
`.card`/`.dropdown-menu`/etc. all do).

## Why not Sass variables for any of this

Dark mode and the theme color override are both **runtime** choices (the
user picks Light/Dark or previews a color without a page reload), so they
have to be CSS custom properties (`--bs-*`, `--skin-*`) that change value
based on an attribute on `<html>`. A Sass variable is fixed at build time
and can't react to that.
