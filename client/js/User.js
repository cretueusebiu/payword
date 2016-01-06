class User {
    /**
     * Create a new user instance.
     * @param  {String} identity
     * @param  {String} pubKey
     * @param  {String} privKey
     */
    constructor(identity, pubKey, privKey) {
        this.identity = identity;
        this.publicKey = pubKey;
        this.privateKey = privKey;
    }

    /**
     * Verify broker signature.
     *
     * @param  {string} message
     * @param  {string} signature
     * @param  {Broker} broker
     * @return {Object}
     */
    verifyBrokerSignature(message, signature, broker) {
        let data = {
            message: message,
            signature: signature,
            public_key: broker.getPublicKey(),
        };

        return $.post('verify.php', data);
    }

    getIdentity() { return this.identity; }
    getPublicKey() { return this.publicKey; }
    getPrivateKey() { return this.privateKey; }

    setIdentity(val) { this.identity = val; }
    setPublicKey(val) { this.publicKey = val; }
    setPrivateKey(val) { this.privateKey = val; }

    /**
     * Save user.
     */
    save() {
        localStorage.setItem('user', JSON.stringify({
            identity : this.identity,
            pubKey : this.publicKey,
            privKey : this.privateKey,
        }));
    }

    /**
     * Load user.
     *
     * @return {User}
     */
    static load() {
        let obj;

        try {
            obj = JSON.parse(localStorage.getItem('user'));
        } catch(e) {}

        obj = obj || {};

        return new User(obj.identity, obj.pubKey, obj.privKey);
    }
}

module.exports = User;
