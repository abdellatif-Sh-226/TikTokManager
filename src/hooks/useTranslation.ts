import en from '../config/languages/en.json'
import fr from '../config/languages/fr.json'
import { useSettingsStore } from '../store'

const translations: Record<string, typeof en> = { en, fr }

export function useTranslation() {
  const lang = useSettingsStore((s) => s.language)
  return translations[lang]
}
