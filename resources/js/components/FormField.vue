<template>
    <DefaultField
        :field="field"
        :errors="errors"
        :show-help-text="showHelpText"
        :full-width-content="fullWidthContent"
    >
        <template #field>
            <div class="flex items-center gap-2">
                <div
                    class="font-awesome-field font-awesome-field-form flex justify-center border border-gray-100 dark:border-gray-700 rounded p-1"
                    v-html="currentSVG"
                />
                <button
                    type="button"
                    class="shadow relative bg-primary-500 hover:bg-primary-400 text-white dark:text-gray-900 cursor-pointer rounded text-sm font-bold
                     focus:outline-none focus:ring ring-primary-200 dark:ring-gray-600 inline-flex items-center justify-center h-9 px-3 shadow
                     relative bg-primary-500 hover:bg-primary-400 text-white dark:text-gray-900"
                    @click="show = true"
                >
                    {{ field.texts.update }}
                </button>
                <LoadingButton
                    v-if="field.nullable"
                    :aria-disabled="!value"
                    :disabled="!value"
                    component="DangerButton"
                    type="button"
                    @click="removeIcon"
                >
                    {{ field.texts.remove }}
                </LoadingButton>
            </div>
            <input
                :id="field.attribute"
                type="hidden"
                v-model="value"
            />
            <div :class="errorClasses"/>
        </template>
    </DefaultField>
    <Modal
        :show="show"
        tabindex="-1"
        role="dialog"
        modal-style="window"
        size="lg"
        class="overflow-hidden"
    >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden space-y-6">
            <ModalHeader v-text="field.texts.header"/>
            <ModalContent class="py-1 divide-y divide-gray-100 dark:divide-gray-800 -mx-3 space-y-4">
                <div class="flex justify-center">
                    <div v-html="selectedSVG"
                         class="font-awesome-field font-awesome-field-form-modal flex justify-center
                            border border-gray-100 dark:border-gray-700 rounded p-1"/>
                </div>
                <div>
                    <input
                        type="search" class="w-full form-control form-input form-input-bordered"
                        v-model="search"
                        :placeholder="field.texts.search"
                    >
                </div>
                <div v-if="field.styleSelector && availableStyles.length > 1" class="flex flex-wrap justify-center gap-1">
                    <button
                        v-for="style in availableStyles"
                        v-text="style"
                        role="button"
                        class="border rounded px-1 py-0.5 border-gray-100 dark:border-gray-700"
                        :class="selectedStyles.includes(style) ?
                            'bg-gray-100 dark:bg-gray-700' :
                            'hover:bg-gray-100 dark:hover:bg-gray-700'"
                        @click="changeStyle(style, selectedStyles.includes(style))"
                        :aria-disabled="loading"
                        :disabled="loading"
                    />
                </div>
                <div class="overflow-y-auto flex flex-wrap max-h-60 gap-2">
                    <template v-for="(data, index) in icons">
                        <button
                            v-for="(icon, style) in data.svg"
                            :title="index + ' ' + style"
                            type="button"
                            class="font-awesome-field font-awesome-field-btn h-12 w-12 text-center
                                    border border-gray-100 dark:border-gray-700 rounded p-1
                                    hover:bg-gray-100 dark:hover:bg-gray-700"
                            @click="selectIcon(style, icon, index)"
                        >
                            <span class="block" v-html="icon.raw"/>
                            <span class="sr-only">{{ index }} {{ style }}</span>
                        </button>
                    </template>
                    <div v-if="!Object.keys(icons).length && !loading" class="px-2 py-4 text-center w-full italic font-semibold">
                        {{ field.texts.null }}
                    </div>
                    <LoadingCard class="w-full py-5 block text-center w-full" v-if="loading"/>
                    <button
                        v-if="chunk && !loading"
                        type="button"
                        class="text-center px-2 py-2 whitespace-nowrap hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-900
                        block text-center w-full"
                        @click="getIcons"
                    >
                        {{ field.texts.more }}
                    </button>
                </div>
            </ModalContent>
            <ModalFooter>
                <div class="flex items-center ml-auto">
                    <CancelButton
                        component="button"
                        type="button"
                        class="ml-auto mr-3"
                        @click="show = false"
                    >
                        {{ field.texts.cancel }}
                    </CancelButton>

                    <DefaultButton
                        :aria-disabled="!selectedSVG && !field.nullable"
                        :disabled="!selectedSVG && !field.nullable"
                        type="button"
                        @click="setIcon"
                    >
                        {{ field.texts.update }}
                    </DefaultButton>
                </div>
            </ModalFooter>
        </div>
    </Modal>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova'
import debounce from "lodash/debounce";

export default {
    mixins: [FormField, HandlesValidationErrors],
    props: [
        'resourceName',
        'resourceId',
        'field',
        'field',
    ],
    data() {
        return {
            value: this.field.value || '',
            currentSVG: this.field.currentSVG || '',
            selectedSVG: this.field.currentSVG || '',
            selectedIcon: null,
            icons: {},
            search: '',
            loading: true,
            show: false,
            chunk: 0,
            availableStyles: null,
            selectedStyles: [],
        }
    },
    mounted() {
        this.getIcons()
    },
    watch: {
        search: debounce(function () {
            this.loading = true
            this.chunk = 0
            this.icons = {}
            this.getIcons()
        }, 250)
    },
    methods: {
        changeStyle(style, isActive) {
            if (isActive && this.selectedStyles.includes(style)) {
                this.selectedStyles = this.selectedStyles.filter(function (v) {
                    return v !== style
                })
            } else {
                this.selectedStyles.push(style)
            }
            this.loading = true
            this.icons = {}
            this.chunk = 0
            this.getIcons()
        },
        removeIcon() {
            this.currentSVG = ''
            this.value = ''
        },
        selectIcon(style, icon, iconClass) {
            this.selectedIcon = {
                style: style,
                svg: icon.raw,
                iconClass: iconClass
            }
            this.selectedSVG = icon.raw
        },
        setIcon() {
            this.currentSVG = this.selectedIcon.svg
            this.value = this.field.saveRawSVG ? this.selectedIcon.svg : 'fa-' + this.selectedIcon.style + ' fa-' + this.selectedIcon.iconClass
            this.show = false
        },
        getIcons() {
            this.loading = true
            Nova.request().post('/nova-vendor/nova-font-awesome-field/icons', {
                search: this.search,
                chunk: this.chunk,
                styles: this.selectedStyles,
            }).then(response => {
                this.icons = {...this.icons, ...response.data.icons}
                this.loading = false
                this.chunk = response.data.chunk
                this.availableStyles = response.data.availableStyles
            })
        },
    },
}
</script>
