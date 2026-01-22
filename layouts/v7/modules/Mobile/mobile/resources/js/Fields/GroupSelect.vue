<template>
    <div>
        <span class="text-sm font-medium text-gray-800">{{ field.label }}<span class="text-red-500" v-if="field.mandatory">*</span></span>
        <v-select
                class="w-full border border-gray-500 outline-none text-base font-medium text-gray-800 border-solid py-1 rounded px-2"
                :options="options"
                v-model="value"
                :placeholder="field.label"
                :selectable="option => ! option.disable"
                :reduce="label => label.value"
                label="label"
                :clearable="false">
        </v-select>

    </div>
</template>
<script>
    import vSelect from 'vue-select'
    import Form from '../mixins/Form'
//    import "vue-select/dist/vue-select.css"
    export default {
        components: {
            vSelect
        },
        data() {
            return {
                items: []
            }
        },
        mixins: [Form],
        computed: {
            options() {
                let arr = []
                for (let key in this.field.type.picklistValues) {
                    arr.push({
                        label: key,
                        disable: true
                    })
                    for (let k in this.field.type.picklistValues[key]) {
                        arr.push({
                            label: this.field.type.picklistValues[key][k],
                            value: k
                        })
                    }
                }
                return arr
            },
        }
    }
</script>
<style lang="css">
    .vs__dropdown-toggle {
        border: none;
        padding-bottom: 0;
    }
    .vs__selected-options {
        padding: 0;
    }
    .vs__search {
        padding: 0;
        margin: 0;
    }
    .vs__search:focus {
        padding: 0;
        margin: 0;
    }
    .vs__selected {
        padding: 0;
        margin: 0;
    }
</style>