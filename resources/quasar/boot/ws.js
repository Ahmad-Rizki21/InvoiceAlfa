import { decrypt } from 'src/services/http-crypter'

class Ws {
  constructor() {
    this.url = null
    this.socket = null
    this.reset()
    this._appSource = null
  }

  get appSource() {
    return this._appSource
  }

  reset() {
    this._readyFns = []
    this._closeFns = []
    this._messageFns = {}
    this._messageFnsOnce = []
    this._eventFnsOnce = {}
  }

  connect() {
    if (!this.url) {
      return this
    }

    this.socket = new WebSocket(this.url)
    this.socket.onmessage = (e) => {
      for (const key in this._messageFns) {
        this._messageFns[key](e, this)
      }

      this._messageFnsOnce.forEach(v => {
        v(e, this)
      })
      this._messageFnsOnce = []


      let eData = null
      let eDataKey = null

      try {
        eData = JSON.parse(e.data)
        eDataKey = eData.mode + '' + eData.id
      } catch (e) {}

      if (eData) {
        const eventFnsOnce = this._eventFnsOnce

        for (const key in eventFnsOnce) {
          if (eDataKey == key && eventFnsOnce[key] && eventFnsOnce[key].length) {
            eventFnsOnce[key].forEach(v => {
              v(e, this, eData)
            })

            this._eventFnsOnce[key] = []
          }
        }
      }
    }

    this.socket.onopen = (e) => {
      this._readyFns.forEach(v => {
        v(this)
      })

      this._readyFns = []
    }

    this.socket.onclose = (e) => {
      this._closeFns.forEach(v => {
        v(this)
      })

      this._closeFns = []
    }


    return this
  }

  onMessage(fn) {
    const id = this.$utils.generateId()
    this._messageFns[id] = fn

    return id
  }

  onceMessage(fn) {
    this._messageFnsOnce.push(fn)

    return this
  }

  onceEvent(mode, id, fn) {
    const key = mode + '' + id
    if (!this._eventFnsOnce[key]) {
      this._eventFnsOnce[key] = []
    }

    this._eventFnsOnce[key].push(fn)

    return this
  }

  onReady(fn) {
    if (this.isReady()) {
      fn(this)
    } else {
      this._readyFns.push(fn)
    }

    return this
  }

  onClose(fn) {
    this._closeFns.push(fn)

    return this
  }

  isReady() {
    return this.socket && this.socket.readyState === WebSocket.OPEN
  }

  send(payload) {
    if (this.isReady()) {
      if (typeof payload === 'object' && payload) {
        payload = JSON.stringify(payload)
      }

      this.socket.send(payload)
    }

    return this
  }

  async close() {
    if (this.socket && this.socket.close) {
      await this.socket.close()
    }
    return this
  }
}

export default ({ Vue }) => {
  const ws = new Ws(Vue.prototype.$utils)

  if (process.env.CLIENT) {
    try {
      const appData = JSON.parse(decrypt(window._APP_DATA || ''))
      ws.url = appData.ws.url
      ws._appSource = appData.ws.src
    } catch (err) {
      console.error(err)
    }
  }

  Vue.prototype.$ws = ws
}
