import Vue from 'vue';
import jQuery from 'jquery';
import crypto from 'crypto';
import User from './User';
import Broker from './Broker';
import Payword from './Payword';

window.$ = window.jQuery = jQuery;

const KEY_LENGTH     = 271;
const DATA_LENGTH    = 220;
const MESSAGE_LENGTH = DATA_LENGTH + 2 * KEY_LENGTH;

let broker = new Broker('http://broker.payword.app/api');
broker.fetchPublicKey();

let app = new Vue({
    el: '#app',

    data: {
        apiToken: null,
        identity: null,
        publicKey: null,
        privateKey: null,
        validCertificate: null,
        certificate: null,

        hashChainLength: 100,

        paymentsDone: {},
        hashChains: {},
    },

    compiled() {
        this.loadData();
    },

    methods: {
        getCertificate() {
            this.saveData();

            let creditLimit = 100;

            let user = new User(this.identity, this.publicKey, this.privateKey, this.apiToken);

            broker.fetchCertificate(user, creditLimit)
                .done((certificate) => this.verifyCertificate(certificate))
                .fail((jqXHR) => console.log(jqXHR.responseText));
        },

        verifyCertificate(certificate) {
            let message = certificate.substr(0, MESSAGE_LENGTH);
            let signature = certificate.substr(MESSAGE_LENGTH, certificate.length);

            let data = {
                message: message,
                signature: signature,
                public_key: broker.getPublicKey(),
            };

            // let verifier = crypto.createVerify('sha1');

            // verifier.update(signature);

            // let ver = verifier.verify(broker.getPublicKey(), signature, 'base64');

            // console.log(ver);

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

            let cn = crypto.randomBytes(20).toString('hex');

            let lastPayword = new Payword(cn); // c(n-1)

            currentHashChain.unshift(lastPayword);

            for (let i = 0; i < this.hashChainLength - 1; i++) {
                let currentPayword = new Payword(lastPayword);
                currentHashChain.unshift(currentPayword);

                lastPayword = currentPayword;
            }
        },

        generatedCommit() {

        },

        loadData() {
            let identity = localStorage.getItem('identity');
            let apiToken = localStorage.getItem('apiToken');
            let publicKey = localStorage.getItem('publicKey');
            let privateKey = localStorage.getItem('privateKey');

            if (identity) this.identity = identity;
            if (apiToken) this.apiToken = apiToken;
            if (publicKey) this.publicKey = publicKey;
            if (privateKey) this.privateKey = privateKey;
        },

        saveData() {
            localStorage.setItem('identity', this.identity);
            localStorage.setItem('apiToken', this.apiToken);
            localStorage.setItem('publicKey', this.publicKey);
            localStorage.setItem('privateKey', this.privateKey);
        }
    }
})
