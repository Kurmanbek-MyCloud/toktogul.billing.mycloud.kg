<template>
    <div>
        <span class="text-sm font-medium text-gray-800">{{ field.label }}<span class="text-red-500" v-if="field.mandatory">*</span></span>
        <input autocomplete="off" :placeholder="field.label" :id="field.name" type="text" v-model="value"
               class="w-full border border-gray-500 outline-none text-base font-medium text-gray-800 border-solid py-1 rounded px-2">
    </div>
</template>
<script>
    import Form from '../mixins/Form'
    export default {
        mixins: [Form]
    }
</script>