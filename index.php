<?php

namespace JanHerman\Translations;

use Kirby\Cms\App as Kirby;

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('jan-herman/translations', [
    'fields' => [
        'translationStatus' => [
            'extends' => 'toggle',
            'computed' => [
                'languageCode' => function(): string
                {
                    return $this->model()->translation()->code();
                },
                'translations' => function () {
                    return TranslationsHelper::getContentTranslationStatuses($this->model());
                },
                'previewUrls' => function () {
                    return TranslationsHelper::getContentTranslationUrls($this->model());
                }
            ]
        ]
    ],
    'api' => [
        'routes' => function (\Kirby\Cms\App $kirby) {
            return [
                [
                    'pattern' => 'pages/(:any)/translations-info',
                    'method'  => 'GET',
                    'action'  => function (string $id) use ($kirby)
                    {
                        $page = $this->page($id);

                        if (!$page) {
                            return false;
                        }

                        return [
                            'options' => $kirby->option('jan-herman.translations'),
                            'translations' => TranslationsHelper::getContentTranslationStatuses($page),
                            'previewUrls' => TranslationsHelper::getContentTranslationUrls($page),
                        ];
                    }
                ]
            ];
        }
    ],
    'blueprints' => [
        'fields/translation-status' => __DIR__ . '/blueprints/fields/translation-status.yml',
    ],
    'pageMethods' => [
        'translationFileExists' => function (string $language_code = null): bool
        {
            return $this->translation($language_code)->exists();
        },
        'isTranslated' => function (string $language_code = null): bool
        {
            if (!kirby()->multilang()) {
                return false;
            }

            $language_code = $language_code ?: kirby()->language()->code();
            $tranlation_file_exists = $this->translationFileExists($language_code);
            $translation_status_field = $this->content($language_code)->is_translated();

            if ($translation_status_field->exists()) {
                return $tranlation_file_exists && $translation_status_field->isTrue();
            }

            return $tranlation_file_exists;
        },
        'translationStatusIndicator' => function (): string
        {
            if (!kirby()->multilang()) {
                return '';
            }

            $disabled = kirby()->language()->isDefault() && !$this->is_translated()->exists() ? 'true' : 'false';

            $theme = 'negative';

            if ($this->isTranslated()) {
                $theme = 'positive';
            } elseif ($this->translationFileExists()) {
                $theme = 'notice';
            }

            return '<div data-disabled="' . $disabled . '" data-theme="' . $theme . '" class="k-button k-status-icon k-translation-status"><span class="k-button-icon k-icon"><svg viewBox="0 0 16 16"><use xlink:href="#icon-globe"></use></svg></span></div>';
        }
    ],
    'pagesMethods' => [
        'translated' => function (string $language_code = null): \Kirby\Cms\Pages
        {
            if (!kirby()->multilang()) {
                return $this;
            }

            $translated_pages = $this->filter(function ($child) use ($language_code) {
                return $child->isTranslated($language_code);
            });

            return $translated_pages;
        }
    ],
    'collectionMethods' => [
        'order' => function (array $language_codes = null): \Kirby\Cms\Languages
        {
            if (!$this instanceof \Kirby\Cms\Languages) {
                return $this;
            }

            $order = $language_codes ?: option('languages.order');

            if (!$order) {
                return $this;
            }

            return $this->sortBy(function($language) use ($order) {
                return in_array($language->code(), $order) ? array_search($language->code(), $order) : 9999;
            });
        }
    ],
    'translations' => [
        'en' => [
            'jan-herman.translations.translation-status-field.label' => 'Translation Status',
            'jan-herman.translations.translation-status-field.missing-translation' => 'Translation does not yet exist.',
            'jan-herman.translations.translation-status-field.waiting-for-approval' => 'Waiting for approval',
            'jan-herman.translations.translation-status-field.approved' => 'Approved',
        ],
        'cs' => [
            'jan-herman.translations.translation-status-field.label'  => 'Stav Překladu',
            'jan-herman.translations.translation-status-field.missing-translation' => 'Překlad zatím neexistuje.',
            'jan-herman.translations.translation-status-field.waiting-for-approval' => 'Čeká na schválení',
            'jan-herman.translations.translation-status-field.approved' => 'Schváleno',
        ]
    ]
]);
