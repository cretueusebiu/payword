class Vendor {

    /**
     * Create a new vendor instance.
     *
     * @param  {String} uri
     */
    constructor(uri) {
        this.apiUri = uri;
    }

    fetchBooks() {
        return $.get(this.apiUri + '/books');
    }
}

module.exports = Vendor;
