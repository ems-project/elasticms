import type { TranslationKey } from './translation/en'

import en from './translation/en'
import nl from './translation/nl'
import fr from './translation/fr'
import de from './translation/de'

export type { TranslationKey }

const translations = { en, nl, fr, de } as const satisfies Record<
    string,
    Record<TranslationKey, string>
>

export type Locale = keyof typeof translations

export const isTransLocale = (v: string): v is Locale => v in translations

export const trans = (
    locale: Locale,
    key: TranslationKey,
    overrides?: Record<string, Record<string, string>>
): string => {
    const override = overrides?.[locale]?.[key]

    return override ?? translations[locale][key] ?? translations.en[key] ?? key
}
