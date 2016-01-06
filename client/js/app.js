import Vue from 'vue';
import jQuery from 'jquery';
import Payword from './Payword';
import crypto from 'crypto';

window.$ = window.jQuery = jQuery;

const BROKER_API = 'http://broker.payword.app/api';
let BROKER_PUB_KEY = null;

let app = new Vue({
    el: '#app',

    data: {
        apiToken: null,
        identity: null,
        privateKey: null,
        publicKey: null,
        validCertificate: null,
        certificate: null,

        hashChainLength: 100,

        paymentsDone: {},
        hashChains: {},
    },

    compiled() {
        let data = {api_token: this.apiToken};

        $.get(BROKER_API + '/public_key', data, null)
            .done(key => BROKER_PUB_KEY = key);
    },

    methods: {
        getCertificate() {
            let creditLimit = 100;

            let data = {
                api_token: this.apiToken,
                identity: this.identity,
                public_key: this.publicKey,
                credit_limit: creditLimit,
            };

            $.post(BROKER_API + '/register', data)
                .done((certificate) => this.verifyCertificate(certificate))
                .fail((jqXHR) => console.log(jqXHR.responseText));
        },

        verifyCertificate(certificate) {
            let message = certificate.substr(0, 941);
            let signature = certificate.substr(941, certificate.length);

            let data = {
                message: message,
                signature: signature,
                public_key: BROKER_PUB_KEY,
            };

            $.post('verify.php', data)
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

            let cn = crypto.randomBytes(1024);

            let lastPayword = new Payword(cn); // c(n-1)

            currentHashChain.push(lastPayword);

            for (let i = 0; i < this.hashChainLength; i++) {
                let currentPayword = new Payword(lastPayword);
                currentHashChain.push(currentPayword);

                lastPayword = currentPayword;
            }

            console.log('Hash chain generated:', currentHashChain);
        },
    }
})
