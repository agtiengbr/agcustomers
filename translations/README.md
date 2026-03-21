This folder contains XLF translation files for the module.

Structure:
- translations/
  - pt-BR/
    - ModulesAgcustomersAdmin.pt-BR.xlf
    - ModulesAgcustomersShop.pt-BR.xlf
  - en-US/
    - ModulesAgcustomersAdmin.en-US.xlf (placeholder)
    - ModulesAgcustomersShop.en-US.xlf (placeholder)

Notes:
- Domain names must match the PrestaShop translation domains used in code: `Modules.Agcustomers.Admin` and `Modules.Agcustomers.Shop`.
- Filenames follow the pattern: `<DomainNameWithoutDots><Locale>.xlf` inside a locale folder.
- Source language is set to `en` and targets to the locale.

How to update:
- Add new `<trans-unit>` entries for new strings.
- Keep IDs stable if possible; they can be arbitrary but must be unique within a file.
- Clear cache or "Flush cache" in BO after adding translations.
