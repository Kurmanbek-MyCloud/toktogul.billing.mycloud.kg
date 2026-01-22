<template>
    <div class="py-1">
        <label :for="field.name" class="flex items-center">
            <span class="w-6 h-auto overflow-hidden border rounded-lg border-solid border-gray-700 check-wrap p-1">
                <svg :class="{'opacity-0': !value}" class="w-full h-full fill-current text-gray-700" enable-background="new 0 0 515.556 515.556"
                     viewBox="0 0 515.556 515.556">
                    <path d="m0 274.226 176.549 176.886 339.007-338.672-48.67-47.997-290.337 290-128.553-128.552z"/>
                </svg>
            </span>
            <span class="leading-tight text-sm font-medium text-gray-800 ml-3">{{ field.label }}</span>
            <input type="checkbox" :id="field.name" class="hidden" v-model="value">
        </label>
    </div>
</template>
<script>
    import Form from '../mixins/Form'
    export default {
        mixins: [Form]
    }
</script>
