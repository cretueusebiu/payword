import Vue from 'vue';
import User from './../User';

Vue.component('book-reader', {
    template: require('./../../templates/book-reader.html'),

    data() {
        return {
            title : '',
            page : '',
            done: false
        }
    },

    ready() {
        let $modal = $('#book-modal');

        this.$on('read-book', (title) => {
            this.title = title;
            $modal.modal('show');
        });

        this.$on('read-done', (title) => {
            this.page = "THE END OF THE BOOK.";
            this.done = true;
        });

        this.$on('show-page', (page) => {
            this.page = page.content;
        });

        $modal.on('hidden.bs.modal', () => {
            this.$dispatch('book-closed');
        });
    },

    methods: {
        getNextPage() {
            this.$dispatch('next-page');
        },
    }
});
