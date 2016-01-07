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

    static getInsance() { return instance; }
}

module.exports = Vendor;
