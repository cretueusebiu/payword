class User {
    constructor(identity, pubKey, privKey, apiToken) {
        this.identity = identity;
        this.publicKey = pubKey;
        this.privateKey = privKey;
        this.apiToken = apiToken;
    }

    getIdentity() { return this.identity; }
    getPublicKey() { return this.publicKey; }
    getPrivateKey() { return this.privateKey; }
    getApiToken() { return this.apiToken; }
}

module.exports = User;
