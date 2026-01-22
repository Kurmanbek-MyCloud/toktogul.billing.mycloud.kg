<template></template>
<script>
    export default {
        methods: {
            requestPushPermission(){
                let self = this
                setTimeout(function(){
                        window.messaging
                            .requestPermission()
                            .then(function () {
                                return window.messaging.getToken()
                            })
                            .then(function (token) {
                                self.saveDeviceToken(token)
                            })
                            .catch(function (reason) {
                                console.log(reason)
                            })
                    },
                    5000
                )
            },
            saveDeviceToken(token){
                axios.post('/save-device-token', {
                    deviceToken: token
                }).then(() => {
                }).catch(error => {
                    console.log(error)
                })
            }
        },
        mounted(){
            this.requestPushPermission()
        }
    }
</script>
