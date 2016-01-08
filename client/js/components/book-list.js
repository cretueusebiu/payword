import Vue from 'vue';
import Vendor from './../Vendor';
import Broker from './../Broker';
import Constants from './../Constants';
import './book-reader';

let vendor = new Vendor();
let broker = new Broker();

Vue.component('book-list', {
    template: require('./../../templates/book-list.html'),
    props: ['user'],

    data() {
        return {
            books: [],
            hashChains: {},
            sentPaywords: {},
            book: null,
            nextPagePrice: null,
            firstPay: true,
            certificate: null,
        }
    },

    ready() {
        this.$on('next-page', () => {
            this.nextPage();
        });

        this.$on('book-closed', () => {
            this.firstPay = true;
        });
    },

    compiled() {
        vendor.fetchBooks().done((books) => this.books = books);
    },

    methods: {
        readBook(book) {
            console.log('Started reading book: ' + book.title + '.');
            this.fetchBook(book);
        },

        /**
         * Fetch book from vendor.
         *
         * @param  {Object} book
         */
        fetchBook(book) {
            console.log('Fetched book info...');
            vendor.fetchBook(book.id).done((book) => {
                this.book = book;
                this.fetchCertificate(book.price);
            });
        },

        /**
         * Fetch certifiacte from broker.
         *
         * @param  {Number} creditLimit
         */
        fetchCertificate(creditLimit) {
            console.log('Requesting certificate from broker...');
            broker.fetchCertificate(this.user, creditLimit)
                .done((certificate) => this.verifyCertificate(certificate))
                .fail((jqXHR) => console.log(jqXHR.responseText));
        },

        /**
         * Verify certificate received from broker.
         *
         * @param  {String} certificate
         */
        verifyCertificate(certificate) {
            let message = certificate.substr(0, Constants.CERTIFICATE_MESSAGE_LENGTH);
            let signature = certificate.substr(Constants.CERTIFICATE_MESSAGE_LENGTH, Constants.SINGATURE_LENGTH);

            this.user.verifyBrokerSignature(message, signature, broker)
                .done((response) => {
                    if (response == 'good') {
                        this.certificate = certificate;
                        console.log('Certificate from broker is valid.');
                        this.sendCommit();
                    } else {
                        alert('Could not verify broker signature.');
                        console.log('Could not verify broker signature.');
                    }
                });
        },

        /**
         * Send commit to vendor.
         */
        sendCommit() {
            let prices = this.book.prices;

            this.generateHashChains(prices);

            let commits = this.generateCommits(prices);

            console.log('Sending ' + commits.length + ' commits to vendor.');

            vendor.sendCommits(this.book.id, commits)
                .done((response) => {
                    console.log('User commit signature and broker certificate signature have been verified.');
                    this.payVendor(response.page_price);
                })
                .fail((jqXHR) => {
                    if (jqXHR.status == 422) {
                        alert(jqXHR.responseText);
                    }

                    console.log(jqXHR.responseText);
                });
        },

        /**
         * Generate hash chains.
         *
         * @param  {Object} prices
         */
        generateHashChains(prices) {
            for (let price in prices) {
                console.log('Generating hashchain of length ' + prices[price] + ' and payword value of ' + price);
                this.hashChains[price] = this.user.generateHashChain(prices[price]);
            }
        },

        /**
         * Generate commits for all prices.
         *
         * @param  {Object} prices
         * @return {Array}
         */
        generateCommits(prices) {
            let commits = [];

            for (let price in prices) {
                commits.push(this.user.generateCommit(
                    vendor.getIdentity(),
                    this.certificate,
                    this.hashChains[price][0],
                    this.hashChains[price].length,
                    price,
                    this.book.id
                ));

                this.sentPaywords[price] = 1;

                console.log('Generating commit of value ' + price);
            }

            return commits;
        },

        /**
         * Send payword to vendor.
         *
         * @param  {Number} price
         */
        payVendor(price) {
            console.log('Sending payment of ' + price + ' cents ...');
            vendor.sendPayword(this.book.id, this.user.getIdentity(), this.getPaywordByPrice(price))
                .done((response) => {
                    if (this.firstPay) {
                        this.showReader();
                        this.firstPay = false;
                    }

                    this.nextPagePrice = response.next_page;
                    this.displayPage(response.page);
                    console.log('Page with id ' + response.page.id + ' received.');
                })
                .fail((jqXHR) => console.log(jqXHR.responseText));
        },

        /**
         * Get payword from hash chains by price.
         *
         * @param  {Number} price
         * @return {Payword}
         */
        getPaywordByPrice(price) {
            let payword = this.hashChains[price][this.sentPaywords[price]];
            this.sentPaywords[price]++;
            return payword;
        },

        nextPage() {
            if (this.nextPagePrice) {
                this.payVendor(this.nextPagePrice);
            } else {
                this.firstPay = true;
                this.$broadcast('read-done');
                console.log('Book finished.');
            }
        },

        showReader() {
            this.$broadcast('read-book', this.book.title);
        },

        displayPage(page) {
            this.$broadcast('show-page', page);
        }
    }
});
