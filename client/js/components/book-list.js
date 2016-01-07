import Vue from 'vue';
import Vendor from './../Vendor';
import Broker from './../Broker';

let vendor = new Vendor();
let broker = new Broker();

const KEY_LENGTH     = 271;
const DATA_LENGTH    = 220;
const MESSAGE_LENGTH = DATA_LENGTH + 2 * KEY_LENGTH;

Vue.component('book-list', {
    template: require('./../../templates/book-list.html'),
    props: ['user'],

    data() {
        return {
            books: [],
            hashChains: {},
            lastPaywords: {},
            book: null,

            validCertificate: null,
            certificate: null,

            hashChainLength: 100,
        }
    },

    compiled() {
        vendor.fetchBooks().done((books) => this.books = books);
    },

    methods: {

        readBook(book) {
            console.log("Started reading book : " + book.title + "...");
            this.fetchBook(book);

        },

        fetchBook(book) {
            vendor.fetchBook(book.id).done((book) => {
                this.book = book;
                console.log("Fetched book info ...");
                this.getCertificate(book.price);
            });
        },

        getCertificate(creditLimit) {
            console.log("Requesting certificate from broker ...");
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
                        console.log("Certificate from broker is valid ...");
                        this.firstCommit();
                    } else {
                        this.validCertificate = false;
                        console.log("Could not verify broker signature ...");
                    }
                });
        },

        firstCommit() {
            let prices = this.book.prices;

            for (let price in prices) {
                this.hashChains[price] = this.user.generateHashChain(prices[price]);
                console.log('Generating hashchain of length ' +
                    prices[price] + ' and payword value of ' + price);
            }

            let commits = [];

            for (let price in prices) {
                commits.push(this.user.generateCommit(
                        vendor.getIdentity(),
                        this.certificate,
                        this.hashChains[price][0],
                        this.hashChains[price].length,
                        price
                        )
                    );

                console.log('Generating commit of value ' + price);
            }

            console.log('Sending ' + commits.length + ' commits to vendor ...');
            vendor.sendCommits(this.book.id, commits)
                .done((response) => {
                    console.log(response);
                })
                .fail((jqXHR) => console.log(jqXHR.responseText));

        },

        payVendor() {
            console.log("Creating");
            let vendorIdentity = 'vendor@vendor.payword.app';
            let paymentNo = 0;

            // First payment
            if (!this.paymentsDone.hasOwnProperty(vendorIdentity)) {
                this.generateHashChain(vendorIdentity);
            } else {
                paymentNo = this.paymentsDone[vendorIdentity].length;
            }
        },
    }
});
