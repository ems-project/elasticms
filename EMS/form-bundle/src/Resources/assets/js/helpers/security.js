import {generate} from "hashcash-token";

export default class
{
    static addHashCashHeader(data, xhr)
    {
        if ('token' in data) {
            xhr.setRequestHeader('x-hashcash', this.getHashCash(data));
        }
    }

    static getHashCash(data)
    {
        if ('token' in data) {
            let token = data.token;
            return [token.hash, token.nonce, token.data].join('|');
        }
    }

    static createToken(crsfToken, difficulty)
    {
        if (0 === difficulty) {
            return false;
        }

        return generate({
            difficulty: parseInt(difficulty),
            data: crsfToken
        });
    }
}
