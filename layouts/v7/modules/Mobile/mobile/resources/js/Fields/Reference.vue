<template>
    <div>
        <span class="text-sm font-medium text-gray-800">{{ field.label }}<span class="text-red-500" v-if="field.mandatory">*</span></span>
        <v-select
                class="w-full border border-gray-500 outline-none text-base font-medium text-gray-800 border-solid py-1 rounded px-2"
                :options="field.type.picklistValues"
                v-model="value"
                :placeholder="field.label"
                :clearable="false"
                :reduce="label => label.value"
        ></v-select>
    </div>
</template>
<script>
import vSelect from 'vue-select'
import Form from '../mixins/Form'
import "vue-select/dist/vue-select.css"
export default {
    components: {
        vSelect
    },
    mixins: [Form]
}
</script>
<style>
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