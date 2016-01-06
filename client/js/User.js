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

    setIdentity(val) { this.identity = val; }
    setPublicKey(val) { this.publicKey = val; }
    setPrivateKey(val) { this.privateKey = val; }
    setApiToken(val) { this.apiToken = val; }

    save() {
        localStorage.setItem('user', JSON.stringify(
        {
            identity : this.identity,
            pubKey : this.publicKey,
            privKey : this.privateKey,
            apiToken : this.apiToken
        }));
    }

    static load() {
        let obj = {};

        try {
            obj = JSON.parse(localStorage.getItem('user'));
        } catch(e) {console.log(e);}

        return new User(obj.identity, obj.pubKey, obj.privKey, obj.apiToken);
    }
}

module.exports = User;
