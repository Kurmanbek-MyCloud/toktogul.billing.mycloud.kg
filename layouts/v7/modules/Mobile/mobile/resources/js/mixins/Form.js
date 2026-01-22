export default {
  props: {
    field: Object,
    fetchRecordItem: Object,
  },
  data() {
    return {
      value: null,
    };
  },
  created() {
    if (this.fetchRecordItem) {
      this.value = this.fetchRecordItem;
    }
    this.field.fill = this.fill;
  },
  methods: {
    fill(formData) {
      formData[this.field.name] = this.value || '';
      // formData.set(this.field.name, this.value)
    },
  },
};
