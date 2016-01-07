import Vue from 'vue';
import jQuery from 'jquery';
import User from './User';
import Broker from './Broker';
import Payword from './Payword';
import './components/book-list';
import './components/user-settings';

window.$ = window.jQuery = jQuery;
require('bootstrap');

let broker = new Broker();

let app = new Vue({
    el: '#app',

    data: {
        user: null,
    },

    ready() {
        broker.fetchPublicKey();
    },

    methods: {
        showSettings() {
            this.$broadcast('show-settings');
        }
    }
})
