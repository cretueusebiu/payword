class Broker {
    /**
     * Create a new broker instance.
     *
     * @param  {String} uri
     */
    constructor(uri) {
        this.apiUri = uri;
        this.publicKey = null;
    }

    /**
     * Fetch broker public key.
     *
     * @param  {Function} done
     */
    fetchPublicKey(done) {
        $.get(this.apiUri + '/public_key')
            .done((key) => {
                this.publicKey = key;
                if (done) done(key);
            });
    }

    /**
     * Fetch certificate from broker.
     *
     * @param  {User} user
     * @param  {Number} creditLimit
     * @return {Object}
     */
    fetchCertificate(user, creditLimit) {
        let data = {
            api_token: user.getApiToken(),
            identity: user.getIdentity(),
            public_key: user.getPublicKey(),
            credit_limit: creditLimit,
        };

        return $.post(this.apiUri + '/register', data);
    }

    /**
     * Get broker public key.
     *
     * @return {String}
     */
    getPublicKey() {
        return this.publicKey;
    }
}

module.exports = Broker;
