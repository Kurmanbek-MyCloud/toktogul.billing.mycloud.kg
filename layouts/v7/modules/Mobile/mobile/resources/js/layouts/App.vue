<template>
    <div>
        <header-wrap :data="data.result"  />
        <div class="main">
            <slot></slot>
        </div>
    </div>
</template>

<script>
import HeaderWrap from './components/Header'
export default {
    data() {
        return {
            data: {}
        }
    },
    components: {
        HeaderWrap,
    },
    created() {
        axios.post('api.php', {_operation: "userInfo"}).then(({ data }) => {
            this.data = data
        })
    }
}
</script>
