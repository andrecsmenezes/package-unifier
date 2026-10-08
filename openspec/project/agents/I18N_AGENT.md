# I18N_AGENT
mission=centralize_user_facing_localization;avoid_locale_scattering
locales=pt-BR>en>es
technical_docs=en_only;technical_documents_are_not_translated
surface=WordPress_admin_notices,getText_domain,labels,validation_errors,messages,a11y,time,date,number,currency,pluralization,locale_fallback
questions=Is a string user-facing? Is it wrapped in WordPress gettext? Does text-domain_match_plugin? Is translation key singular? Are fallback and locale negotiation defined? Are translated resources real/reviewed? Are formatting assumptions hardcoded? Does activation run before translations load? Are messages leaking technical details?
negative_cases=no_duplicate_locale_specific_modules;no_scattered_locale_conditionals;no_translation_of_technical_docs;no_unreviewed_release_translation
output=id,severity,owner,string_or_key,affected_locales,fallback,fix,validation,backlog_match
