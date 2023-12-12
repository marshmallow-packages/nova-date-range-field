<template>
    <DefaultField
        :field="currentField"
        :errors="errors"
        :show-help-text="showHelpText"
        :full-width-content="fullWidthContent"
    >
        <template #field>
                <DateRangePicker
                    class="w-full flex-1 form-control form-input form-input-bordered"
                    :id="currentField.uniqueKey"
                    :dusk="field.attribute"
                    :name="field.name"
                    :field="currentField"
                    :value="value"
                    @change="handleChange"
                />
        </template>
    </DefaultField>
</template>

<script>
    import { DependentFormField, HandlesValidationErrors } from "laravel-nova";
    import DateRangePicker from "./DateRangePicker";

    export default {
        mixins: [DependentFormField, HandlesValidationErrors],

        components: { DateRangePicker },
        props: ["resourceName", "resourceId", "field"],

        data: () => ({
            formattedDates: [],
            date_values: [],
        }),

        methods: {
            /*
             * Set the initial, internal value for the field.
             */
            setInitialValue() {
                this.value = this.currentField.value || "";
            },

            /**
             * Update the field's internal value
             */
            handleChange(event) {
                let value = event?.target?.value ?? event;

                if (event?.date_values) {
                    this.date_values = event?.date_values;
                } else {
                    this.value = value;
                }

                if (this.field) {
                    this.emitFieldValueChange(this.field.attribute, this.value);
                }
            },

            /**
             * Fill the given FormData object with the field's internal value.
             */
            fill(formData) {
                let values = this.value || "";

                if (this.date_values.length) {
                    values = this.date_values;
                }

                formData.append(this.field.attribute, values);
            },
        },
    };
</script>
