// file: /src/boot/storage.js
import { Cookies } from 'quasar'
import localstory from 'localstory'

class Storage {
  constructor() {
    this._prefix = 'invprn'

    this.localStorage = localstory(window.localStorage, this._prefix, {
      ttl: 1000 * 60 * 60 * 24
    });
    this.cookies = Cookies;
  }

  set(key, value) {
    this.localStorage.set(key, value);
    this.cookies.set(this._prefix + key, value, {
      expires: 1
    });
  }

  get(key) {
    const localStorageValue = this.localStorage.get(key);

    if (localStorageValue) {
      return localStorageValue
    }

    if (this.cookies.has(this._prefix + key)) {
      return this.cookies.get(this._prefix + key);
    }
  }

  unset(key) {
    this.localStorage.unset(key);
    this.cookies.remove(this._prefix + key);
  }

  keys() {
    const keys = this.localStorage.keys();
    const cookies = this.cookies.getAll();

    for (let key in cookies) {
      if (String(key).indexOf(this._prefix) === 0) {
        key = String(key).replace(this._prefix, '');

        if (keys.indexOf(key) === -1) {
          keys.push(key);
        }
      }
    }

    return keys;
  }

  clear() {
    this.localStorage.clear();
    const cookies = this.cookies.getAll();

    for (const key in cookies) {
      if (String(key).indexOf(this._prefix) === 0) {
        this.cookies.remove(key);
      }
    }
  }
}

export default ({ Vue }) => {
  if (process.env.CLIENT && !('localStorage' in window)) {
    window.__TEMP_LOCALSTORAGE = {};
    window.localStorage = {
      getItem(key) {
        return window.__TEMP_LOCALSTORAGE[key];
      },
      setItem(key, value) {
        window.__TEMP_LOCALSTORAGE[key] = value;
      }
    };
  }

  Vue.prototype.$storage = new Storage();
  Vue.prototype.$cookies = Cookies;
  Vue.prototype.$localStorage = localstory(window.localStorage, 'invprnl', {
    ttl: 1000 * 60 * 60 * 365
  });
}
