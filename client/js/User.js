import pad from 'pad';
import crypto from 'crypto';
import {RSAKey} from 'jsrsasign';
import Payword from './Payword';

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

    generateHashChain(hashChainLength) {
        let currentHashChain = [];

        let cn = crypto.randomBytes(20).toString('hex');

        let lastPayword = new Payword(cn); // c(n-1)

        currentHashChain.unshift(lastPayword);

        for (let i = 0; i < hashChainLength; i++) {
            let currentPayword = new Payword(lastPayword);
            currentHashChain.unshift(currentPayword);

            lastPayword = currentPayword;
        }

        return currentHashChain;
    }

    /**
     * [generateCommit description]
     * @param  {String} vendorIdentity
     * @param  {String} certificate
     * @param  {Payword} firstPayword
     * @param  {Number} hashChainLength
     * @return {String}
     */
    generateCommit(vendorIdentity, certificate, firstPayword, hashChainLength, price) {

        let message =   pad(vendorIdentity, 100) +
                        certificate +
                        firstPayword.getSecret() +
                        Math.floor(Date.now() / 1000).toString() +
                        pad(hashChainLength.toString(), 10) +
                        pad(price.toString(), 10);

        let rsa = new RSAKey();
        rsa.readPrivateKeyFromPEMString(this.getPrivateKey());
        let signature = rsa.signString(message, 'sha1');

        return message + signature;
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
