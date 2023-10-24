import CryptoJS from 'crypto-js'

let _instance;
const encKey = 'H89F3kf';

class Encrypter {
  constructor() {
    this.key = encKey + 'BEcZ';
    this.resultSuffix = ':(rst)==';

    this.key += 'h5WfA'
  }

  static instance() {
    if (_instance) {
      return _instance;
    }

    return (_instance = new Encrypter());
  }

  getKey() {
    return this.key
  }

  encrypt(value) {
    const key = this.getKey()
    const iv = CryptoJS.lib.WordArray.random(16);

    const encryptedValue = CryptoJS.AES.encrypt(value, CryptoJS.enc.Utf8.parse(key), {
      iv: iv,
      mode: CryptoJS.mode.CBC,
      padding: CryptoJS.pad.Pkcs7,
    });

    const ivBase64 = CryptoJS.enc.Base64.stringify(iv);
    const encryptedValueBase64 = encryptedValue.toString();

    const mac = CryptoJS.HmacSHA256(ivBase64 + encryptedValueBase64, key).toString();

    const payload = {
      iv: ivBase64,
      value: encryptedValueBase64,
      mac: mac,
    };

    const json = JSON.stringify(payload);

    const result = this.strSplit(json, 100000).map((value) => CryptoJS.enc.Base64.stringify(CryptoJS.enc.Utf8.parse(value)));

    return result.join('.') + this.resultSuffix;
  }

  strSplit(str, length) {
    const chunks = [];
    for (let i = 0; i < str.length; i += length) {
      chunks.push(str.substr(i, length));
    }
    return chunks;
  }

  decrypt(payload) {
    payload = payload.replace(this.resultSuffix, '');

    const splittedPayload = payload.split('.');
    let val = '';

    for (const value of splittedPayload) {
      val += atob(value);
    }

    payload = val;

    let jsonObj;

    try {
      jsonObj = JSON.parse(payload);
    } catch (e) {
      throw new Error('The payload is invalid.');
    }

    if (!(jsonObj instanceof Object)) {
      throw new Error('The payload is invalid.');
    }

    const requiredKeys = ['iv', 'value', 'mac'];

    for (const item of requiredKeys) {
      if (!jsonObj.hasOwnProperty(item) || typeof jsonObj[item] !== 'string') {
        throw new Error('The payload is invalid.');
      }
    }

    if (jsonObj.hasOwnProperty('tag') && typeof jsonObj.tag !== 'string') {
      throw new Error('The payload is invalid.');
    }

    const ivValue = jsonObj.iv;
    const encryptedData = jsonObj.value;

    const key = CryptoJS.enc.Utf8.parse(this.getKey());
    const iv = CryptoJS.enc.Base64.parse(ivValue);
    const decodedValue = CryptoJS.enc.Base64.parse(encryptedData);

    const decValue = CryptoJS.AES.decrypt({
      ciphertext: decodedValue,
    }, key, {
      iv,
      padding: CryptoJS.pad.Pkcs7,
    });

    return decValue.toString(CryptoJS.enc.Utf8);
  }

  isEncrypted(value) {
    return Boolean(typeof value === 'string' && value.includes(this.resultSuffix))
  }
}

export function encrypt(value) {
  return Encrypter.instance().encrypt(value);
}

export function decrypt(value) {
  return Encrypter.instance().decrypt(value);
}
