<script>
    import flatpickr from "flatpickr";
    import "flatpickr/dist/themes/airbnb.css";
    import { DateTime } from "luxon";

    export default {
        props: {
            field: {
                required: true,
            },
        },

        data: () => ({ flatpickr: null, date_values: null }),

        mounted() {
            const self = this;

            this.$nextTick(() => {
                let fieldOptions = this.options;

                let setOptions = {
                    onClose: this.onChange,
                    enableTime: this.enableTime,
                    enableSeconds: this.enableSeconds,
                    dateFormat: this.dateFormat,
                    allowInput: true,
                    mode: this.modeType,
                    time_24hr: !this.twelveHourTime,
                    onReady() {
                        self.$refs.dateRangePicker.parentNode.classList.add(
                            "date-range-filter"
                        );
                    },
                    locale: {
                        rangeSeparator: ` ${this.separator} `,
                        firstDayOfWeek: this.firstDayOfWeek,
                    },
                };

                let options = {
                    ...setOptions,
                    ...fieldOptions,
                };

                this.flatpickr = flatpickr(this.$refs.dateRangePicker, options);
            });
        },

        computed: {
            placeholder() {
                return this.field.placeholder || this.__("Pick a date range");
            },
            startDate() {
                return flatpickr.formatDate(
                    flatpickr.parseDate(
                        this.field.currentValue[0],
                        this.dateFormat
                    ),
                    this.dateFormat
                );
            },
            endDate() {
                return flatpickr.formatDate(
                    flatpickr.parseDate(
                        this.field.currentValue[1],
                        this.dateFormat
                    ),
                    this.dateFormat
                );
            },
            value() {
                if (
                    typeof this.field.currentValue === "object" &&
                    this.field.currentValue.length >= 2
                ) {
                    return `${this.startDate} ${this.separator} ${this.endDate}`;
                }
                return this.field.currentValue || null;
            },
            disabled() {
                return this.field.disabled;
            },
            separator() {
                return this.field.separator || "-";
            },
            modeType() {
                let mode = this.field.modeType;
                if (!mode && this.field.single) {
                    mode = "single";
                }
                if (!mode) {
                    mode = "range";
                }
                return mode;
            },
            dateFormat() {
                return (
                    this.field.dateFormat ||
                    (this.enableTime ? "Y-m-d H:i" : "Y-m-d")
                );
            },
            twelveHourTime() {
                return this.field.twelveHourTime || false;
            },
            enableTime() {
                return this.field.enableTime || false;
            },
            enableSeconds() {
                return this.field.enableSeconds || false;
            },
            firstDayOfWeek() {
                return this.field.firstDayOfWeek || 1;
            },
            options() {
                return this.field.options;
            },
            timezone() {
                return Nova.config("userTimezone") || Nova.config("timezone");
            },
        },

        methods: {
            onChange(event) {
                if (this.modeType != "single") {
                    let date_values = event.map((value) => {
                        return flatpickr.formatDate(value, this.dateFormat);
                    });
                    let field_data = JSON.stringify({
                        from: date_values[0] ?? "",
                        till: date_values[1] ?? "",
                    });
                    this.date_values = field_data;
                } else {
                    this.date_values = event.value;
                }

                this.$emit("change", {
                    target: this.$refs.dateRangePicker,
                    date_values: this.date_values,
                });
            },
        },
    };
</script>

<template>
    <input
        :disabled="disabled"
        :dusk="field.attribute"
        :class="{ '!cursor-not-allowed': disabled }"
        :value="value"
        :name="field.name"
        ref="dateRangePicker"
        autocomplete="off"
        type="text"
        :placeholder="placeholder"
    />
</template>

<style scoped>
    .\!cursor-not-allowed {
        cursor: not-allowed !important;
    }
</style>
