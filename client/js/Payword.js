import sha1 from 'node-sha1';

class Payword {
    constructor(secretOrPayword) {
        if (secretOrPayword instanceof Payword) {
            this.secret = sha1(secretOrPayword.getSecret());
        } else {
            this.secret = secretOrPayword;
        }
    }

    getSecret() {
        return this.secret;
    }
}

module.exports = Payword;
