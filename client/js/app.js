"use strict";

const BROKER_API = 'http://broker.payword.app/api';
let BROKER_PUB_KEY = null;

let app = new Vue({
    el: '#app',

    data: {
        validCertificate: null,
        certificate: null,

        paymentsDone: {
            vendorIdentity: [
            ]
        },
    },

    compiled() {
        let data = {api_token: this.apiToken};

        $.get(BROKER_API + '/public_key', data, null)
            .done(key => BROKER_PUB_KEY = key);
    },

    methods: {
        getCertificate() {
            let creditLimit = 1000;

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
            if (!paymentsDone.hasOwnProperty(vendorIdentity)) {
                this.generateHashChain(vendorIdentity);
            } else {
                paymentNo = paymentsDone[vendorIdentity].length;
            }
        },

        generateHashChain(vendorIdentity) {
            let currentHashChain = [];
        },
    }
})
