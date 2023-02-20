<?php

namespace JanHerman\Translations;

use Kirby\Cms\ModelWithContent;

class TranslationsHelper
{
    public static function getContentTranslationStatuses(ModelWithContent $model, string $language_code = null): array
    {
        $translation_statuses = [];
        $translations = $language_code ? (array) $model->translation($language_code) : $model->translations();

        if ($translations) {
            foreach ($translations as $translation) {
                $code = $translation->code();
                $translation_file_exists = $translation->exists();
                $translation_status_field = $model->content($code)->is_translated();

                $translation_statuses[$code]['code'] = $code;
                $translation_statuses[$code]['file'] = $translation_file_exists;
                $translation_statuses[$code]['isTranslated'] = $translation_status_field->exists() ? ($translation_status_field->isTrue() && $translation_file_exists) : $translation_file_exists;
            }
        }

        return $translation_statuses;
    }

    public static function getContentTranslationUrls(ModelWithContent $model): array
    {
        $preview_urls = [];

        if ($model->exists()) {
            foreach (kirby()->languages() as $language) {
                $preview_urls[$language->code()] = $model->url($language->code());
            }
        }

        return $preview_urls;
    }
}
