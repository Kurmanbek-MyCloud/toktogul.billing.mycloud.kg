<template>
    <header class="bg-indigo-400 shadow-md w-full fixed z-10">
        <div class="container xs:container">
            <div class="flex py-3 items-center justify-between">
                <div class="flex items-center">
                    <button @click="openMenu" class="inline-flex">
                        <i class="dripicons-menu text-3xl xs:text-2xl 2xs:text-xl text-white inline-flex"></i>
                    </button>
                    <span class="text-white ml-4 font-medium">{{ moduleLabel }}</span>
                </div>
                <button class="inline-flex">
                    <i class="dripicons-search text-3xl xs:text-2xl 2xs:text-xl text-white inline-flex"></i>
                </button>
            </div>
        </div>
        <transition name="menu">
            <div class="fixed left-0 top-0 bg-white shadow-md w-8/12 2xs:w-9/12 3xs:w-10/12 h-screen z-50 overflow-y-scroll scroll z-10" v-if="menu">
                <div class="h-full">
                    <div class="bg-indigo-400 py-4 px-8 2xs:px-4 3xs:px-3 flex">
                        <div class="w-12 h-12 mr-4">
                            <svg class="fill-current text-white" x="0px" y="0px" viewBox="0 0 50 50">
                                <path d="M25 1C11.767 1 1 11.767 1 25c0 7.091 3.094 13.472
                                8 17.869v.017l.348.3c.061.053.128.097.19.149a24.496 24.496 0
                                0 0 3.189 2.279c.085.051.172.099.257.148.557.324 1.126.629
                                1.71.908l.018.008a23.838 23.838 0 0 0 3.915 1.456l.075.021c.641.175
                                1.293.322 1.954.443l.185.033a24.17 24.17 0 0 0 1.939.262c.075.007.15.011.224.017.659.055
                                1.323.09 1.996.09s1.337-.035 1.996-.09c.075-.006.15-.01.224-.017.655-.06 1.301-.15 1.939-.262l.185-.033a23.451
                                23.451 0 0 0 1.954-.443l.075-.021a23.838 23.838 0 0 0 3.915-1.456l.018-.008a24.261 24.261 0 0 0
                                1.71-.908c.086-.05.172-.097.257-.148a24.123 24.123 0 0 0 1.487-.968c.124-.087.248-.174.371-.264.456-.334.9-.683
                                1.331-1.047.062-.052.129-.096.19-.149l.348-.3v-.017c4.906-4.398 8-10.778 8-17.869C49 11.767 38.233 1 25 1zm0 24c-4.411
                                0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8zm3 2c6.065 0 11 4.935 11
                                11v3.958c-.042.035-.086.067-.128.102-.395.321-.8.626-1.214.918-.092.065-.182.132-.274.195-.447.305-.906.591-1.373.862l-.257.148a21.799 21.799
                                0 0 1-6.871 2.468l-.171.031a22.27 22.27 0 0 1-1.715.225c-.079.007-.159.012-.239.018-.583.045-1.169.075-1.758.075s-1.175-.03-1.758-.077l-.239-.018a21.789
                                21.789 0 0 1-1.886-.256 22.013 22.013 0 0 1-5.212-1.626l-.161-.073a21.799 21.799 0 0
                                1-1.755-.917c-.467-.27-.926-.557-1.373-.862-.093-.063-.183-.13-.274-.195a21.826 21.826 0 0
                                1-1.214-.918c-.042-.034-.086-.067-.128-.102V38c0-6.065 4.935-11 11-11h6zm13
                                13.076V38c0-6.271-4.464-11.519-10.38-12.735A9.996 9.996 0 0 0 35 17c0-5.514-4.486-10-10-10s-10
                                4.486-10 10a9.996 9.996 0 0 0 4.38 8.265C13.464 26.481 9 31.729 9 38v2.076C5.284 36.135 3 30.831
                                3 25 3 12.869 12.869 3 25 3s22 9.869 22 22c0 5.831-2.284 11.135-6 15.076z"/>
                            </svg>
                        </div>
                        <div class="flex flex-col justify-between">
                            <span class="font-medium text-white text-base 3xs:text-sm">{{ data.userinfo.last_name }}</span>
                            <span class="font-medium text-white text-sm 3xs:text-xs">{{ data.userinfo.email }}</span>
                        </div>
                    </div>
                    <div class="bg-indigo-400 p-4 3xs:px-3 relative">
                        <div class="text-white px-4 2xs:px-0">
                            <button class="flex items-center justify-between border-t border-b border-solid border-white py-2 w-full px-4" @click="dropDown = !dropDown">
                                <span class="mr-8 font-bold text-lg 3xs:text-base">{{ programSelected || data.apps[0] }}</span>
                                <span class="text-white text-sm transform transition-transform duration-150" :class="{'rotate-180': dropDown}"><i class="dripicons-arrow-thin-down"></i></span>
                            </button>
                        </div>
                        <div class="absolute left-0 top-full overflow-hidden w-full z-10">
                            <transition name="dropdown">
                                <div v-if="dropDown" class="bg-white py-4 px-12 2xs:px-8 3xs:px-7 w-full border-b-8 border-solid border-indigo-400">
                                    <div class="mb-3 py-1" v-for="program in data.apps">
                                        <button class="text-indigo-400 font-medium w-full text-left 3xs:text-sm"
                                                :class="{'underline font-semibold': programSelected == program}"
                                                @click="selectProgram(program)">{{ program }}</button>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                    <div class="p-4 border-b border-solid border-indigo-400 3xs:px-3">
                        <ul class="p-4 2xs:px-0">
                            <!--<li class="text-base font-medium pb-3">-->
                                <!--<router-link :to="`/view/${programSelected}/Events`"-->
                                             <!--:class="{'text-indigo-400': '/events' === $route.path}"-->
                                             <!--class="flex items-center text-gray-800 text-base 3xs:text-sm py-2 hover:text-indigo-400 transition duration-150">-->
                                    <!--<i class="w-6 h-6 mr-4 menu-icons vicon-calendar"></i><span>События</span>-->
                                <!--</router-link>-->
                            <!--</li>-->
                            <li class="text-base font-medium pb-3">
                                <router-link to="/"
                                             :class="{'text-indigo-400': '/' === $route.path}"
                                             class="flex items-center text-gray-800 text-base 3xs:text-sm py-2 hover:text-indigo-400 transition duration-150">
                                    <i class="w-6 h-6 mr-4 menu-icons vicon-calendar"></i><span>Календарь</span>
                                </router-link>
                            </li>
                            <li v-for="list in menuList" class="text-base font-medium pb-3" >
                                <router-link :to="`/view/${programSelected}/${list.name}`"
                                             :class="{'text-indigo-400': list.label === moduleLabel}"
                                             class="flex items-center text-gray-800 text-base 3xs:text-sm py-2 hover:text-indigo-400 transition duration-150">
                                    <i class="w-6 h-6 mr-4 menu-icons" :class="`vicon-${list.name.toLowerCase()}`"></i><span>{{ list.label }}</span>
                                </router-link>
                            </li>
                        </ul>
                    </div>
                    <div class="p-8 2xs:px-4 3xs:px-3">
                        <button @click="logout()" class="text-base font-medium text-gray-800 flex items-center hover:text-indigo-400 transition duration-150">
                            <i class="dripicons-power mr-4 text-lg 3xs:text-base"></i><span class="mb-1">Logout</span>
                        </button>
                    </div>
                </div>
            </div>
        </transition>
        <transition name="fade">
            <div v-if="menu" class="fixed left-0 top-0 bg-black w-full h-screen opacity-25 z-10" @click="closeMenu"></div>
        </transition>
    </header>
</template>
<script>
export default {
    props: {
        data: {
            type: Object,
            default: () => {
                return {}
            }
        },
        moduleLabel: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            menu: false,
            programSelected: '',
            dropDown: false,
            page: ''
        }
    },
    created() {
        if(this.$route.params.program) {
            this.programSelected = this.$route.params.program
        }else {
            this.programSelected = 'MARKETING'
        }
    },
    methods: {
        logout() {
            axios.post('api.php', {_operation: "logout"}).then(() => {
                window.location.reload();
            })
        },
        selectProgram(program) {
            this.programSelected = program
            this.dropDown = false
        },
        openMenu() {
            this.menu = true
            document.querySelector('html').classList.add('overflow-hidden')
        },
        closeMenu() {
            this.menu = false
            this.dropDown = false
            document.querySelector('html').classList.remove('overflow-hidden')
        },
        selectedPage() {
            this.menuList.forEach(list => {
                if(list.name === this.$route.params.name) {
                    this.page = list.label
                }
            })
        }
    },
    watch: {
        $route() {
            this.closeMenu()
            this.selectedPage()
        }
    },
    computed: {
        menuList() {
            return this.data.menus[this.programSelected] || this.data.menus[this.data.apps[0]]
        },
        viewTitle() {
            return this.menuList.forEach(item => item.name.include(this.$route.params.name))
        },
    }
}
</script>

<style lang="css" scoped>
    .menu-enter-active, .menu-leave-active {
        transition: transform 0.3s ease;
    }
    .menu-enter, .menu-leave-to {
        transform: translateX(-100%);
    }
</style>