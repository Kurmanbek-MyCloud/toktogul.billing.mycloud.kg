<template>
  <Layout :module-label="moduleLabel">
    <div class="flex border-b border-gray-500 border-solid items-center px-3">
      <button
        @click="sort(sortOrder)"
        class="mr-4 border-r border-gray-500 border-solid px-4 block"
      >
        <i
          :class="{ 'dripicons-arrow-thin-up': sortOrder }"
          class="text-sm dripicons-arrow-thin-down font-medium text-gray-800"
        ></i>
      </button>
      <v-select
        class="w-full outline-none text-sm font-medium text-gray-800 py-1"
        :options="fiterOptions"
        v-model="filterId"
        :selectable="fiterOptions => !fiterOptions.disable"
        label="label"
        @input="selectFilter()"
        :clearable="false"
      ></v-select>
    </div>
    <div class="py-1">
      <div v-if="recordList.records">
        <div
          v-for="record in recordList.records"
          class="px-4 border-b border-solid border-gray-300"
        >
          <router-link
            :to="`${$route.path}/detail/${record.id}`"
            class="py-1 w-full flex text-sm justify-between items-center"
          >
            <span>
              <span
                v-for="field in recordList.nameFields"
                class="font-medium text-gray-800"
              >
                {{ record[field] }}
              </span>
            </span>
            <span
              class="dripicons-information text-base inline-flex text-gray-500"
            ></span>
          </router-link>
        </div>
      </div>
      <div class="flex justify-center py-4" v-else>
        <span class="text-sm font-medium text-gray-800">Нет данных</span>
      </div>
      <router-link
        :to="`${$route.path}/create`"
        class="fixed bottom-0 right-0 bg-indigo-400 rounded-full m-6 flex p-3 shadow-lg"
      >
        <i class="dripicons-plus text-2xl inline-flex text-white"></i>
      </router-link>
    </div>
  </Layout>
</template>
<script>
import vSelect from 'vue-select';
export default {
  components: {
    vSelect,
  },
  data() {
    return {
      recordList: {},
      eventsFilter: {},
      filters: {},
      filterId: {
        label: 'All',
        value: null,
      },
      sortOrder: null,
      value: '',
      moduleLabel: '',
    };
  },
  created() {
    this.getFilter();
    this.getData();
  },
  methods: {
    sort(sortOrder) {
      if (sortOrder) {
        this.sortOrder = null;
      } else {
        this.sortOrder = 'asc';
      }
      this.getData();
    },
    getData() {
      axios
        .post('api.php', {
          module: this.$route.params.name,
          filterid: this.filterId.value,
          page: 1,
          orderBy: null,
          sortOrder: this.sortOrder,
          _operation: 'listModuleRecords',
        })
        .then(response => {
          this.recordList = response.data.result;
        });
      //                axios.post('api.php', {module: this.$route.params.name, filterid: null, page: 1, orderBy: null, sortOrder: "", _operation: "listModuleRecords"}).then((response) => {
      //                    this.events = response.data.result
      //                })
      //                axios.post('api.php', {module: this.$route.params.name, filterid: "13", page: 1, orderBy: false, sortOrder: false, _operation: "listModuleRecords"}).then((response) => {
      //                    this.eventsFilter = response.data.result
      //                })
      //                axios.post('api.php', {module: this.$route.params.name, filterid: "13", page: 1, orderBy: false, sortOrder: "asc", _operation: "listModuleRecords"}).then((response) => {
      //                    this.eventsFilter = response.data.result
      //                })
    },
    getFilter() {
      axios
        .post('api.php', {
          _operation: 'fetchModuleFilters',
          module: this.$route.params.name,
        })
        .then(response => {
          this.moduleLabel = response.data.result.moduleLabel;
          this.filters = response.data.result.filters;
        });
    },
    selectFilter() {
      this.getData();
    },
  },
  computed: {
    fiterOptions() {
      let arr = [];
      for (let key in this.filters) {
        if (key != 'Others') {
          arr.push({
            label: key,
            disable: true,
          });
          this.filters[key].forEach(item => {
            arr.push({
              label: item.name,
              value: item.id,
            });
          });
        }
      }
      return arr;
    },
  },
  watch: {
    $route() {
      this.sortOrder = null;
      this.filterId = { label: 'All', value: null };
      this.getData();
      this.getFilter();
    },
  },
};
</script>
