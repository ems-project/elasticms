export default class
{
    static jsonParse(string)
    {
        try {
            return JSON.parse(string);
        } catch (e) {
            return false;
        }
    }

    static urlEncodeData(data)
    {
        let urlEncoded = [];
        for (let key in data) {
            urlEncoded.push(encodeURI(key.concat('=').concat(data[key])));
        }
        return urlEncoded.join('&');
    }
}
