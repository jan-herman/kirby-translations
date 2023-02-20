panel.plugin('jan-herman/translations', {
    fields: {
        translationStatus: {
            props: {
                name: String,
                label: String,
                text: [Array, String],
                languageCode: String,
                translations: Object,
                previewUrls: Object,
                value: Boolean
            },
            computed:{
                apiUrl() {
                    return this.$view.props.model.link + '/translations-info';
                },
                fileExists() {
                    return this.translationsData[this.languageCode].file;
                },
                hasUnsavedChanges () {
                    return this.$store.getters["content/hasChanges"]();
                }
            },
            data() {
                return {
                    translationsData: this.translations
                }
            },
            created() {
                this.$events.$on('model.update', this.updateTranslationsData);
            },
            destroyed() {
                this.$events.$off('model.update', this.updateTranslationsData);
            },
            methods: {
                updateTranslationsData(){
                    return this.$api.get(this.apiUrl)
                    .then(response => {
                        this.translationsData = response.translations;
                    }).catch(error => {
                        this.$store.dispatch('notification/error', error);
                    })
                },
                onInput(value) {
                    this.$emit('input', value);
                }
            },
            template: `
                <div>
                    <k-info-field
                        class="k-field-translation-status-info"
                        :label="label"
                        :text="$t('jan-herman.translations.translation-status-field.missing-translation')"
                        theme="negative"
                        v-show="!fileExists && !hasUnsavedChanges"
                    />
                    <k-toggle-field
                        :class="[
                            'k-field-translation-status',
                            {'k-field-translation-status--is-translated': value}
                        ]"
                        :disabled="!this.$multilang"
                        :name="name"
                        :label="label"
                        icon="globe"
                        :after="languageCode"
                        :text="text"
                        :value="value"
                        @input="onInput"
                        v-show="fileExists || hasUnsavedChanges"
                    />
                </div>
            `
        }
    }
});