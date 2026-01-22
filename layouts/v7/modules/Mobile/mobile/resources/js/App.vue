<template>
  <div>
    <header-wrap :data="data.result" :module-label="moduleLabel" />
    <div class="main pt-16">
      <slot></slot>
    </div>
  </div>
</template>

<script>
import HeaderWrap from './components/Header';
export default {
  props: {
    moduleLabel: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      data: {},
    };
  },
  components: {
    HeaderWrap,
  },
  created() {
    axios.post('api.php', { _operation: 'userInfo' }).then(({ data }) => {
      this.data = data;
    });
  },
};
</script>
