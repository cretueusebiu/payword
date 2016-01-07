import Vue from 'vue';
import Vendor from './../Vendor';

let vendor = new Vendor();

Vue.component('book-list', {
    template: require('./../../templates/book-list.html'),

    data() {
        return {
            books: [],
        }
    },

    compiled() {
        vendor.fetchBooks().done((books) => this.books = books);
    }
});
