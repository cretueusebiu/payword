import {RSAKey} from 'jsrsasign';

let instance = null;

class Broker {
    /**
     * Create a new broker instance.
     */
    constructor(uri) {
        if (!instance) {
            instance = this;
        }

        this.apiUri = 'http://broker.payword.app/api';
        this.publicKey = null;

        return instance;
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
        let rsa = new RSAKey();
        rsa.readPrivateKeyFromPEMString(user.getPrivateKey());
        let signature = rsa.signString(user.getIdentity(), 'sha1');

        let data = {
            identity: user.getIdentity(),
            public_key: user.getPublicKey(),
            credit_limit: creditLimit,
            signature: signature,
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
