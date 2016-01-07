import Vue from 'vue';
import User from './../User';

Vue.component('user-settings', {
    template: require('./../../templates/user-settings.html'),

    props: ['user'],

    data() {
        return {
            identity: null,
            publicKey: null,
            privateKey: null,
            saved: false,
        }
    },

    compiled() {
        this.user = User.load();
        this.identity = this.user.getIdentity();
        this.publicKey = this.user.getPublicKey();
        this.privateKey = this.user.getPrivateKey();
    },

    ready() {
        let $modal = $('#settings-modal');

        this.$on('show-settings', () => {
            $modal.modal('show');
        });

        $modal.on('hidden.bs.modal', () => {
            this.saved = false;
        });
    },

    methods: {
        save() {
            this.user.setIdentity(this.identity);
            this.user.setPublicKey(this.publicKey);
            this.user.setPrivateKey(this.privateKey);
            this.user.save();

            this.saved = true;
        },
    }
});
