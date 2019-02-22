
require('./bootstrap');

window.Vue = require('vue');

Vue.component('stripe-card', require('./components/Stripe.vue'));
Vue.component('test-slot', require('./components/Slot.vue'));

// const files = require.context('./', true, /\.vue$/i)

// files.keys().map(key => {
//     return Vue.component(_.last(key.split('/')).split('.')[0], files(key))
// })

import feather from 'feather-icons';

const app = new Vue({
    el: '#app',
    mounted() {
        feather.replace()
    },
    methods: {
        url(path) {
            return window.app.url + '/' + path
        }
    }
});

