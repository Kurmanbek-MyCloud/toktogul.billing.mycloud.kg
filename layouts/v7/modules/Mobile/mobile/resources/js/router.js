import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

import Create from './views/Create.vue';
import View from './views/View.vue';
import Detail from './views/Detail.vue';
import Calendar from './views/Calendar.vue';

const routes = [
  { path: '/', component: Calendar },
  { path: '/view/:program/:name', component: View },
  { path: '/view/:program/:name/filter/:filter', component: View },
  { path: '/view/:program/:name/detail/:id', component: Detail },
  { path: '/view/:program/:name/detail/:id/:action', component: Create },
  { path: '/view/:program/:name/:action/:preset?', component: Create },
];

const router = new VueRouter({
  history: 'hash',
  routes,
});

export default router;
