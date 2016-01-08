let instance = null;

class Vendor {
    /**
     * Create a new vendor instance.
     */
    constructor() {
        if (!instance) {
            instance = this;
        }

        this.apiUri = 'http://vendor.payword.app/api';

        return instance;
    }

    /**
     * Fetch books from server.
     *
     * @return {Object}
     */
    fetchBooks() {
        return $.get(this.apiUri + '/books');
    }

    /**
     * Fetch book from server.
     *
     * @param {Number} id
     * @return {Object}
     */
    fetchBook(id) {
        return $.get(this.apiUri + '/books/' + id);
    }

    /**
     * Send commits to server.
     *
     * @param {Number} bookId
     * @param {Array} commits
     * @return {Object}
     */
    sendCommits(bookId, commits) {
        return $.post(this.apiUri + '/books/' + bookId, {commits: commits});
    }

    /**
     * Send payword to server.
     *
     * @param {Number} bookId
     * @param {String} userIdentity
     * @param {Payword} payword
     * @return {Object}
     */
    sendPayword(bookId, userIdentity, payword) {
        let data = {userIdentity: userIdentity, payword: payword.getSecret()};

        return $.post(this.apiUri + '/books/' + bookId + '/page', data);
    }

    getIdentity() {
        return 'vendor@vendor.payword.app';
    }

    static getInsance() { return instance; }
}

module.exports = Vendor;
