<template>
    <div>
        <span class="text-sm font-medium text-gray-800">{{ field.label }}<span class="text-red-500" v-if="field.mandatory">*</span></span>
        <input required="required" type="date" v-model="date" class="w-full border border-gray-500 outline-none text-base font-medium text-gray-800 border-solid py-1 rounded px-2">
    </div>
</template>
<script>
import Form from '../mixins/Form'
import moment from 'moment'
export default {
    mixins: [Form],
    data() {
        return {
            date: '',
        }
    },
    watch: {
        date() {
            this.value = moment(this.date).format('DD-MM-YYYY')
        }
    },
    created() {
        if(this.fetchRecordItem) {
            this.date = this.fetchRecordItem
        }
    }
}
</script>
