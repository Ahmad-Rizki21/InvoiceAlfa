import {
  copyToClipboard,
  debounce,
  dom,
  extend,
  format,
  frameDebounce,
  throttle,
  uid,
  date
} from 'quasar'
import initials from 'initials'
import currencyFormatter from 'currency-formatter'

const { humanStorageSize } = format

function pembilang(value) {
  value = Math.floor(Math.abs(value));

  let calculated = 0;
  const huruf = [
    '',
    'satu',
    'dua',
    'tiga',
    'empat',
    'lima',
    'enam',
    'tujuh',
    'delapan',
    'sembilan',
    'sepuluh',
    'sebelas',
  ];
  let temp = '';

  if (value < 12) {
    return ' ' + huruf[value];
  } else if (value < 20) {
    return pembilang(Math.floor(value - 10)) + ' belas';
  } else if (value < 100) {
    calculated = Math.floor(value / 10);
    return pembilang(calculated) + ' puluh' + pembilang(value % 10);
  } else if (value < 200) {
    return ' seratus' + pembilang(value - 100);
  } else if (value < 1000) {
    calculated = Math.floor(value / 100);
    return pembilang(calculated) + ' ratus' + pembilang(value % 100);
  } else if (value < 2000) {
    return ' seribu' + pembilang(value - 1000);
  } else if (value < 1000000) {
    calculated = Math.floor(value / 1000);
    return pembilang(calculated) + ' ribu' + pembilang(value % 1000);
  } else if (value < 1000000000) {
    calculated = Math.floor(value / 1000000);
    return pembilang(calculated) + ' juta' + pembilang(value % 1000000);
  } else if (value < 1000000000000) {
    calculated = Math.floor(value / 1000000000);
    return pembilang(calculated) + ' miliar' + pembilang(value % 1000000000);
  } else if (value < 1000000000000000) {
    calculated = Math.floor(value / 1000000000000);
    return pembilang(value / 1000000000000) + ' triliun' + pembilang(value % 1000000000000);
  }

  return temp.trim();
}

class Utils {
  constructor({ $i18n }) {
    this.$i18n = $i18n
  }
  get copyToClipboard() {
    return copyToClipboard
  }

  currency(amount, opts = {}) {
    if (typeof amount === 'undefined' || amount === null) {
      return;
    }

    return currencyFormatter.format(amount, {
      symbol: '',
      decimal: ',',
      thousand: '.',
      precision: 0,
      ...opts
    });
  }
  get debounce() {
    return debounce
  }
  delay(time) {
    return new Promise(resolve => {
      setTimeout(() => {
        resolve(null)
      }, time || 10)
    })
  }
  get dom() {
    return dom
  }
  get frameDebounce() {
    return frameDebounce
  }
  generateId(len = 24) {
    return uid().replace(/-/g, '').substr(len - 13) + String(new Date().getTime())
  }
  get humanStorageSize() {
    return humanStorageSize
  }
  initials(str, maxLength = 2) {
    let result = initials(str)

    if (maxLength && typeof result === 'string' && result.length > maxLength) {
      return result.substr(0, maxLength);
    }

    return result;
  }
  isEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
  }
  isUrl(str) {
    return ('' + str).substr(0, 4).toLowerCase() === 'http'
  }
  isUrlValid(str) {
    return !!str.match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g)
  }
  range(size) {
    return [...Array(size).keys()]
  }
  merge(...payload) {
    payload = payload.map(v => {
      if (Array.isArray(v)) {
        return [...v]
      }

      if (v && typeof v === 'object') {
        return { ...v }
      }

      return v
    })

    return extend(...payload)
  }
  get throttle() {
    return throttle
  }
  titleCase(str) {
    str = str.toLowerCase().split(' ')
    for (let i = 0; i < str.length; i++) {
      str[i] = str[i].charAt(0).toUpperCase() + str[i].slice(1)
    }
    return str.join(' ');
  }

  padZero(number) {
    if (number < 10) {
      return '0' + number;
    }

    return number.toString();
  }

  formatSecondsToTime(seconds, withDay = true, withSeconds = true) {
    if (!seconds) {
      if (withSeconds) {
        return '00:00:00'
      }

      return '00:00'
    }

    const locale = this.$i18n.locale
    let days
    let hours

    if (withDay) {
      days = Math.floor(seconds / 86400);
      hours = Math.floor((seconds % 86400) / 3600);
    } else {
      days = 0
      hours = Math.floor(seconds / 3600);
    }

    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;
    const padZero = this.padZero
    const trans = (en, id) => locale === 'en' ? en : id

    let formattedTime = `${padZero(hours)}:${padZero(minutes)}`

    if (withSeconds) {
      formattedTime += `:${padZero(remainingSeconds)}`
    }

    if (withDay && days) {
      return `${days}${trans('d', 'hr')} ${formattedTime}`;
    }

    return formattedTime;
  }

  formatChartSecondsToTime(seconds, withDay = true) {
    if (!seconds) {
      return '0'
    }

    const locale = this.$i18n.locale
    let days
    let hours

    if (withDay) {
      days = Math.floor(seconds / 86400);
      hours = Math.floor((seconds % 86400) / 3600);
    } else {
      days = 0
      hours = Math.floor(seconds / 3600);
    }

    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;
    const padZero = (number) => {
      if (number < 10) {
        return '0' + number;
      }

      return number.toString();
    }

    const trans = (en, id) => locale === 'en' ? en : id

    const formattedTime = `${padZero(hours)}:${padZero(minutes)}`;

    if (withDay && days) {
      return `${days}${trans('d', 'hr')} ${formattedTime}`;
    }

    if (!hours) {
      return `${minutes}m`
    }

    if (!minutes) {
      return `${remainingSeconds}${trans('s', 'd')}`
    }


    return formattedTime;
  }

  formatTextSecondsToTime(seconds, withDay = true) {
    if (!seconds) {
      return '0'
    }

    const locale = this.$i18n.locale

    let days
    let hours

    if (withDay) {
      days = Math.floor(seconds / 86400);
      hours = Math.floor((seconds % 86400) / 3600);
    } else {
      days = 0
      hours = Math.floor(seconds / 3600);
    }

    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;
    const padZero = (number) => {
      if (number < 10) {
        return '0' + number;
      }

      return number.toString();
    }


    const trans = (en, id) => locale === 'en' ? en : id

    const formattedTime = `${hours}${trans('h', 'j')} ${minutes}m`

    if (withDay && days) {
      return `${days}${trans('d', 'hr')} ${formattedTime}`;
    }

    if (!hours) {
      return `${minutes}m`
    }

    if (!minutes) {
      return `${remainingSeconds}${trans('s', 'd')}`
    }


    return formattedTime;
  }

  formatLongTextSecondsToTime(seconds, withDay = true) {
    const plural = (v) => v === 1 ? '' : (locale === 'en' ? 's' : '')
    const trans = (en, id) => locale === 'en' ? en : id

    if (!seconds) {
      return '0 ' + trans('seconds', 'detik')
    }

    const locale = this.$i18n.locale

    let days
    let hours

    if (withDay) {
      days = Math.floor(seconds / 86400);
      hours = Math.floor((seconds % 86400) / 3600);
    } else {
      days = 0
      hours = Math.floor(seconds / 3600);
    }

    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;
    const padZero = (number) => {
      if (number < 10) {
        return '0' + number;
      }

      return number.toString();
    }


    const formattedTime = `${hours} hour${plural(hours)} ${minutes} minute${plural(minutes)}`

    if (withDay && days) {
      return `${days} ${trans('day', 'hari')}${plural(days)} ${formattedTime}`;
    }

    let res = ''

    if (!hours) {
      if (minutes) {
        res = `${minutes} ${trans('minute', 'menit')}${plural(minutes)}`
      }

      if (remainingSeconds) {
        res += ` ${remainingSeconds} ${trans('second', 'detik')}${plural(remainingSeconds)}`
      }

      return res
    } else {
      res = `${hours} ${trans('hour', 'jam')}${plural(hours)}`

      if (minutes) {
        res += ` ${minutes} ${trans('minute', 'menit')}${plural(minutes)}`
      }

      return res
    }
  }

  isDirty(n, o) {
    for (const key in o) {
      if (o[key] !== n[key]) {
        return true
      }
    }

    return false
  }

  isDateValid(value, format = 'DD/MM/YYYY') {
    if (!value) {
      return false
    }
    return date.formatDate(date.extractDate(value, format), format) === value
  }

  convertDateFormat(value, formatFrom = 'DD/MM/YYYY', formatTo = 'YYYY-MM-DD') {
    if (!value) {
      return value
    }

    return date.formatDate(date.extractDate(value, formatFrom), formatTo)
  }

  revertDateFormat(value, formatFrom = 'YYYY-MM-DD', formatTo = 'DD/MM/YYYY') {
    if (!value) {
      return value
    }

    return date.formatDate(date.extractDate(value, formatFrom), formatTo)
  }

  formatFloatDecimal(number, toFixed = 2) {
    number = parseFloat(number, 10)

    if (number % 1 !== 0 && number % 0.1 !== 0) {
      return number.toFixed(toFixed);
    }

    if (number % 1 !== 0 && number % 0.1 === 0) {
      return number.toFixed(1)
    }


    if (number % 1 === 0) {
      return number.toString()
    }

    return parseFloat(number.toFixed(toFixed)).toString()
  }

  terbilang(number) {
    const result = pembilang(Math.round(number)).trim()

    return result ? (result + ' rupiah') : ''
  }
}

export default ({ Vue, app }) => {
  Vue.prototype.$utils = new Utils({
    $i18n: app.i18n
  })
}
