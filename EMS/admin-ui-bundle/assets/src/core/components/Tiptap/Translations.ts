import type { TranslationKey } from './Translation/EN'

import EN from './Translation/EN'
import NL from './Translation/NL'
import FR from './Translation/FR'
import DE from './Translation/DE'

export type { TranslationKey }

const Translations = { EN, NL, FR, DE } as const satisfies Record<
    string,
    Record<TranslationKey, string>
>

export type Locale = keyof typeof Translations

export const isTransLocale = (v: string): v is Locale => v in Translations

export const trans = (
    locale: Locale,
    key: TranslationKey,
    overrides?: Record<string, Record<string, string>>
): string => {
    const override = overrides?.[locale]?.[key]

    return override ?? Translations[locale][key] ?? Translations.EN[key] ?? key
}
