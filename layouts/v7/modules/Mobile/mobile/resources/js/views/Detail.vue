<template>
     <CreateLayout>
          <div class="bg-indigo-400 py-1">
               <div class="container flex justify-between items-center">
                    <div class="flex items-center">
                         <button class="mr-4" @click="$router.go(-1)">
                              <i class="dripicons-cross text-white text-2xl"></i>
                         </button>
                         <span class="font-medium text-white mb-1" v-if="record.label">{{ record.label }}</span>
                    </div>
                    <router-link :to="`${$route.path}/edit`">
                         <i class="dripicons-pencil text-white text-lg"></i>
                    </router-link>
               </div>
          </div>
          <div class="container py-1" >
               <div>
                    <div class="flex mb-1">
                         <button :class="{'text-indigo-400': selected}" class="w-1/2 text-center text-gray-800 py-1 border-b border-solid border-indigo-400" @click="selected=true">Details</button>
                         <button :class="{'text-indigo-400': !selected}" class="w-1/2 text-center text-gray-800 py-1 border-b border-solid border-indigo-400" @click="selected=false">Related</button>
                    </div>
               </div>
               <div>
                    <div v-if="selected && describe">
                         <div v-for="field in describe.fields">
                              <div v-if="field" class="border-b border-solid border-gray-300 flex py-1 text-sm font-medium text-gray-800">
                                   <span class="w-1/2 pr-4">{{ field.label }}</span>
                                   <span class="pl-4 w-1/2">
                                        <span v-if="typeof record[field.name] == 'string'">{{ record[field.name] }}</span>
                                        <span v-else>{{ record[field.name].label }}</span>
                                   </span>
                              </div>
                         </div>
                    </div>
                    <div v-if="!selected">
                         <div v-for="(value, name) in fetchRecord">
                              <router-link :to="`/view/${$route.params.program}/${value.relatedModule}/${record.id}`" class="border-b border-solid border-gray-300 flex justify-between py-1 text-sm font-medium text-gray-800">
                                   <span class=" pr-2">{{ name }}</span>
                                   <span class="pl-2 text-indigo-400">{{ value.count }}</span>
                              </router-link>
                         </div>
                    </div>
               </div>
          </div>
     </CreateLayout>
</template>
<script>
    export default {
        data(){
            return {
                describe: {},
                fetchRecord: {},
                record: {},
                selected: true,
//                items: []
            }
        },
        created() {
            this.getData()
        },
        mounted() {
            for (key in this.fetchRecord) {
                this.items.push(key)
            }

        },
        methods: {
            getData() {
                axios.post('api.php', {module: this.$route.params.name, _operation: "describe"}).then((response) => {
                    this.describe = response.data.result.describe
                })
                axios.post('api.php', {module: this.$route.params.name, record: this.$route.params.id, view_mode: "web", _operation: "fetchRecord"}).then((response) => {
                    this.record = response.data.result.record
                })
                axios.post('api.php', {module: this.$route.params.name, record: this.$route.params.id, _operation: "fetchRecord", mode: "getRelatedRecordCount"}).then((response) => {
                    this.fetchRecord = response.data.result
                })
            },
        },
        watch: {
            $route() {
                this.getData()
            }
        }
    }
</script>
