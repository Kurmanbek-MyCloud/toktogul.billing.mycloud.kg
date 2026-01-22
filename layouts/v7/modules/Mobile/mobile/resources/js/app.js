// import { createApp } from 'vue'
import App from './App.vue'
import CreateLayout from './layouts/CreateLayout.vue'
import Login from './components/Login.vue'
// const app = createApp(App)


// app.use(router)
// app.mount('#app')
import axios from 'axios'

import Vue from 'vue'
import router from './router'
import Notifications from 'vue-notification'

const PushPermission = () => import(/* webpackChunkName: "push-permission" */ './components/PushPermission')
// Vue.use(router)
Vue.component('Layout', App)
Vue.component('CreateLayout', CreateLayout)
Vue.component('login', Login)
Vue.component('push-permission', PushPermission)

// window.Vue = Vue
window.axios = axios

Vue.use(Notifications);

new Vue({
    el: '#app',
    router,
    data() {
        return {

        }
    },
    mounted(){
        this.initializeFireBaseMessaging()
    },
    methods: {
        initializeFireBaseMessaging() {
            window.messaging.onMessage(function (payload) {
                const notificationOption = {
                    body: payload.notification.body,
                    icon: payload.notification.icon
                }

                if(Notification.permission === "granted"){
                    let notification = new Notification(payload.notification.title, notificationOption)

                    notification.onclick = function (ev) {
                        ev.preventDefault()
                        window.open(payload.notification.click_action, '_blank')
                        notification.close()
                    }
                }
            })
        },
    }
})

