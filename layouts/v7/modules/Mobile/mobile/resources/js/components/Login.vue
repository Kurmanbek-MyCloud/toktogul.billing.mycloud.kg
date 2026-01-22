<template>
    <div class="container flex justify-center">
        <div class="w-9/12 px-6 3xs:px-3 xs:w-full">
            <form v-on:submit.prevent="auth">
                <div class="mb-10">
                    <input class="w-full px-1 py-2 text-gray-800 text-lg border-b border-gray-800 border-solid"
                           :class="{'border-red-500': error.length}"
                           type="text" v-model="authForm.username" placeholder="Логин">
                </div>
                <div class="mb-6">
                    <div class="flex justify-between mb-2 border-b border-gray-800 border-solid" :class="{'border-red-500': error.length}">
                        <input class="w-full px-1 py-2 text-gray-800 text-lg "
                               :type=" viewPassword && 'text' || 'password'" v-model="authForm.password" placeholder="Пароль">
                        <button class="text-2xl px-1" @click.stop.prevent="viewPassword = !viewPassword">
                            <i class="dripicons-preview inline-flex leading-none text-gray-800" :class="{'text-indigo-400': viewPassword}"></i>
                        </button>
                    </div>
                    <p v-if="error.length" class="text-sm font-medium text-red-500 text-center">Логин или пароль неверный</p>
                </div>
                <button class="w-full py-2 text-base font-medium text-center text-white bg-indigo-400 my-4 relative">
                    <span :class="{'opacity-0': loading}">Войти</span>
                    <span class="loader absolute m-auto inset-0 w-6 h-6" :class="{'opacity-0': !loading}"></span>
                </button>
            </form>
        </div>
        <div></div>
    </div>
</template>
<script>
    import md5 from 'crypto-js/md5'
    export default {
        name: 'login',
        data() {
            return {
                authForm: {
                    username: '',
                    password: '',
                    _operation: "login",
                },
                formData: new FormData(),
                viewPassword: false,
                error: '',
                loading: false,
                token: null,
                result: {}
            }
        },

        methods: {
            auth() {
                this.loading = true
                axios.get('/webservice.php', {params: {operation: 'getchallenge', username: this.authForm.username}}).then(({ data }) => {
                    this.token = md5(data.result.token + 'BxEZilsTVwrDQrl9')
                    this.formData.append('username', this.authForm.username)
                    this.formData.append('operation', 'login')
                    this.formData.append('accessKey', this.token)
                    axios.post('/webservice.php', this.formData).then(({ data }) => {
                        this.result = data
                        localStorage.setItem('sessionName', data.result.sessionName)
                        localStorage.setItem('userId', data.result.userId)


                    })

                })
                axios.post('api.php', this.authForm).then( response => {
                    if(!response.data.success) {
                        this.error = response.data.error.message
                        this.loading = false
                    } else {
                        localStorage.setItem('session', response.data.result.login.session)
                        window.location.reload()
                    }
                })
            }
        }
    }
</script>