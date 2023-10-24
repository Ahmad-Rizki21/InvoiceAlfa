import Sqids from 'sqids'

const hashers = {
  userId() {
    return new Sqids('stuser_id', 6)
  }
}

export default class Hashids {
  static encodeUserId(userId) {
    return hashers.userId().encode(['user', userId])
  }
  static decodeUserId(encodedUserId) {
    try {
      const decoded = hashers.userId().decode(encodedUserId)[0]

      if (decoded && decoded.length === 2 && decoded[0] == '198823') {
        return decoded[1]
      }
    } catch (err) {}

    return null
  }
}
