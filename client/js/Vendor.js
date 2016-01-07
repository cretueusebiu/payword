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
     * Fetch vendor books.
     *
     * @return {Object}
     */
    fetchBooks() {
        return $.get(this.apiUri + '/books');
    }

    /**
     * Fetch book.
     *
     * @return {Object}
     */
    fetchBook(id) {
        return $.get(this.apiUri + '/books/' + id);
    }

    sendCommits(bookId, commits) {
        return $.post(this.apiUri + '/books/' + bookId, {commits: commits});
    }

    sendPayword(bookId, userIdentity, payword) {
        return $.post(this.apiUri + '/books/' + bookId + '/page', {userIdentity: userIdentity, payword: payword.getSecret()});
    }

    getIdentity() {
        return 'vendor@vendor.payword.app';
    }

    static getInsance() { return instance; }
}

module.exports = Vendor;
