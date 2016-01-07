import Vue from 'vue';
import jQuery from 'jquery';
import crypto from 'crypto';
import User from './User';
import Broker from './Broker';
import Payword from './Payword';
import './components/book-list';
import './components/user-settings';

window.$ = window.jQuery = jQuery;
require('bootstrap');

const KEY_LENGTH     = 271;
const DATA_LENGTH    = 220;
const MESSAGE_LENGTH = DATA_LENGTH + 2 * KEY_LENGTH;

let broker = new Broker();

let app = new Vue({
    el: '#app',

    data: {
        user: null,

        validCertificate: null,
        certificate: null,

        hashChainLength: 100,

        paymentsDone: {},
        hashChains: {},
    },

    ready() {
        broker.fetchPublicKey();
    },

    methods: {
        getCertificate() {
            let creditLimit = 100;

            broker.fetchCertificate(this.user, creditLimit)
                .done((certificate) => this.verifyCertificate(certificate))
                .fail((jqXHR) => console.log(jqXHR.responseText));
        },

        verifyCertificate(certificate) {
            let message = certificate.substr(0, MESSAGE_LENGTH);
            let signature = certificate.substr(MESSAGE_LENGTH, certificate.length);

            this.user.verifyBrokerSignature(message, signature, broker)
                .done((response) => {
                    if (response == 'good') {
                        this.certificate = certificate;
                        this.validCertificate = true;
                        this.payVendor();
                    } else {
                        this.validCertificate = false;
                    }
                });
        },

        payVendor() {
            let vendorIdentity = 'vendor@vendor.payword.app';
            let paymentNo = 0;

            // First payment
            if (!this.paymentsDone.hasOwnProperty(vendorIdentity)) {
                this.generateHashChain(vendorIdentity);
            } else {
                paymentNo = this.paymentsDone[vendorIdentity].length;
            }
        },

        generateHashChain(vendorIdentity) {
            let currentHashChain = [];

            let cn = crypto.randomBytes(20).toString('hex');

            let lastPayword = new Payword(cn); // c(n-1)

            currentHashChain.unshift(lastPayword);

            for (let i = 0; i < this.hashChainLength - 1; i++) {
                let currentPayword = new Payword(lastPayword);
                currentHashChain.unshift(currentPayword);

                lastPayword = currentPayword;
            }
        },

        showSettings() {
            this.$broadcast('show-settings');
        }
    }
})
