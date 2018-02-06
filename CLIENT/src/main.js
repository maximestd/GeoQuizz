// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import axios from 'axios'

Vue.config.productionTip = false

window.axios = axios.create({
  baseURL: 'http://api.geoquizz.local:10101',
});

new Vue({
  el: '#app',
  router,
  components: { App },
  template: '<App/>'
})
